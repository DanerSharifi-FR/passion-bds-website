<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AlloApiController;

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

Route::middleware('web')->group(function () {
    Route::get('/allos', [AlloApiController::class, 'index'])->name('allos.api.index');
    Route::post('/allos/bookings', [AlloApiController::class, 'storeBooking'])
        ->middleware('auth')
        ->name('allos.api.bookings.store');
    Route::get('/allos/bookings', [AlloApiController::class, 'bookings'])
        ->middleware('auth')
        ->name('allos.api.bookings.index');
    Route::put('/allos/bookings/{booking}', [AlloApiController::class, 'updateBooking'])
        ->middleware('auth')
        ->name('allos.api.bookings.update');
});
