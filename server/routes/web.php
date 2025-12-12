<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminUsersApiController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Admin\AlloController;
use App\Http\Controllers\Admin\ChallengeController;
use App\Http\Controllers\Admin\TransactionApiController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Api\LeaderboardApiController;
use App\Http\Controllers\Auth\CodeAuthController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::controller(PageController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/poles', 'team')->name('team');
    Route::get('/galerie', 'gallery')->name('gallery');
    Route::get('/classement', 'leaderboard')->name('leaderboard');
    Route::get('/connexion', 'login')->middleware('guest')->name('login');
});

Route::get('/api/leaderboard', [LeaderboardApiController::class, 'index'])->name('api.leaderboard');

Route::prefix('auth')->as('auth.')->controller(CodeAuthController::class)->group(function () {
    Route::post('/request-code', 'requestCode')->middleware('guest')->name('request-code');
    Route::post('/verify-code',  'verifyCode')->middleware('guest')->name('verify-code');
    Route::post('/logout',       'logout')->middleware('auth')->name('logout');
});

Route::prefix('admin')->as('admin.')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::get('/login', fn () => view('admin.login'))->name('login');

        Route::prefix('auth')->as('auth.')->controller(AdminAuthController::class)->group(function () {
            Route::post('/request-code', 'requestCode')->name('request-code');
            Route::post('/verify-code',  'verifyCode')->name('verify-code');
        });
    });

    Route::middleware('auth')->group(function () {

        Route::get('/', [AdminController::class, 'dashboard'])
            ->middleware('role:ROLE_SUPER_ADMIN,ROLE_BLOGGER,ROLE_GAMEMASTER,ROLE_SHOP,ROLE_TEAM')
            ->name('dashboard');

        Route::middleware('role:ROLE_SUPER_ADMIN,ROLE_GAMEMASTER')->group(function () {
            Route::get('/challenges', [ChallengeController::class, 'index'])->name('challenges');
            Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');
            Route::get('/allos', [AlloController::class, 'index'])->name('allos');
        });

        Route::middleware('role:ROLE_SUPER_ADMIN,ROLE_GAMEMASTER')
            ->prefix('api')
            ->as('api.')
            ->group(function () {
                Route::get('/transactions', [TransactionApiController::class, 'index'])->name('transactions.index');
                Route::post('/transactions/manual', [TransactionApiController::class, 'storeManual'])->name('transactions.manual.store');
                Route::get('/students', [TransactionApiController::class, 'students'])->name('students.index');
            });

        Route::middleware('role:ROLE_SUPER_ADMIN')->group(function () {
            Route::get('/users', [AdminUsersController::class, 'index'])->name('users');

            Route::prefix('api')->name('api.')->group(function () {
                Route::get('/users', [AdminUsersApiController::class, 'index'])->name('users');
                Route::post('/users', [AdminUsersApiController::class, 'store'])->name('users.store');
                Route::put('/users/{user}/roles', [AdminUsersApiController::class, 'updateRoles'])->name('users.roles.update');
                Route::delete('/users/{user}', [AdminUsersApiController::class, 'destroy'])->name('users.destroy');
            });
        });
    });
});
