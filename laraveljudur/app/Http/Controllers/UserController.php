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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function getProfile($id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->role_id == 2) { // Donor
            $donor = Donor::where('user_id', $user->id)->first();

            if (!$donor) {
                return response()->json(['message' => 'Donor information not found'], 404);
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

            if (!$volunteer) {
                return response()->json(['message' => 'Volunteer information not found'], 404);
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
                'age' => $user->age,
                'phone' => $user->phone,
                'profile_picture' => $user->profile_picture ? asset('storage/' . $user->profile_picture) : null,
            ],
            'type' => 'unknown'
        ]);
    }

    public function updateProfile(Request $request, $id): JsonResponse
    {
        Log::info('Update request received', ['id' => $id]);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'age' => 'integer|min:1',
            'phone' => 'string|size:11',
            'password' => 'nullable|string|min:6',
            'profile_picture' => 'nullable|string' // Expecting Base64 image string
        ]);

        $user = User::findOrFail($id);

        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->age = $request->input('age');
        $user->phone = $request->input('phone');

        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        if ($request->filled('profile_picture')) {
            $imageData = $request->input('profile_picture');
            preg_match("/^data:image\/(.*?);base64,/", $imageData, $match);
            $extension = $match[1];
            $image = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
            $image = str_replace(' ', '+', $image);
            $imageName = time() . '.' . $extension;
            Storage::put('public/profile_pictures/' . $imageName, base64_decode($image));
            $user->profile_picture = $imageName;
        }

        $user->save();

        Log::info('User updated successfully', ['user' => $user]);

        return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
    }

    public function index(): JsonResponse
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

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'age' => 'required|integer|min:0',
            'phone' => 'required|string|max:15',
            'role_id' => 'required|in:5,6'
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'age' => $validatedData['age'],
            'phone' => $validatedData['phone'],
            'role_id' => $validatedData['role_id']
        ]);

        return response()->json($user);
    }

    public function show($id): JsonResponse
    {
        $user = User::with('role')->findOrFail($id);

        return response()->json($user);
    }

    public function update(Request $request, $id): JsonResponse
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

    public function destroy($id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
