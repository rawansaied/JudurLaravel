<?php

namespace App\Http\Controllers;

use App\Mail\InspectionScheduled;
use App\Models\Land;
use App\Models\LandInspection;
use App\Models\User;
use App\Models\Volunteer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VolunteerAnalyticsController extends Controller
{
    public function getVolunteerSummary($volunteerId)
    {
        $volunteer = Volunteer::with('events')->findOrFail($volunteerId);

        // Calculate the summary
        $totalEvents = $volunteer->events->count();
        $totalHours = $volunteer->events->sum('duration');
        $peopleHelped = $volunteer->events->sum('people_helped');
        $goodsDistributed = $volunteer->events->sum('goods_distributed');

        // Return the summary as JSON
        return response()->json([
            'totalEvents' => $totalEvents,
            'totalHours' => $totalHours,
            'peopleHelped' => $peopleHelped,
            'goodsDistributed' => $goodsDistributed,
        ]);
    }
    public function getVolunteerActivityOverTime($volunteerId)
{
    $volunteer = Volunteer::with('events')->findOrFail($volunteerId);

    // Get events with date and duration information
    $activityData = $volunteer->events->groupBy('date')->map(function ($events, $date) {
        return [
            'date' => $date,
            'totalHours' => $events->sum('duration'),
            'eventsCount' => $events->count(),
        ];
    })->values();

    return response()->json($activityData);
}
public function getVolunteerIdByUserId($userId)
{
    // Assuming your Volunteer model has a relationship with User model
    $volunteer = Volunteer::where('user_id', $userId)->first();

    if ($volunteer) {
        return response()->json(['volunteer_id' => $volunteer->id], 200);
    }

    return response()->json(['message' => 'Volunteer not found'], 404);
}
public function getExaminerLandData($volunteerId)
{
    // Find the volunteer by ID
    $volunteer = Volunteer::with('lands')->findOrFail($volunteerId); // Eager load lands

    // Log the examiner status
    Log::info('Checking examiner status for volunteer:', [
        'volunteerId' => $volunteerId,
        'examiner' => $volunteer->examiner,
    ]);

    // Check if the volunteer is marked as an examiner
    if ($volunteer->examiner) {
        // Return the lands associated with the examiner
        return response()->json($volunteer->lands);
    }

    // Return error response if not an examiner
    return response()->json(['error' => 'User is not an examiner.'], 403);
}
public function getLandInspections($volunteerId)
{
    $inspections = LandInspection::with('land')
        ->where('examiner_id', $volunteerId) 
        ->get();

    Log::info('Land inspections data:', ['data' => $inspections]);

    return response()->json($inspections);
}
public function getPendingLands()
{
    $pendingStatusId = 3; 

    $pendingLands = Land::where('status_id', $pendingStatusId)->get();

    return response()->json($pendingLands);
}

public function notifyLandOwner(Request $request)
{
    // Validate the request to ensure landId and inspectionDate are provided
    $request->validate([
        'landId' => 'required|integer',
        'inspectionDate' => 'required|date',
    ]);

    // Get the landId and inspectionDate from the request
    $landId = $request->input('landId');
    $inspectionDate = $request->input('inspectionDate');

    // Find the land with the given ID
    $land = Land::find($landId);

    if (!$land || $land->status_id != 3) { // Ensure the land has pending status
        return response()->json(['message' => 'Land with pending status not found.'], 404);
    }

    // Find the land owner (assuming donor_id relates to a User)
    $landOwner = User::find($land->donor_id);

    // if ($landOwner) {
    //     Mail::to($landOwner->email)->send(new InspectionScheduled($land, $inspectionDate));
    //     return response()->json(['message' => 'Land owner notified successfully.'], 200);
    // } else {
    //     return response()->json(['message' => 'Land owner not found.'], 404);
    // }
}








}
