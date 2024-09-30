<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DonationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Register a new user
Route::post('/register', [AuthController::class, 'register']);
Route::post('/register/donor', [AuthController::class, 'registerDonor']);
Route::post('/register/volunteer', [AuthController::class, 'registerVolunteer']);

// Login a user
Route::post('/login', [AuthController::class, 'login']);

// Logout a user (requires authentication)
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);


////


// Route::middleware('auth:sanctum')->group(function () {
//     Route::post('/donate-land', [DonationController::class, 'donateLand']);
//     Route::post('/donate-item', [DonationController::class, 'donateItem']);
//     Route::post('/donate-financial', [DonationController::class, 'donateFinancial']);
// });
Route::middleware('auth:sanctum')->post('/donate-land', [DonationController::class, 'donateLand']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/donate-item', [DonationController::class, 'donateItem']);
    Route::post('/donate-money', [DonationController::class, 'donateMoney']);
});
