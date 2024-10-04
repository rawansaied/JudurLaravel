<?php
namespace App\Http\Controllers;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User;
use App\Models\Volunteer;
use App\Models\Donor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log; 
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
        }
        
        elseif ($user->role_id == 3) { // Volunteer
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
    
        return response()->json([ 'user' => [
            'name' => $user->name,
            'email' => $user->email,
            'age' => $user->age,
            'phone' => $user->phone,
            'password'=>$user->password,
            'profile_picture' => asset('storage/' . $user->profile_picture), 
        ],
        'type' => 'unknown']);
    }
    public function updateProfile(Request $request, $id)
    {
        // Validate the input data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'age' => 'required|integer|min:1',
            'phone' => 'required|string|size:10',
            'password' => 'nullable|string|min:6', // Only update password if provided
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png|max:2048' // Profile picture validation
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
    
        // Handle profile picture upload if provided
        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $filePath = $file->store('profile_pictures', 'public');
            $user->profile_picture = $filePath;
        }
    
        // Save the changes
        $user->save();
    
        // Return a success response
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }
    
    

}
