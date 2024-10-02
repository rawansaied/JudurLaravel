<?php

use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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



// Dashboard Routes Start

Route::get('/donors', [AdminController::class, 'getDonors']);
Route::get('/volunteers', [AdminController::class, 'getVolunteers']);
Route::get('/donor/{id}', [AdminController::class, 'donorDetails']);
Route::get('/volunteer/{id}', [AdminController::class, 'volunteerDetails']);

Route::get('/pending-volunteers', [AdminController::class, 'getPendingVolunteers']);
Route::put('/volunteer/{id}/status', [AdminController::class, 'updateStatus']);

// Dashboard Routes End