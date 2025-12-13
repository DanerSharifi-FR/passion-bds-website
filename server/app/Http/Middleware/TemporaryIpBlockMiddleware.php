<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TemporaryIpBlockMiddleware
{
    private const BLOCK_MINUTES = 5;

    // Only these actions should trigger an IP block.
    private const BLOCKING_ACTIONS = [
        'AUTH.LOGIN_CODE.REQUEST_RATE_LIMIT',
        'AUTH.LOGIN_CODE.VERIFY_RATE_LIMIT',
        'AUTH.ADMIN_LOGIN_CODE.REQUEST_RATE_LIMIT',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $ip = (string) $request->ip();

        // Avoid breaking static assets (adjust if you serve assets differently)
        if (
            $request->is('assets/*') ||
            $request->is('build/*') ||
            $request->is('storage/*') ||
            $request->is('favicon.ico')
        ) {
            return $next($request);
        }

        $last = AuditLog::query()
            ->where('ip_address', $ip)
            ->whereIn('action', self::BLOCKING_ACTIONS)
            ->orderByDesc('created_at')
            ->first();

        if (!$last) {
            return $next($request);
        }

        $blockedUntil = $last->created_at->copy()->addMinutes(self::BLOCK_MINUTES);

        if (now()->greaterThanOrEqualTo($blockedUntil)) {
            return $next($request);
        }

        $remainingSeconds = now()->diffInSeconds($blockedUntil);

        return response()
            ->view('blocked.ip', [
                'blockedUntilIso' => $blockedUntil->toISOString(),
                'remainingSeconds' => $remainingSeconds,
                'ipAddress' => $ip,
                'triggerAction' => $last->action,
            ], 429)
            ->header('Retry-After', (string) $remainingSeconds)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }
}
