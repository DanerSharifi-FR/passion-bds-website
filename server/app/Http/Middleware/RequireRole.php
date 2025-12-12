<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        if (!$user) abort(401);

        $user->loadMissing('roles');

        if ($user->hasRole('ROLE_SUPER_ADMIN')) {
            return $next($request);
        }

        if (!$user->hasAnyRole($roles)) {
            abort(403);
        }

        return $next($request);
    }

}
