<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Models\EventStatus;
use Illuminate\Support\Facades\DB;
use App\Models\Volunteer;
class EventController extends Controller
{
    // Fetch all events
    public function index()
    {
        // Find the 'upcoming' status from the event_statuses table
        $upcomingStatus = EventStatus::where('name', 'upcoming')->first();
    
        // Check if the upcoming status exists
        if (!$upcomingStatus) {
            return response()->json(['message' => 'Upcoming status not found'], 404);
        }
    
        // Filter events by the found 'upcoming' status id
        $upcomingEvents = Event::where('event_status', $upcomingStatus->id)->get();
    
        // Map through events to append the image_url
        $upcomingEvents = $upcomingEvents->map(function ($event) {
            $event->image_url = $event->image ? asset('storage/' . $event->image) : null;
            return $event;
        });
    
        return response()->json($upcomingEvents);
    }
    
    // Fetch a specific event by ID
    public function show($id)
    {
        $event = Event::findOrFail($id);
    
        // Assuming 'image' is the column that stores the image filename
        $event->image_url = asset('storage/' . $event->image);
    
        return response()->json($event);
    }

    public function joinEvent(Request $request)
    {
        $user = auth()->user();  // Get the authenticated user
        $eventId = $request->input('event_id');

        // Check if the user is a volunteer
        if ($user->role_id === 3) { // Assuming 2 is for volunteers
            $volunteer = Volunteer::where('user_id', $user->id)->first(); // Fetch volunteer by user_id

            // Check volunteer status
            if ($volunteer && $volunteer->volunteer_status !== 2) { // Assuming 1 is 'approved'
                return response()->json(['message' => 'Your registration is still under review'], 403);
            }

            // Add the volunteer to the event
            if (!$this->isVolunteerParticipating($volunteer->id, $eventId)) {
                $this->addVolunteerToEvent($volunteer->id, $eventId);
                return response()->json(['message' => 'Your registration is confirmed, you have been added to the event.'], 200);
            } else {
                return response()->json(['message' => 'You are already registered for this event'], 400);
            }
        }

        return response()->json(['message' => 'You must be a volunteer to join an event'], 403);
    }

    public function cancelEvent(Request $request, $eventId)
    {
        $user = auth()->user();
        $volunteer = Volunteer::where('user_id', $user->id)->first();

        if (!$volunteer) {
            return response()->json(['message' => 'You are not registered as a volunteer.'], 404);
        }

        // Check if the user is participating in the event
        $isParticipating = DB::table('event_volunteer')
            ->where('volunteer_id', $volunteer->id)
            ->where('event_id', $eventId)
            ->exists();

        if (!$isParticipating) {
            return response()->json(['message' => 'You are not participating in this event.'], 404);
        }

        // Remove the volunteer from the event
        DB::table('event_volunteer')
            ->where('event_id', $eventId)
            ->where('volunteer_id', $volunteer->id)
            ->delete();

        return response()->json(['message' => 'You have successfully canceled your participation in the event.'], 200);
    }

    private function addVolunteerToEvent($volunteerId, $eventId)
    {
        // Insert into event_volunteer table
        DB::table('event_volunteer')->insert([
            'event_id' => $eventId,
            'volunteer_id' => $volunteerId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function isVolunteerParticipating($volunteerId, $eventId)
    {
        return DB::table('event_volunteer')
            ->where('volunteer_id', $volunteerId)
            ->where('event_id', $eventId)
            ->exists();
    }
    
    
}
