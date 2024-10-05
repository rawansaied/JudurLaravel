<?php


namespace App\Http\Controllers;
use App\Models\Land;
use App\Models\LandInspection;
use App\Models\Volunteer;
use Illuminate\Http\Request;
use App\Models\LandStatus;
use Illuminate\Support\Facades\Auth;


class LandInspectionController extends Controller
{
    public function store(Request $request)
    {
        // Get the logged-in user from the token
        $user = Auth::user();
        
        // Check if the user is a volunteer with role_id = 3 and examiner status
        if ($user->role_id != 3) {
            return response()->json([
                'message' => 'Only volunteers can submit a land inspection report.'
            ], 403);
        }
    
        // Check if the user is an examiner
        $volunteer = Volunteer::where('user_id', $user->id)->first();
        if (!$volunteer || $volunteer->examiner != 1) {
            return response()->json([
                'message' => 'Only examiners can submit a land inspection report.'
            ], 403);
        }
    
        // Validate the request data
        $validated = $request->validate([
            'land_id' => 'required|exists:lands,id',
            'date' => 'required|date',
            'hygiene' => 'required|string|max:255',
            'capacity' => 'required|integer',
            'electricity_supply' => 'required|boolean', // This must be true or false
            'general_condition' => 'required|string|max:255',
            'photo' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
        ]);
    
        // Save the land inspection
        $landInspection = LandInspection::create([
            'land_id' => $validated['land_id'],
            'date' => $validated['date'],
            'examiner_id' => $user->id,
            'hygiene' => $validated['hygiene'],
            'capacity' => $validated['capacity'],
            'electricity_supply' => $validated['electricity_supply'],
            'general_condition' => $validated['general_condition'],
            'photo_path' => $request->file('photo') ? $request->file('photo')->store('land_inspection_photos', 'public') : null,
        ]);
    
        return response()->json([
            'message' => 'Land inspection report submitted successfully.',
            'land_inspection' => $landInspection,
        ], 200);
    }
    

    public function getLands()
    {
        // Find the 'accepted' status ID from the land_statuses table
        $acceptedStatus = LandStatus::where('name', 'accepted')->first();
    
        if (!$acceptedStatus) {
            return response()->json(['error' => 'Accepted status not found.'], 500);
        }
    
        // Fetch lands with the 'accepted' status
        $lands = Land::where('status_id', $acceptedStatus->id)->get();
    
        // Return the lands as a JSON response
        return response()->json($lands);
    }
}
