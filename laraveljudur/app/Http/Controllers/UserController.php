<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Volunteer;
use App\Models\Donor;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function getProfile($id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->role_id == 2) { // Donor
            $donor = Donor::where('user_id', $user->id)->first();
            return response()->json([
                'user' => $user,
                'type' => 'donor',
                'donor_info' => $donor,
            ]);
        } elseif ($user->role_id == 3) { // Volunteer
            $volunteer = Volunteer::where('user_id', $user->id)->first();
            return response()->json([
                'user' => $user,
                'type' => 'volunteer',
                'volunteer_info' => $volunteer,
            ]);
        }

        return response()->json(['user' => $user, 'type' => 'unknown']);
    }


    public function updateProfile(Request $request, $id)
    {
        Log::info('Update Profile Request', $request->all()); // Log request data
        try {
            $user = User::findOrFail($id);
            Log::info('User found', ['user' => $user]);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'profile_picture' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            ]);

            // Update user details
            $user->name = $validated['name'];
            $user->email = $validated['email'];

            if ($request->hasFile('profile_picture')) {
                Log::info('Profile picture upload detected.');
                if ($user->profile_picture) {
                    Storage::delete('public/images/' . $user->profile_picture);
                }

                $path = $request->file('profile_picture')->store('public/images');
                $user->profile_picture = basename($path);
            }

            $user->save();

            return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
        } catch (\Exception $e) {
            Log::error('Error updating profile: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while updating the profile.'], 500);
        }
    }

    public function index()
    {
        $users = User::whereIn('role_id', [5, 6])
            ->with('role')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->name,
                ];
            });

        return response()->json($users);
    }


    public function store(Request $request)
{
    $validatedData = $request->validate([
        'name' => 'required|string',
        'email' => 'required|email|unique:users',
        'password' => 'required|string|min:6',
        'role_id' => 'required|in:6,7' 
    ]);

    $user = User::create([
        'name' => $validatedData['name'],
        'email' => $validatedData['email'],
        'password' => bcrypt($validatedData['password']),
        'role_id' => $validatedData['role_id']
    ]);

    return response()->json($user);
}
public function show($id)
{
    $user = User::with('role')->findOrFail($id);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    return response()->json($user);
}


public function update(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users,email,' . $id,
        'password' => 'nullable|string|min:6',
        'role_id' => 'required|exists:roles,id',
        'age' => 'required|integer|min:0',
        'phone' => 'required|string|max:15',
    ]);

    $user = User::findOrFail($id);
    $user->name = $request->name;
    $user->email = $request->email;

    if ($request->password) {
        $user->password = Hash::make($request->password);
    }

    $user->role_id = $request->role_id;
    $user->age = $request->age;
    $user->phone = $request->phone;

    $user->save();

    return response()->json(['message' => 'User updated successfully']);
}



    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
    

}
