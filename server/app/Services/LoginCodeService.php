<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Middleware\TemporaryIpBlockMiddleware;
use App\Mail\LoginCodeMail;
use App\Models\LoginCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Random\RandomException;

class LoginCodeService
{
    private const ADMIN_ROLES = [
        'ROLE_SUPER_ADMIN',
        'ROLE_BLOGGER',
        'ROLE_GAMEMASTER',
        'ROLE_SHOP',
        'ROLE_TEAM',
    ];

    /**
     * Même logique que ton regex JS :
     * - prenom.nom
     * - lettres/chiffres + tirets autorisés
     * - au moins 1 char de chaque côté du "."
     * - domaine strict @imt-atlantique.net
     */
    private const EMAIL_REGEX = '/^[a-z0-9][a-z0-9-]*\.[a-z0-9][a-z0-9-]*@imt-atlantique\.net$/i';

    private const CODE_LENGTH = 4;
    private const EXPIRES_MINUTES = 5;

    // Cooldown strict : 30s entre 2 demandes (par session, sinon IP)
    private const COOLDOWN_SECONDS = 30;

    // Anti-spam : demandes de code (par email + session, sinon IP)
    private const REQUEST_LIMIT = 6;
    private const REQUEST_WINDOW_SECONDS = 600; // 10 minutes

    // Anti-bruteforce : vérifs de code (par email + session, sinon IP)
    private const VERIFY_LIMIT = 20;
    private const VERIFY_WINDOW_SECONDS = 600; // 10 minutes

    public function __construct(private AuditLogService $auditLogService)
    {
    }

    /**
     * @throws ValidationException
     */
    public function requestCode(string $email, string $ip, ?string $userAgent, ?string $sessionId = null): void
    {
        $email = mb_strtolower(trim($email));
        $identifier = $this->identifier($sessionId, $ip);

        $this->assertEmailFormat($email);

        // Cooldown strict (par session)
        $cooldownKey = 'login_code_cooldown:' . $identifier;
        $this->enforceCooldown($cooldownKey);

        // Anti-spam request (par email+session)
        $rateKey = 'login_code_req:' . sha1($email . '|' . $identifier);
        if (RateLimiter::tooManyAttempts($rateKey, self::REQUEST_LIMIT)) {
            $wait = RateLimiter::availableIn($rateKey);

            // IMPORTANT: déclenche le "ban" consulté par le middleware => blade au refresh
            $this->setTemporaryBlockForActor(
                action: 'AUTH.LOGIN_CODE.REQUEST_RATE_LIMIT',
                email: $email,
                ip: $ip,
                sessionId: $sessionId,
            );

            $this->auditLogService->log(
                actor: null,
                action: 'AUTH.LOGIN_CODE.REQUEST_RATE_LIMIT',
                entityType: 'login_codes',
                entityId: null,
                description: 'SPAMMING USER: +6 in 10min',
                metadata: [
                    'email' => $email,
                    'ip' => $ip,
                    'session_id' => $sessionId,
                    'rate_key' => $rateKey,
                    'wait_seconds' => $wait,
                    'max_attempts' => self::REQUEST_LIMIT,
                    'window_seconds' => self::REQUEST_WINDOW_SECONDS,
                ],
                ip: $ip,
                userAgent: $userAgent,
                sessionId: $sessionId,
            );

            throw ValidationException::withMessages([
                'email' => 'Trop de demandes. Réessaie plus tard.',
            ]);
        }
        RateLimiter::hit($rateKey, self::REQUEST_WINDOW_SECONDS);

        DB::transaction(function () use ($email, $ip, $userAgent): void {
            $user = User::where('university_email', $email)->lockForUpdate()->first();

            if (!$user) {
                $user = new User();
                $user->university_email = $email;
                $user->display_name = User::displayNameFromUniversityEmail($email);
                $user->is_active = true;
                $user->save();
            } elseif (!$user->display_name) {
                $user->display_name = User::displayNameFromUniversityEmail($email);
                $user->save();
            }

            if (!$user->is_active) {
                throw ValidationException::withMessages([
                    'email' => 'Compte désactivé.',
                ]);
            }

            // Invalider les anciens codes encore "actifs"
            LoginCode::where('user_id', $user->id)
                ->whereNull('used_at')
                ->update(['used_at' => now()]);

            $code = $this->generateNumericCode(self::CODE_LENGTH);
            $expiresAt = now()->addMinutes(self::EXPIRES_MINUTES);

            LoginCode::create([
                'user_id' => $user->id,
                'code_hash' => Hash::make($code),
                'expires_at' => $expiresAt,
                'used_at' => null,
                'attempt_count' => 0,
                'ip_address' => $ip,
                'user_agent' => $userAgent ? mb_substr($userAgent, 0, 500) : null,
            ]);

            Mail::to($email)->send(new LoginCodeMail(
                code: $code,
                expiresHuman: 'dans ' . self::EXPIRES_MINUTES . ' minutes',
            ));
        });
    }

