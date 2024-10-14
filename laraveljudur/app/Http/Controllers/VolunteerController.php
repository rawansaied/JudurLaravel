<?php

namespace App\Http\Controllers;

use App\Models\Examiner;
use App\Models\ExaminerStatus;
use App\Models\Notification;
use App\Models\User;
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

    $pendingStatus = ExaminerStatus::where('name', 'pending')->first();

    if ($pendingStatus) {
        $examiner = new Examiner();
        $examiner->user_id = $volunteer->user_id; 
        $examiner->education = $request->input('education', '');
        $examiner->reason = $request->input('reason');
        $examiner->examiner_status = $pendingStatus->id; 
        $examiner->save(); 

        $volunteer->examiner_request_made = true;
        $volunteer->volunteer_status = $pendingStatus->id; 
        $volunteer->save();
        $admins = User::where('role_id', 1)->get();
        $mentors = User::where('role_id', 6)->get();
        $user = User::find($volunteer->user_id);  
        
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,  
                'message' => 'A new Examiner request '. $user->name .' has been made. Please review the request through the Examiners management page.',
                'is_read' => false,
            ]);
        }
        
        foreach ($mentors as $mentor) {
            Notification::create([
                'user_id' => $mentor->id,  
                'message' => 'A new Examiner request '. $user->name .' has been submitted. Kindly check the Examiners management page for details.',
                'is_read' => false,
            ]);
        }

        Log::info('Request submitted successfully', ['examiner' => $examiner]);

        return response()->json(['message' => 'Your request has been submitted successfully, pending admin approval.'], 200);
    }

    return response()->json(['error' => 'Pending status not found.'], 404);
}
public function getVolunteerStatus($user_id)
{
    $volunteer = Volunteer::where('user_id', $user_id)->first();

    if ($volunteer) {
        return response()->json(['status' => $volunteer->volunteer_status]);
    } else {
        return response()->json(['status' => null], 404);
    }
}
    
}