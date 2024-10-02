<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\DonorController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Register a new user
Route::post('/register', [AuthController::class, 'register']);
Route::post('/register/donor', [AuthController::class, 'registerDonor']);
Route::post('/register/volunteer', [AuthController::class, 'registerVolunteer']);

Route::post('/login', [AuthController::class, 'login']);



// Logout a user (requires authentication)
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);


////

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/donate-land', [DonationController::class, 'donateLand']);
    Route::post('/donate-item', [DonationController::class, 'donateItem']);
    Route::post('/donate-money', [DonationController::class, 'donateMoney']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/donor/dashboard', [DonorController::class, 'dashboard']);
    Route::get('/donor/view-details/{type}', [DonorController::class, 'viewDetails']);
});



Route::middleware('auth:sanctum')->group(function () {
    Route::get('/donor/dashboard', [DonorController::class, 'dashboard'])->name('donor.dashboard');
});