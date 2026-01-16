<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TemporaryIpBlockMiddleware
{
    private const BLOCK_MINUTES = 60;

    // Only these actions should trigger a block.
    private const BLOCKING_ACTIONS = [
        'AUTH.LOGIN_CODE.REQUEST_RATE_LIMIT',
        'AUTH.LOGIN_CODE.VERIFY_RATE_LIMIT',
        'AUTH.ADMIN_LOGIN_CODE.REQUEST_RATE_LIMIT',
    ];

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

        // Avoid blocking if connected admin
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

        // Get identifiers for rate limiting (session-based, not IP-based)
        $identifiers = $this->getRateLimitIdentifiers($request);

        // Check if any identifier is blocked
        $query = AuditLog::query()
            ->whereIn('action', self::BLOCKING_ACTIONS)
            ->orderByDesc('created_at');

        // Check by session_id first (most specific for guests)
        if ($identifiers['session_id']) {
            $query->where(function ($q) use ($identifiers) {
                $q->where('session_id', $identifiers['session_id']);
                if ($identifiers['user_id']) {
                    $q->orWhere('user_id', $identifiers['user_id']);
                }
            });
        } elseif ($identifiers['user_id']) {
            $query->where('user_id', $identifiers['user_id']);
        } else {
            // Fallback to IP only if no session exists (API clients, bots, etc.)
            $query->where('ip_address', $identifiers['ip']);
        }

        $last = $query->first();

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
                'ipAddress' => $identifiers['ip'],
                'sessionId' => $identifiers['session_id'] ? substr($identifiers['session_id'], 0, 8) . '...' : null,
                'triggerAction' => $last->action,
            ], 429)
            ->header('Retry-After', (string) $remainingSeconds)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * Get identifiers for rate limiting.
     * Priority: session_id > user_id > ip (fallback)
     */
    private function getRateLimitIdentifiers(Request $request): array
    {
        return [
            'session_id' => $request->session()->getId(),
            'user_id' => auth()->id(),
            'ip' => (string) $request->ip(),
        ];
    }
}