    /**
     * @throws ValidationException
     */
    public function requestAdminCode(string $email, string $ip, ?string $userAgent, ?string $sessionId = null): void
    {
        $email = mb_strtolower(trim($email));
        $identifier = $this->identifier($sessionId, $ip);

        $this->assertEmailFormat($email);

        $cooldownKey = 'admin_login_code_cooldown:' . $identifier;
        $this->enforceCooldown($cooldownKey);

        $rateKey = 'admin_login_code_req:' . sha1($email . '|' . $identifier);
        if (RateLimiter::tooManyAttempts($rateKey, self::REQUEST_LIMIT)) {
            $wait = RateLimiter::availableIn($rateKey);

            $this->setTemporaryBlockForActor(
                action: 'AUTH.ADMIN_LOGIN_CODE.REQUEST_RATE_LIMIT',
                email: $email,
                ip: $ip,
                sessionId: $sessionId,
            );

            $this->auditLogService->log(
                actor: null,
                action: 'AUTH.ADMIN_LOGIN_CODE.REQUEST_RATE_LIMIT',
                entityType: 'login_codes',
                entityId: null,
                description: 'SPAMMING ADMIN: +6 in 10min',
                metadata: [
                    'email' => $email,
                    'ip' => $ip,
                    'session_id' => $sessionId,
                    'rate_key' => $rateKey,
                    'wait_seconds' => $wait,
                    'max_attempts' => self::REQUEST_LIMIT,
                    'window_seconds' => self::REQUEST_WINDOW_SECONDS,
                ],
                ip: $ip,
                userAgent: $userAgent,
                sessionId: $sessionId,
            );

            throw ValidationException::withMessages([
                'email' => 'Trop de demandes. Réessaie plus tard.',
            ]);
        }
        RateLimiter::hit($rateKey, self::REQUEST_WINDOW_SECONDS);

        DB::transaction(function () use ($email, $ip, $userAgent): void {
            $user = User::where('university_email', $email)->lockForUpdate()->first();

            if (!$user || !$user->is_active) {
                throw ValidationException::withMessages(['email' => 'Accès admin refusé.']);
            }

            $user->loadMissing('roles');

            if (!$user->hasAnyRole(self::ADMIN_ROLES)) {
                throw ValidationException::withMessages(['email' => 'Accès admin refusé.']);
            }

            LoginCode::where('user_id', $user->id)
                ->whereNull('used_at')
                ->update(['used_at' => now()]);

            $code = $this->generateNumericCode(self::CODE_LENGTH);

            LoginCode::create([
                'user_id' => $user->id,
                'code_hash' => Hash::make($code),
                'expires_at' => now()->addMinutes(self::EXPIRES_MINUTES),
                'used_at' => null,
                'attempt_count' => 0,
                'ip_address' => $ip,
                'user_agent' => $userAgent ? mb_substr($userAgent, 0, 500) : null,
            ]);

            Mail::to($email)->send(new LoginCodeMail(
                code: $code,
                expiresHuman: 'dans ' . self::EXPIRES_MINUTES . ' minutes',
            ));
        });
    }

    /**
     * @throws ValidationException
     */
    public function verifyAdminCode(string $email, string $code, string $ip, ?string $sessionId = null): User
    {
        $user = $this->verifyCode($email, $code, $ip, $sessionId);

        $user->loadMissing('roles');

        if (!$user->hasAnyRole(self::ADMIN_ROLES)) {
            throw ValidationException::withMessages(['email' => 'Accès admin refusé.']);
        }

        return $user;
    }

