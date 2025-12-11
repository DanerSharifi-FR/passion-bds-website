<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AlloAdminController;
use App\Http\Controllers\Admin\AlloSlotAdminController;
use App\Http\Controllers\Admin\AlloUsageAdminController;
use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PointTransactionAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
| These routes are loaded by the web RouteServiceProvider and all of them
| will be assigned to the "web" middleware group.
|
*/

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/poles', function () {
    return view('team');
})->name('team');

Route::get('/galerie', function () {
    return view('gallery');
})->name('gallery');

/*
|--------------------------------------------------------------------------
| Admin login (séparé du login étudiant)
|--------------------------------------------------------------------------
|
| /admin/login utilise email + code 4 chiffres via login_codes.
|


Route::prefix('admin')
    ->group(function (): void {
        Route::get('/login', [AdminLoginController::class, 'showLoginForm'])
            ->name('admin.login');

        Route::post('/login/request-code', [AdminLoginController::class, 'requestCode'])
            ->name('admin.login.requestCode');

        Route::post('/login/verify-code', [AdminLoginController::class, 'verifyCode'])
            ->name('admin.login.verifyCode');
    });*/

/*
|--------------------------------------------------------------------------
| Admin routes protégées
|--------------------------------------------------------------------------
|
| /admin/* protégé par auth, puis sous-groupes par rôles.
|

Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        // Dashboard admin
        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        // Logout admin
        Route::post('/logout', [AdminLoginController::class, 'logout'])
            ->name('logout');

*/
        /*
         |------------------------------------------------------------------
         | Zone Gamemaster + SuperAdmin
         |------------------------------------------------------------------

        Route::middleware('role:ROLE_GAMEMASTER,ROLE_SUPER_ADMIN')
            ->group(function (): void {
                // Allos – listing
                Route::get('/allos', [AlloAdminController::class, 'index'])
                    ->name('allos.index');

                // Allos – création
                Route::get('/allos/create', [AlloAdminController::class, 'create'])
                    ->name('allos.create');

                Route::post('/allos', [AlloAdminController::class, 'store'])
                    ->name('allos.store');

                // Allos – édition
                Route::get('/allos/{allo}/edit', [AlloAdminController::class, 'edit'])
                    ->name('allos.edit');

                Route::put('/allos/{allo}', [AlloAdminController::class, 'update'])
                    ->name('allos.update');

                // Allo slots – listing pour un allo donné
                Route::get('/allos/{allo}/slots', [AlloSlotAdminController::class, 'index'])
                    ->name('allos.slots.index');

                // Allo slots – génération automatique des slots
                Route::post('/allos/{allo}/slots/generate', [AlloSlotAdminController::class, 'generate'])
                    ->name('allos.slots.generate');

                // Allo usages – listing des réservations pour un allo
                Route::get('/allos/{allo}/usages', [AlloUsageAdminController::class, 'index'])
                    ->name('allos.usages.index');

                // Allo usages – accepter une demande
                Route::post('/allos/{allo}/usages/{usage}/accept', [AlloUsageAdminController::class, 'accept'])
                    ->name('allos.usages.accept');

                // Allo usages – marquer comme réalisé
                Route::post('/allos/{allo}/usages/{usage}/done', [AlloUsageAdminController::class, 'markDone'])
                    ->name('allos.usages.done');

                // Allo usages – annuler une demande
                Route::post('/allos/{allo}/usages/{usage}/cancel', [AlloUsageAdminController::class, 'cancel'])
                    ->name('allos.usages.cancel');

                // Points – listing des transactions
                Route::get('/points', [PointTransactionAdminController::class, 'index'])
                    ->name('points.index');

                // Points – création d’une transaction manuelle
                Route::get('/points/create', [PointTransactionAdminController::class, 'create'])
                    ->name('points.create');

                Route::post('/points', [PointTransactionAdminController::class, 'store'])
                    ->name('points.store');

                Route::post('/points/manual', [PointTransactionAdminController::class, 'store'])
                    ->name('points.store');
            });
*/
        /*
         |------------------------------------------------------------------
         | Zone SuperAdmin ONLY – gestion des comptes et rôles
         |------------------------------------------------------------------

        Route::middleware(['web', 'auth', 'role:ROLE_SUPER_ADMIN'])
            ->group(function () {
                Route::get('/users', [UserAdminController::class, 'index'])->name('users.index');
                Route::get('/users/create', [UserAdminController::class, 'create'])->name('users.create');
                Route::post('/users', [UserAdminController::class, 'store'])->name('users.store');
                Route::get('/users/{user}/edit', [UserAdminController::class, 'edit'])->name('users.edit');
                Route::put('/users/{user}', [UserAdminController::class, 'update'])->name('users.update');
                Route::delete('/users/{user}', [UserAdminController::class, 'destroy'])->name('users.destroy');
            });
    });
 */
