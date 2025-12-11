<?php

declare(strict_types=1);

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

Route::get('/classement', function () {
    return view('leaderboard');
})->name('leaderboard');

Route::get('/connexion', function () {
    return view('login');
})->name('login');

