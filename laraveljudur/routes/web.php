<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('login');
});
use App\Http\Controllers\AuthController as ControllersAuthController;
Route::get('login/google', [ControllersAuthController::class, 'redirectToGoogle'])->name('login.google');
Route::get('login/google/callback', [ControllersAuthController::class, 'handleGoogleCallback']);
