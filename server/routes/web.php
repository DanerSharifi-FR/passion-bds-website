<?php

use App\Http\Controllers\Admin\ActivitiesApiController;
use App\Http\Controllers\Admin\ActivitiesController;
use App\Http\Controllers\Admin\ActivityParticipantsApiController;
use App\Http\Controllers\Admin\ActivityPlayersController;
use App\Http\Controllers\Admin\ActivityTeamsApiController;
use App\Http\Controllers\Admin\ActivityTransactionsApiController;
use App\Http\Controllers\Admin\AdminAuditLogsApiController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminUsersApiController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Admin\AlloApiController;
use App\Http\Controllers\Admin\AlloController;
use App\Http\Controllers\Admin\ChallengeController;
use App\Http\Controllers\Admin\TransactionApiController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Api\ActivityLeaderboardApiController;
use App\Http\Controllers\Api\LeaderboardApiController;
use App\Http\Controllers\Auth\CodeAuthController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::controller(PageController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/poles', 'team')->name('team');
    Route::get('/galerie', 'gallery')->name('gallery');
    Route::get('/classement', 'leaderboard')->name('leaderboard');

    Route::get('/activities/{activity}', 'activityLeaderboard')->name('activities.leaderboard');
    Route::get('/activities', 'activities')->name('activities');

    Route::get('/allos', 'allos')->middleware('role:ROLE_SUPER_ADMIN,ROLE_BLOGGER,ROLE_GAMEMASTER,ROLE_SHOP,ROLE_TEAM')->name('allos');
    Route::get('/allos/reservations', 'alloReservations')->middleware('role:ROLE_SUPER_ADMIN,ROLE_BLOGGER,ROLE_GAMEMASTER,ROLE_SHOP,ROLE_TEAM')->name('allos.reservations');
    Route::get('/allos/{alloId}/creneaux', 'alloSlots')->middleware('role:ROLE_SUPER_ADMIN,ROLE_BLOGGER,ROLE_GAMEMASTER,ROLE_SHOP,ROLE_TEAM')->name('allos.slots');
    Route::get('/connexion', 'login')->middleware('guest')->name('login');
});

Route::get('/api/leaderboard', [LeaderboardApiController::class, 'index'])->name('api.leaderboard');

Route::prefix('api')->group(function () {
    // API “web” (same-origin, comme admin)
    Route::prefix('activities')->group(function () {
        Route::get('/', [ActivitiesApiController::class, 'index'])
            ->name('activities.api.index');

        Route::get('/{activity}/leaderboard', [ActivityLeaderboardApiController::class, 'index'])
            ->name('activities.api.leaderboard');

        Route::get('/live', [ActivitiesApiController::class, 'live']);
    });
});

Route::prefix('auth')->as('auth.')->controller(CodeAuthController::class)->group(function () {
    Route::post('/request-code', 'requestCode')->middleware('guest')->name('request-code');
    Route::post('/verify-code', 'verifyCode')->middleware('guest')->name('verify-code');
    Route::post('/logout', 'logout')->middleware('auth')->name('logout');
});

