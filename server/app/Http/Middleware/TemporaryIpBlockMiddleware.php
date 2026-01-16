<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class TemporaryIpBlockMiddleware
{
    private const BLOCK_MINUTES = 60;

    // This key lives in the same store as your existing RateLimiter keys (so it persists)
    private const BLOCK_PREFIX = 'tmp_block:';

    public function handle(Request $request, Closure $next): Response
    {
        // Avoid breaking static assets
        if (
            $request->is('assets/*') ||
            $request->is('build/*') ||
            $request->is('storage/*') ||
            $request->is('favicon.ico')
        ) {
            return $next($request);
        }

        // Super-admin bypass
        if (auth()->check()) {
            $connectedUserRoles = DB::table('user_roles')
                ->join('roles', 'roles.id', '=', 'user_roles.role_id')
                ->where('user_roles.user_id', auth()->id() ?? 0)
                ->pluck('roles.name')
                ->all();

            if (in_array('ROLE_SUPER_ADMIN', $connectedUserRoles, true)) {
                return $next($request);
            }
        }

        $blockKey = self::resolveBlockKey($request);
        $limiterKey = self::BLOCK_PREFIX . $blockKey;

        // If blocked => show Blade on HTML requests, JSON on AJAX
        if (RateLimiter::tooManyAttempts($limiterKey, 1)) {
            $remainingSeconds = RateLimiter::availableIn($limiterKey);
            $blockedUntil = CarbonImmutable::now()->addSeconds($remainingSeconds);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Trop de tentatives. Réessaie plus tard.',
                    'errors' => [
                        'code' => ['Trop de tentatives. Réessaie plus tard.'],
                    ],
                    'blocked_until' => $blockedUntil->toISOString(),
                    'remaining_seconds' => $remainingSeconds,
                ], 429)
                    ->header('Retry-After', (string) $remainingSeconds)
                    ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
            }

            return response()
                ->view('blocked.ip', [
                    'blockedUntilIso' => $blockedUntil->toISOString(),
                    'remainingSeconds' => $remainingSeconds,
                    'ipAddress' => (string) $request->ip(),
                    'triggerAction' => 'RATE_LIMIT',
                    'blockKey' => $blockKey,
                ], 429)
                ->header('Retry-After', (string) $remainingSeconds)
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        }

        return $next($request);
    }

    /**
     * Priority you asked:
     * 1) si connecté => mail
     * 2) sinon => session
     */
    public static function resolveBlockKey(Request $request): string
    {
        if (auth()->check()) {
            $email = mb_strtolower(trim((string) (auth()->user()->university_email ?? '')));
            if ($email !== '') {
                return 'mail:' . $email;
            }

            return 'user:' . (string) (auth()->id() ?? 0);
        }

        // Session-based (NOT IP)
        $sid = $request->session()->getId();
        return 'sess:' . $sid;
    }

    /**
     * Call this where the rate limit happens.
     * This is what makes refresh show the blade.
     */
    public static function setTemporaryBlock(string $blockKey): void
    {
        $limiterKey = self::BLOCK_PREFIX . $blockKey;

        // Don’t extend the ban on repeated hits (optional, but sane)
        if (!RateLimiter::tooManyAttempts($limiterKey, 1)) {
            RateLimiter::hit($limiterKey, self::BLOCK_MINUTES * 60);
        }
    }

    public static function blockKeyForService(string $email, string $ip, ?string $sessionId): string
    {
        $email = mb_strtolower(trim($email));

        // If logged AND same email => block by mail
        if (auth()->check()) {
            $authMail = mb_strtolower(trim((string) (auth()->user()->university_email ?? '')));
            if ($authMail !== '' && $authMail === $email) {
                return 'mail:' . $email;
            }
        }

        // Else: session (your priority #2)
        if ($sessionId) {
            return 'sess:' . $sessionId;
        }

        // Last resort (should not happen for your web.php routes)
        return 'ip:' . $ip;
    }
}
