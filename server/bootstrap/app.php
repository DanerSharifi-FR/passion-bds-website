<?php

declare(strict_types=1);

use App\Http\Middleware\RequireRole;
use App\Http\Middleware\TemporaryIpBlockMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;


return Application::configure(
    basePath: dirname(__DIR__),
)->withRouting(
    web: __DIR__ . '/../routes/web.php',
    api: __DIR__ . '/../routes/api.php',
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
    apiPrefix: '/api',
)->withMiddleware(function ($middleware) {
    $middleware->alias([
        'role' => RequireRole::class,
    ]);
    $middleware->redirectGuestsTo(function (Request $request) {
        if ($request->expectsJson()) return null;
        if ($request->is('admin') || $request->is('admin/*')) return route('admin.login');
        return route('login');
    });
    $middleware->append(TemporaryIpBlockMiddleware::class);
})->withExceptions(
    function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            static function (Request $request): bool {
                if ($request->is('api/*')) {
                    return true;
                }

                return $request->expectsJson();
            }
        );
    },
)->create();
