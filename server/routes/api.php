<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Ces routes sont automatiquement préfixées par /api (voir bootstrap/app.php)
| et utilisent le groupe de middleware "api" par défaut de Laravel.
|
| Exemple:
|   GET /api/ping  ->  {"message": "pong"}
|
*/

Route::get('/ping', function (Request $request) {
    return response()->json([
        'message' => 'pong',
    ]);
});
