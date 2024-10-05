<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Models\EventStatus;
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
}
