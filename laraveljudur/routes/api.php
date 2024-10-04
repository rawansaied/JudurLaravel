<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VolunteerAnalyticsController;
use App\Http\Controllers\ContactUsController;
use Illuminate\Support\Facades\Mail;

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

Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});


//volunteer 
Route::get('/volunteer-summary/{volunteerId}', [VolunteerAnalyticsController::class, 'getVolunteerSummary']);
Route::get('/volunteer-activity/{volunteerId}', [VolunteerAnalyticsController::class, 'getVolunteerActivityOverTime']);
Route::get('/volunteer/by-user/{userId}', [VolunteerAnalyticsController::class, 'getVolunteerIdByUserId']);
Route::get('/volunteer-events/{volunteerId}', [VolunteerAnalyticsController::class, 'getVolunteerEvents']);
Route::get('/examiner-lands/{volunteerId}', [VolunteerAnalyticsController::class, 'getExaminerLandData']);
Route::get('/land-inspections/{volunteerId}', [VolunteerAnalyticsController::class, 'getLandInspections']);



// Route to show the contact form
Route::get('/contact', [ContactUsController::class, 'showContactForm'])->name('contact.form');

// Route to handle the form submission
Route::post('/contact/send', [ContactUsController::class, 'sendContactMessage'])->name('contact.send'); // Ensure this matches your method name

Route::get('/send-test-email', function () {
    Mail::raw('This is a test email from Judur!', function ($message) {
        $message->to('your-email@example.com')
                ->subject('Test Email');
    });
    return 'Test email sent!';
});





