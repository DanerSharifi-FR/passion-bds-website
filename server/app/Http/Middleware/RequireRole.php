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
        if (!$user) {
            return redirect()->route('login')->with('error', 'You do not have the required role to access this page.');
        }

        $user->loadMissing('roles');

        if ($user->hasRole('ROLE_SUPER_ADMIN')) {
            return $next($request);
        }

        if (!$user->hasAnyRole($roles)) {
            // redirect to connection page
            return redirect()->route('login')->with('error', 'You do not have the required role to access this page.');
        }

        return $next($request);
    }

}
