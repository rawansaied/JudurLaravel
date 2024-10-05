<?php

namespace App\Http\Controllers;

use App\Models\Land;
use App\Models\LandInspection;
use App\Models\User;
use App\Models\Volunteer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;





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
        ->where('examiner_id', $volunteerId) // or adjust based on your needs
        ->get();

    Log::info('Land inspections data:', ['data' => $inspections]);

    return response()->json($inspections);
}







}