Route::prefix('admin')->as('admin.')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::get('/login', fn() => view('admin.login'))->name('login');

        Route::prefix('auth')->as('auth.')->controller(AdminAuthController::class)->group(function () {
            Route::post('/request-code', 'requestCode')->name('request-code');
            Route::post('/verify-code', 'verifyCode')->name('verify-code');
        });
    });

    Route::middleware('auth')->group(function () {

        Route::get('/', [AdminController::class, 'dashboard'])
            ->middleware('role:ROLE_SUPER_ADMIN,ROLE_BLOGGER,ROLE_GAMEMASTER,ROLE_SHOP,ROLE_TEAM')
            ->name('dashboard');

        // Admin pages (views)
        Route::middleware('role:ROLE_SUPER_ADMIN,ROLE_GAMEMASTER')->group(function () {
            Route::get('/challenges', [ChallengeController::class, 'index'])->name('challenges');
            Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');

            Route::get('/activities', [ActivitiesController::class, 'index'])->name('activities');
            Route::get('/activities/{activity}/players', [ActivityPlayersController::class, 'index'])->name('activities.players');
        });

        Route::middleware('role:ROLE_SUPER_ADMIN,ROLE_BLOGGER,ROLE_GAMEMASTER,ROLE_SHOP,ROLE_TEAM')
            ->prefix('allos')
            ->name('allos.')
            ->group(function () {
                Route::get('/', [AlloController::class, 'index'])->name('index');
                Route::get('/demandes', [AlloController::class, 'requests'])->name('requests');
                Route::get('/catalogue-creneaux', [AlloController::class, 'catalog'])->name('catalog');
            });

        // Admin API
        Route::middleware('role:ROLE_SUPER_ADMIN,ROLE_GAMEMASTER')
            ->prefix('api')
            ->as('api.')
            ->group(function () {

                // Transactions (existing)
                Route::get('/transactions', [TransactionApiController::class, 'index'])->name('transactions.index');
                Route::post('/transactions/manual', [TransactionApiController::class, 'storeManual'])->name('transactions.manual.store');

                // Students search (used by activities participants add)
                Route::get('/students', [TransactionApiController::class, 'students'])->name('students.index');

                // Activities CRUD (matches blade)
                Route::get('/activities', [ActivitiesApiController::class, 'index'])->name('activities.index');
                Route::post('/activities', [ActivitiesApiController::class, 'store'])->name('activities.store');
                Route::put('/activities/{activity}', [ActivitiesApiController::class, 'update'])->name('activities.update');

                // Activity Admins (Gamemasters) - matches blade
                Route::get('/activities/{activity}/admins', [ActivitiesApiController::class, 'listAdmins'])
                    ->name('activities.admins.index');

                Route::post('/activities/{activity}/admins', [ActivitiesApiController::class, 'addAdmin'])
                    ->name('activities.admins.store');

                Route::delete('/activities/{activity}/admins/{adminId}', [ActivitiesApiController::class, 'removeAdmin'])
                    ->name('activities.admins.delete');

                // Invitable gamemasters search (per activity) - matches blade
                Route::get('/activities/{activity}/invitable-gamemasters', [ActivitiesApiController::class, 'invitableGamemasters'])
                    ->name('activities.gamemasters.invitable');

                // Participants - matches blade
                Route::get('/activities/{activity}/participants', [ActivityParticipantsApiController::class, 'index'])
                    ->name('activities.participants.index');

                Route::post('/activities/{activity}/participants', [ActivityParticipantsApiController::class, 'store'])
                    ->name('activities.participants.store');

                Route::delete('/activities/{activity}/participants/{userId}', [ActivityParticipantsApiController::class, 'destroy'])
                    ->name('activities.participants.destroy');

                Route::get('/activities/{activity}/participants/search', [ActivityParticipantsApiController::class, 'search'])
                    ->name('activities.participants.search');

                // Activity transactions - matches blade
                Route::get('/activities/{activity}/transactions', [ActivityTransactionsApiController::class, 'index'])
                    ->name('activities.transactions.index');

                Route::post('/activities/{activity}/transactions/manual', [ActivityTransactionsApiController::class, 'storeManual'])
                    ->name('activities.transactions.manual.store');

                // Activity teams
                Route::get('/activities/{activity}/teams', [ActivityTeamsApiController::class, 'index'])
                    ->name('activities.teams.index');

                Route::post('/activities/{activity}/teams', [ActivityTeamsApiController::class, 'store'])
                    ->name('activities.teams.store');

                Route::delete('/activities/{activity}/teams/{teamId}', [ActivityTeamsApiController::class, 'destroy'])
                    ->name('activities.teams.destroy');

                Route::put('/activities/{activity}/participants/{userId}/points', [ActivityParticipantsApiController::class, 'setPoints']);

                Route::get('/audit-logs', [AdminAuditLogsApiController::class, 'index']);
            });

        Route::middleware('role:ROLE_SUPER_ADMIN,ROLE_BLOGGER,ROLE_GAMEMASTER,ROLE_SHOP,ROLE_TEAM')
            ->prefix('api')
            ->as('api.')
            ->group(function () {
                // Allos management
                Route::get('/allos', [AlloApiController::class, 'index'])->name('allos.index');
                Route::post('/allos', [AlloApiController::class, 'store'])->name('allos.store');
                Route::put('/allos/{allo}', [AlloApiController::class, 'update'])->name('allos.update');
                Route::delete('/allos/{allo}', [AlloApiController::class, 'destroy'])->name('allos.delete');
                Route::get('/allo-admins', [AlloApiController::class, 'listAdmins'])->name('allos.admins.index');
                Route::get('/allo-usages', [AlloApiController::class, 'usages'])->name('allos.usages.index');
                Route::get('/allo-visits', [AlloApiController::class, 'visits'])->name('allos.visits.index');
                Route::put('/allo-usages/{usage}', [AlloApiController::class, 'updateUsage'])->name('allos.usages.update');
            });

        // Users page + API (keep as-is)
        Route::middleware('role:ROLE_SUPER_ADMIN,ROLE_GAMEMASTER')->group(function () {
            Route::get('/logs', [AdminController::class, 'logs'])->name('logs');
            Route::get('/users', [AdminUsersController::class, 'index'])->name('users');

            Route::prefix('api')->name('api.')->group(function () {
                Route::get('/users', [AdminUsersApiController::class, 'index'])->name('users');
                Route::post('/users', [AdminUsersApiController::class, 'store'])->name('users.store');
                Route::put('/users/{user}', [AdminUsersApiController::class, 'update'])->name('users.roles.update');
                Route::delete('/users/{user}', [AdminUsersApiController::class, 'destroy'])->name('users.destroy');
            });
        });
    });
});
