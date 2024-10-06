<?php

namespace App\Http\Controllers;

use App\Mail\InspectionScheduled;
use App\Models\Land;
use App\Models\LandInspection;
use App\Models\User;
use App\Models\Volunteer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $volunteer = Volunteer::where('user_id', $userId)->select('id as volunteer_id', 'examiner')->first();

        if ($volunteer) {
            return response()->json($volunteer, 200); // Return the whole volunteer object including the examiner
        }

        return response()->json(['message' => 'Volunteer not found'], 404);
    }



    public function getVolunteerEvents($volunteerId)
    {
        $volunteer = Volunteer::with('events')->findOrFail($volunteerId);

        return response()->json($volunteer->events);
    }


    public function getExaminerLandData($volunteerId)
    {
        $volunteer = Volunteer::with('lands')->findOrFail($volunteerId);

        Log::info('Checking examiner status for volunteer:', [
            'volunteerId' => $volunteerId,
            'examiner' => $volunteer->examiner,
        ]);

        if ($volunteer->examiner) {
            return response()->json($volunteer->lands);
        }

        return response()->json(['error' => 'User is not an examiner.'], 403);
    }
    public function getLandInspections($examinerId)
    {
        // Fetch land inspections with related land and status data
        $inspections = LandInspection::with(['land.status'])->where('examiner_id', $examinerId)->get();
    
        if ($inspections->isEmpty()) {
            return response()->json(['message' => 'No land inspections found for this examiner'], 404);
        }
    
        // Map the inspections to include the status name from the land_statuses table
        $inspectionsWithStatusName = $inspections->map(function ($inspection) {
            return [
                'id' => $inspection->id,
                'donor_id' => $inspection->donor_id,
                'description' => $inspection->description,
                'land_size' => $inspection->land_size,
                'address' => $inspection->land->address ?? 'Address not available', // Safe access
                'proof_of_ownership' => $inspection->proof_of_ownership,
                'status_name' => $inspection->land->status->name ?? 'Status not available', // Get status name from related land status
                'date' => $inspection->date,
                'general_condition' => $inspection->general_condition,
            ];
        });
    
        return response()->json($inspectionsWithStatusName);
    }

    public function getPendingLands()
    {
        $pendingStatusId = 1;
    
        $pendingLands = Land::with('donor.user') 
            ->where('status_id', $pendingStatusId)
            ->get();
    
        return response()->json($pendingLands);
    }
    

    public function notifyLandOwner(Request $request)
    {
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
