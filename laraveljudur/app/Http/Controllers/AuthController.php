<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Donor;
use App\Models\Volunteer;
   // Register a donor
   use Illuminate\Support\Facades\DB;  

use Illuminate\Support\Facades\Log;


class AuthController extends Controller
{
    // Register a new user
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'required',
            'age' => 'required',
            'phone' => 'required',


        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'age' => $request->age,
            'phone' => $request->phone,


        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
 

    public function registerDonor(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'age' => 'required|integer',
            'phone' => 'required|string',
        ]);
    
        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => 2,
                'age' => $validated['age'],
                'phone' => $validated['phone'],
            ]);
    
            $donorIdNumber = uniqid('DONOR-');
    
            Donor::create([
                'user_id' => $user->id,
                'donor_id_number' => $donorIdNumber,
            ]);
    
            DB::commit();
    
            return response()->json(['message' => 'Donor registered successfully', 'data' => $user], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error registering donor: ' . $e->getMessage()], 500);
        }
    }
    
    // Register a volunteer
    public function registerVolunteer(Request $request)
    {
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
        // Create a new user record
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => 3, // Role ID for Volunteer
            'age' => $validated['age'],
            'phone' => $validated['phone'],
        ]);

        // Create a new volunteer record
        Volunteer::create([
            'user_id' => $user->id,
            'skills' => $validated['skills'],
            'availability' => $validated['availability'],
            'aim' => $validated['aim'],
        ]);

        // Return response
        return response()->json(['message' => 'Volunteer registered successfully', 'data' => $user], 201);
    }

    // Log in a user
    public function login(Request $request)
    {            \Log::info("Login attempt", $request->all());
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid login details'], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'message' => 'u r Logged ',
            'user' => $user,



        ]);
    }



    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();

            return response()->json(['message' => 'Logged out successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Logout failed', 'message' => $e->getMessage()], 500);
        }
    }
};
