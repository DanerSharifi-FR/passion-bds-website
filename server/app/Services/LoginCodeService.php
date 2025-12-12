<?php

namespace App\Services;

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
    private const EXPIRES_MINUTES = 10;
    private const MAX_ATTEMPTS_PER_CODE = 5;

    // Cooldown strict : 30s entre 2 demandes depuis la même IP
    private const IP_COOLDOWN_SECONDS = 30;

    // Anti-spam : demandes de code (par email + ip)
    private const REQUEST_LIMIT = 6;
    private const REQUEST_WINDOW_SECONDS = 600;  // 10 minutes

    // Anti-bruteforce : vérifs de code (par email + ip)
    private const VERIFY_LIMIT = 20;
    private const VERIFY_WINDOW_SECONDS = 600;   // 10 minutes

    /**
     * @throws ValidationException
     */
    public function requestCode(string $email, string $ip, ?string $userAgent): void
    {
        $email = mb_strtolower(trim($email));

        // Cooldown strict par IP: 1 demande toutes les 30s
        $cooldownKey = 'login_code_cooldown_ip:' . $ip;
        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            $wait = RateLimiter::availableIn($cooldownKey);

            throw ValidationException::withMessages([
                'email' => "Attends {$wait}s avant de redemander un code.",
            ]);
        }
        RateLimiter::hit($cooldownKey, self::IP_COOLDOWN_SECONDS);

        if (!preg_match(self::EMAIL_REGEX, $email)) {
            throw ValidationException::withMessages([
                'email' => 'Format invalide (prenom.nom@imt-atlantique.net)',
            ]);
        }

        // Rate limit : empêcher spam d’envoi de codes (par email + ip)
        $rateKey = 'login_code_req:' . sha1($email . '|' . $ip);
        if (RateLimiter::tooManyAttempts($rateKey, self::REQUEST_LIMIT)) {
            throw ValidationException::withMessages([
                'email' => 'Trop de demandes. Réessaie plus tard.',
            ]);
        }
        RateLimiter::hit($rateKey, self::REQUEST_WINDOW_SECONDS);

        DB::transaction(function () use ($email, $ip, $userAgent) {
            // Crée l’utilisateur si absent (pas d’énumération, et c’est pratique)
            $user = User::where('university_email', $email)->lockForUpdate()->first();

            if (!$user) {
                $user = new User();
                $user->university_email = $email;
                $user->display_name = User::displayNameFromUniversityEmail($email);
                $user->is_active = true;
                $user->save();
            } elseif (!$user->display_name) {
                // don’t overwrite if already set
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

            // Envoi synchro (pas besoin de worker). Plus tard : ->queue()
            Mail::to($email)->send(new LoginCodeMail(
                code: $code,
                expiresHuman: 'dans ' . self::EXPIRES_MINUTES . ' minutes',
            ));
        });
    }

    /**
     * @throws ValidationException
     */
    public function requestAdminCode(string $email, string $ip, ?string $userAgent): void
    {
        $email = mb_strtolower(trim($email));

        // cooldown IP (same as user)
        $cooldownKey = 'admin_login_code_cooldown_ip:' . $ip;
        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            $wait = RateLimiter::availableIn($cooldownKey);
            throw ValidationException::withMessages(['email' => "Attends {$wait}s avant de redemander un code."]);
        }
        RateLimiter::hit($cooldownKey, self::IP_COOLDOWN_SECONDS);

        if (!preg_match(self::EMAIL_REGEX, $email)) {
            throw ValidationException::withMessages(['email' => 'Format invalide (prenom.nom@imt-atlantique.net)']);
        }

        // rate limit email+ip
        $rateKey = 'admin_login_code_req:' . sha1($email . '|' . $ip);
        if (RateLimiter::tooManyAttempts($rateKey, self::REQUEST_LIMIT)) {
            throw ValidationException::withMessages(['email' => 'Trop de demandes. Réessaie plus tard.']);
        }
        RateLimiter::hit($rateKey, self::REQUEST_WINDOW_SECONDS);

        DB::transaction(function () use ($email, $ip, $userAgent) {
            $user = User::where('university_email', $email)->lockForUpdate()->first();

            // MUST exist
            if (!$user || !$user->is_active) {
                throw ValidationException::withMessages(['email' => 'Accès admin refusé.']);
            }

            $user->loadMissing('roles');

            // MUST have at least one admin role
            if (!$user->hasAnyRole(self::ADMIN_ROLES)) {
                throw ValidationException::withMessages(['email' => 'Accès admin refusé.']);
            }

            // invalidate old codes
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
    public function verifyAdminCode(string $email, string $code, string $ip): User
    {
        $user = $this->verifyCode($email, $code, $ip);

        $user->loadMissing('roles');

        if (!$user->hasAnyRole(self::ADMIN_ROLES)) {
            throw ValidationException::withMessages(['email' => 'Accès admin refusé.']);
        }

        return $user;
    }

    /**
     * @throws ValidationException
     */
    public function verifyCode(string $email, string $code, string $ip): User
    {
        $email = Str::lower(trim($email));
        $code  = preg_replace('/\D+/', '', trim($code)); // keep digits only

        // Defensive validation (backend must not trust frontend)
        $mailFormat = '/^[a-z0-9][a-z0-9-]*\.[a-z0-9][a-z0-9-]*@imt-atlantique\.net$/i';
        if (!preg_match($mailFormat, $email)) {
            throw ValidationException::withMessages([
                'email' => "Email invalide (prenom.nom@imt-atlantique.net).",
            ]);
        }

        if (strlen($code) !== 4) {
            throw ValidationException::withMessages([
                'code' => 'Code invalide (4 chiffres).',
            ]);
        }

        return DB::transaction(function () use ($email, $code) {

            $user = User::where('university_email', $email)
                ->lockForUpdate()
                ->first();

            if (!$user || !$user->is_active) {
                throw ValidationException::withMessages([
                    'email' => "Compte introuvable ou désactivé.",
                ]);
            }

            // ONLY LATEST non-expired code row is considered
            $latest = LoginCode::where('user_id', $user->id)
                ->where('expires_at', '>', now())
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if (!$latest || $latest->used_at) {
                throw ValidationException::withMessages([
                    'code' => "Code expiré ou déjà utilisé. Redemande un code.",
                ]);
            }

            if (!Hash::check($code, $latest->code_hash)) {
                $latest->attempt_count++;

                // Hard stop after too many tries
                if ($latest->attempt_count >= 6) {
                    // force user to request a new code
                    $latest->used_at = now();
                }

                $latest->save();

                throw ValidationException::withMessages([
                    'code' => "Code incorrect.",
                ]);
            }

            // Success
            $latest->used_at = now();
            $latest->save();

            // Safety: kill any other unused codes (should already be killed in requestCode, but keep it)
            LoginCode::where('user_id', $user->id)
                ->whereNull('used_at')
                ->where('id', '!=', $latest->id)
                ->update(['used_at' => now()]);

            $user->last_login_at = now();
            $user->save();

            return $user;
        });
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
