<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User;
use App\Models\Volunteer;
use App\Models\Donor;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function getProfile($id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->role_id == 2) { // Donor
            $donor = Donor::where('user_id', $user->id)->first();

            // Check if the donor exists
            if (!$donor) {
                return response()->json([
                    'message' => 'Donor information not found'
                ], 404);
            }

            $latestItemDonation = $donor->latestItemDonation ?? null;

            return response()->json([
                'user' => $user,
                'type' => 'donor',
                'donor_info' => $donor,
                'latest_item_donation' => $latestItemDonation,
            ]);
        } elseif ($user->role_id == 3) { // Volunteer
            $volunteer = Volunteer::where('user_id', $user->id)->first();

            // Check if the volunteer exists
            if (!$volunteer) {
                return response()->json([
                    'message' => 'Volunteer information not found'
                ], 404);
            }

            return response()->json([
                'user' => $user,
                'type' => 'volunteer',
                'volunteer_info' => $volunteer,
            ]);
        }

        return response()->json([
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'age' => $user->age,
                'phone' => $user->phone,
                'password' => $user->password,
                'profile_picture' => asset('storage/' . $user->profile_picture),
            ],
            'type' => 'unknown'
        ]);
    }

    public function updateProfile(Request $request, $id)
    {
        Log::info('Update request received', ['id' => $id]);
    
        // Validate the input data
        $request->validate([
            'name' => 'string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'age' => 'integer|min:1',
            'phone' => 'string|size:10',
            'password' => 'nullable|string|min:6', // Only update password if provided
            'profile_picture' => 'nullable|string' // Expecting Base64 image string
        ]);
    
        // Find the user by ID
        $user = User::findOrFail($id);
    
        // Update the user's information
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->age = $request->input('age');
        $user->phone = $request->input('phone');
    
        // Handle password update if provided
        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }
    
        // Handle profile picture if provided as Base64
        if ($request->filled('profile_picture')) {
            $imageData = $request->input('profile_picture');
            
            // Extract the file extension
            preg_match("/^data:image\/(.*?);base64,/", $imageData, $match);
            $extension = $match[1];
            
            // Remove the Base64 header
            $image = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
            $image = str_replace(' ', '+', $image);
            
            // Decode the Base64 string
            $imageName = time() . '.' . $extension;
            Storage::put('public/profile_pictures/' . $imageName, base64_decode($image));
    
            // Save the image name to the database
            $user->profile_picture = $imageName;
        }
    
        $user->save();
    
        Log::info('User updated successfully', ['user' => $user]);
    
        // Return a success response
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
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
