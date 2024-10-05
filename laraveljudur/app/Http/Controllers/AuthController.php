<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Donor;
use App\Models\Volunteer;
use Illuminate\Support\Facades\DB;  
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    // Register a new user
    public function register(Request $request)
    {
        Log::info('Register request received', $request->all());
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'required',
            'age' => 'required',
            'phone' => 'required',
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $request->role_id,
                'age' => $request->age,
                'phone' => $request->phone,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('User registered successfully', ['user_id' => $user->id]);

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);
        } catch (\Exception $e) {
            Log::error('Error registering user: ' . $e->getMessage());
            return response()->json(['message' => 'Error registering user'], 500);
        }
    }

    public function registerDonor(Request $request)
    {
        Log::info('Register Donor request received', $request->all());

        $validated = $request->validate([
            'name' => 'required|string|min:2',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required',
            'age' => 'required|integer',
            'password' => 'required|min:6',
            'profile_picture' => 'nullable|string',  // Validate base64 string if provided
        ]);
    
        // Handle Base64 Image Upload
        if ($request->profile_picture) {
            // Decode the base64 image
            $imageData = base64_decode($request->profile_picture);
            $imageName = time() . '.png';  // Create a unique name for the image
            $imagePath = 'profile_pictures/' . $imageName;
    
            // Store the image in the public storage folder
            Storage::disk('public')->put($imagePath, $imageData);
    
            $validated['profile_picture'] = $imagePath;
        }
    
        // Create the user (example with User model)
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'age' => $validated['age'],
            'password' => bcrypt($validated['password']),
            'profile_picture' => $validated['profile_picture'] ?? null,  // Save the file path to the database
        ]);
    
        return response()->json(['message' => 'Registration successful', 'user' => $user], 201);
    
    }

    // Register a volunteer
    public function registerVolunteer(Request $request)
    {
        Log::info('Register Volunteer request received', $request->all());

        $validated = $request->validate([
            'name' => 'required|string|min:2',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'age' => 'required|integer',
            'phone' => 'required|string',
            'skills' => 'required|string',
            'availability' => 'required|string',
            'aim' => 'required|string',
        ]);

        try {
            Log::info('Creating volunteer user', ['email' => $validated['email']]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => 3, // Role ID for Volunteer
                'age' => $validated['age'],
                'phone' => $validated['phone'],
            ]);

            Log::info('Creating volunteer record', ['user_id' => $user->id]);

            Volunteer::create([
                'user_id' => $user->id,
                'skills' => $validated['skills'],
                'availability' => $validated['availability'],
                'aim' => $validated['aim'],
            ]);

            Log::info('Volunteer registered successfully', ['user_id' => $user->id]);

            return response()->json(['message' => 'Volunteer registered successfully', 'data' => $user], 201);
        } catch (\Exception $e) {
            Log::error('Error registering volunteer: ' . $e->getMessage());
            return response()->json(['message' => 'Error registering volunteer'], 500);
        }
    }

    // Log in a user
    public function login(Request $request)
    {
        Log::info('Login request received', $request->only('email'));

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            Log::warning('Invalid login attempt', ['email' => $request->email]);
            return response()->json(['message' => 'Invalid login details'], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        Log::info('User logged in', ['user_id' => $user->id]);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'message' => 'You are logged in',
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            Log::info('User logged out', ['user_id' => $request->user()->id]);

            return response()->json(['message' => 'Logged out successfully']);
        } catch (\Exception $e) {
            Log::error('Logout failed: ' . $e->getMessage());
            return response()->json(['error' => 'Logout failed', 'message' => $e->getMessage()], 500);
        }
    }
}