    /**
     * @throws ValidationException
     */
    public function verifyCode(string $email, string $code, string $ip, ?string $sessionId = null): User
    {
        $email = Str::lower(trim($email));
        $code = preg_replace('/\D+/', '', trim($code));

        $identifier = $this->identifier($sessionId, $ip);

        $this->assertEmailFormat($email);

        if (strlen($code) !== self::CODE_LENGTH) {
            throw ValidationException::withMessages([
                'code' => 'Code invalide (4 chiffres).',
            ]);
        }

        $verifyKey = 'login_code_verify:' . sha1($email . '|' . $identifier);
        if (RateLimiter::tooManyAttempts($verifyKey, self::VERIFY_LIMIT)) {
            $wait = RateLimiter::availableIn($verifyKey);

            $this->setTemporaryBlockForActor(
                action: 'AUTH.LOGIN_CODE.VERIFY_RATE_LIMIT',
                email: $email,
                ip: $ip,
                sessionId: $sessionId,
            );

            $this->auditLogService->log(
                actor: null,
                action: 'AUTH.LOGIN_CODE.VERIFY_RATE_LIMIT',
                entityType: 'login_codes',
                entityId: null,
                description: 'Rate limit hit on login code verification',
                metadata: [
                    'email' => $email,
                    'ip' => $ip,
                    'session_id' => $sessionId,
                    'verify_key' => $verifyKey,
                    'wait_seconds' => $wait,
                    'max_attempts' => self::VERIFY_LIMIT,
                    'window_seconds' => self::VERIFY_WINDOW_SECONDS,
                ],
                ip: $ip,
                userAgent: null,
                sessionId: $sessionId,
            );

            throw ValidationException::withMessages([
                'code' => 'Trop de tentatives. Réessaie plus tard.',
            ]);
        }
        RateLimiter::hit($verifyKey, self::VERIFY_WINDOW_SECONDS);

        return DB::transaction(function () use ($email, $code): User {
            $user = User::where('university_email', $email)
                ->lockForUpdate()
                ->first();

            if (!$user || !$user->is_active) {
                throw ValidationException::withMessages([
                    'email' => 'Compte introuvable ou désactivé.',
                ]);
            }

            $latest = LoginCode::where('user_id', $user->id)
                ->where('expires_at', '>', now())
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if (!$latest || $latest->used_at) {
                throw ValidationException::withMessages([
                    'code' => 'Code expiré ou déjà utilisé. Redemande un code.',
                ]);
            }

            if (!Hash::check($code, $latest->code_hash)) {
                $latest->attempt_count++;

                // hard stop after too many tries
                if ($latest->attempt_count >= 6) {
                    $latest->used_at = now();
                }

                $latest->save();

                throw ValidationException::withMessages([
                    'code' => 'Code incorrect.',
                ]);
            }

            $latest->used_at = now();
            $latest->save();

            // Safety: kill any other unused codes
            LoginCode::where('user_id', $user->id)
                ->whereNull('used_at')
                ->where('id', '!=', $latest->id)
                ->update(['used_at' => now()]);

            $user->last_login_at = now();
            $user->save();

            return $user;
        });
    }

    private function identifier(?string $sessionId, string $ip): string
    {
        // Priority: session > ip
        return $sessionId ?: $ip;
    }

    private function enforceCooldown(string $cooldownKey): void
    {
        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            $wait = RateLimiter::availableIn($cooldownKey);

            throw ValidationException::withMessages([
                'email' => "Attends {$wait}s avant de redemander un code.",
            ]);
        }

        RateLimiter::hit($cooldownKey, self::COOLDOWN_SECONDS);
    }

    private function assertEmailFormat(string $email): void
    {
        if (!preg_match(self::EMAIL_REGEX, $email)) {
            throw ValidationException::withMessages([
                'email' => 'Format invalide (prenom.nom@imt-atlantique.net)',
            ]);
        }
    }

    private function setTemporaryBlockForActor(string $action, string $email, string $ip, ?string $sessionId): void
    {
        $blockKey = TemporaryIpBlockMiddleware::blockKeyForService($email, $ip, $sessionId);
        TemporaryIpBlockMiddleware::setTemporaryBlock($blockKey);
    }

    /**
     * @throws RandomException
     */
    private function generateNumericCode(int $length): string
    {
        $max = (10 ** $length) - 1;
        $n = random_int(0, $max);
        return str_pad((string) $n, $length, '0', STR_PAD_LEFT);
    }
}
