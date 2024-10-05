<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    // Fetch all events
    public function index()
    {
        return response()->json(Event::all());
    }

    // Fetch a specific event by ID
    public function show($id)
    {
        $event = Event::findOrFail($id);
        return response()->json($event);
    }
}
