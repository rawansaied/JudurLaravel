<?php
namespace App\Http\Controllers;

use App\Models\LandInspection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LandInspectionController extends Controller
{
    public function store(Request $request)
    {
        // Validate request data
        $validatedData = $request->validate([
            'land_id' => 'required|exists:lands,id',
            'date' => 'required|date',
            'hygiene' => 'required|string',
            'capacity' => 'required|integer',
            'electricity_supply' => 'required|boolean',
            'general_condition' => 'required|string',
            'photo_path' => 'nullable|string',
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Check if the user is a volunteer and examiner
        $volunteer = $user->volunteer; // Assuming a user has one volunteer profile

        if ($volunteer && $volunteer->examiner) {
            // Create a new land inspection
            $landInspection = LandInspection::create([
                'land_id' => $validatedData['land_id'],
                'date' => $validatedData['date'],
                'examiner_id' => $user->id,
                'hygiene' => $validatedData['hygiene'],
                'capacity' => $validatedData['capacity'],
                'electricity_supply' => $validatedData['electricity_supply'],
                'general_condition' => $validatedData['general_condition'],
                'photo_path' => $validatedData['photo_path'],
            ]);

            // Return success response
            return response()->json(['message' => 'Land Inspection created successfully', 'inspection' => $landInspection], 201);
        }

        return response()->json(['message' => 'Unauthorized: You are not an examiner'], 403);
    }
}
