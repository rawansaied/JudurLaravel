<?php

namespace App\Http\Controllers;

use App\Models\Examiner;
use App\Models\ExaminerStatus;
use App\Models\Volunteer;
use App\Models\VolunteerStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VolunteerController extends Controller
{
    // Method to check if the volunteer has already made a request to become an examiner
    public function checkExaminerRequest()
{
    // Find the currently logged-in volunteer
    $volunteer = Volunteer::where('user_id', Auth::id())->first();
    
    if ($volunteer && $volunteer->examiner_request_made) {
        return response()->json([
            'hasMadeRequest' => true, 
            'message' => 'You have already made a request to become an examiner.'
        ], 200);
    }

    return response()->json([
        'hasMadeRequest' => false,
        'message' => 'No request to become an examiner has been made.'
    ], 200);
}


public function requestExaminer(Request $request)
{
    Log::info('RequestExaminer invoked', ['user_id' => Auth::id()]);

    // Find the currently logged-in volunteer
    $volunteer = Volunteer::where('user_id', Auth::id())->first();

    if (!$volunteer) {
        return response()->json(['error' => 'Volunteer not found.'], 404);
    }

    // Validate the incoming request
    $request->validate([
        'fullName' => 'required|string|max:255',
        'email' => 'required|email',
        'reason' => 'required|string',
        'availability' => 'required|string',
        'hours' => 'required|integer|min:1',
        'nonProfitAwareness' => 'required|boolean'
    ]);

    // Check if the volunteer has already made a request
    if ($volunteer->examiner_request_made) {
        Log::info('Examiner request already made', ['user_id' => Auth::id()]);
        return response()->json(['message' => 'You have already made a request to become an examiner.'], 400);
    }

    // Get the pending examiner status
    $pendingStatus = ExaminerStatus::where('name', 'pending')->first();

    if ($pendingStatus) {
        // Create a new examiner record
        $examiner = new Examiner();
        $examiner->user_id = $volunteer->user_id; // Assign the user_id from the volunteer
        $examiner->education = $request->input('education', ''); // Use input from the request or set to an empty string
        $examiner->reason = $request->input('reason'); // Use the reason from the request
        $examiner->examiner_status = $pendingStatus->id; // Set the examiner status to pending
        $examiner->save(); // Save the examiner record

        // Update the volunteer record
        $volunteer->examiner_request_made = true;
        $volunteer->volunteer_status = $pendingStatus->id; // Assuming you want to change the volunteer status to pending
        $volunteer->save();

        Log::info('Request submitted successfully', ['examiner' => $examiner]);

        return response()->json(['message' => 'Your request has been submitted successfully, pending admin approval.'], 200);
    }

    return response()->json(['error' => 'Pending status not found.'], 404);
}

    
}