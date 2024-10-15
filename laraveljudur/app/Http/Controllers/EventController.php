<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Models\EventStatus;
use Illuminate\Support\Facades\DB;
use App\Models\Volunteer;
use SimpleSoftwareIO\QrCode\Facades\QrCode ;

class EventController extends Controller
{
    // Fetch all events
    public function index()
    {
        $upcomingStatus = EventStatus::where('name', 'upcoming')->first();

        if (!$upcomingStatus) {
            return response()->json(['message' => 'Upcoming status not found'], 404);
        }

        $upcomingEvents = Event::where('event_status', $upcomingStatus->id)->get();

        $upcomingEvents = $upcomingEvents->map(function ($event) {
            $event->image_url = $event->image ? asset('storage/' . $event->image) : null;
            return $event;
        });

        return response()->json($upcomingEvents);
    }

    public function show($id)
    {
        $event = Event::findOrFail($id);

        $event->image_url = asset('storage/' . $event->image);

        return response()->json($event);
    }

    public function joinEvent(Request $request)
    {
        $user = auth()->user();
        $eventId = $request->input('event_id');
    
        if ($user->role_id === 3) {
            $volunteer = Volunteer::where('user_id', $user->id)->first();
    
            if ($volunteer && $volunteer->volunteer_status !== 2) {
                return response()->json(['message' => 'Your registration is still under review'], 403);
            }
    
            $event = Event::find($eventId);
            $joinedVolunteersCount = DB::table('event_volunteer')
                ->where('event_id', $eventId)
                ->count();
    
            if ($joinedVolunteersCount >= $event->expected_organizer_number) {
                return response()->json(['message' => 'The event is full'], 400);
            }
    
            if (!$this->isVolunteerParticipating($volunteer->id, $eventId)) {
                $this->addVolunteerToEvent($volunteer->id, $eventId);
    
                $qrCodeUrl = route('generate.qr.code', ['id' => $eventId]);
    
                return response()->json([
                    'message' => 'Your registration is confirmed, you have been added to the event.',
                    'qr_code_url' => $qrCodeUrl,
                ], 200);
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

    public function isVolunteerJoined(Request $request, $eventId)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);  // Handle unauthenticated users
        }

        $volunteer = Volunteer::where('user_id', $user->id)->first();

        if (!$volunteer) {
            return response()->json(['isJoined' => false, 'joined_volunteers_count' => 0, 'expected_organizer_number' => 0], 200);
        }

        $isParticipating = DB::table('event_volunteer')
            ->where('volunteer_id', $volunteer->id)
            ->where('event_id', $eventId)
            ->exists();

        $joinedVolunteersCount = DB::table('event_volunteer')
            ->where('event_id', $eventId)
            ->count();

        $event = Event::find($eventId);

        return response()->json([
            'isJoined' => $isParticipating,
            'joined_volunteers_count' => $joinedVolunteersCount,
            'expected_organizer_number' => $event->expected_organizer_number
        ], 200);
    }


    public function generateQrCode($eventId)
    {
        $url = route('event.details', ['id' => $eventId]); 
    
        $qrCodeImage = QrCode::size(300)->generate($url);
    
        $base64QrCode = base64_encode($qrCodeImage);
    
        return response()->json([
            'qr_code' => 'data:image/png;base64,' . $base64QrCode
        ], 200);
    }
    
}
