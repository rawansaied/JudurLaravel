<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Land;
use App\Models\LandStatus;
use App\Models\LandInspection;
class LandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

          $lands = Land::with('inspections')->get();
    return response()->json($lands);
    }


    public function accept($id)
    {
        $land = Land::findOrFail($id);
        $land->status = 'accepted';
        $land->save();

        return response()->json(['message' => 'Land accepted successfully.']);
    }

    // Reject the land status
    public function reject($id)
    {
        $land = Land::findOrFail($id);
        $land->status = 'rejected';
        $land->save();

        return response()->json(['message' => 'Land rejected successfully.']);
    }  
    
    
    
    public function updateStatus(Request $request, $id)
    {
        // Validate that the status is either 'accepted' or 'rejected'
        $request->validate([
            'status' => 'required|in:accepted,rejected',
        ]);
    
        // Find the land by its ID
        $land = Land::find($id);
    
        if (!$land) {
            return response()->json(['message' => 'Land not found'], 404);
        }
    
        // Get the corresponding status ID from land_statuses table
        $status = LandStatus::where('name', $request->input('status'))->first();
    
        if (!$status) {
            return response()->json(['message' => 'Invalid status'], 400);
        }
    
        // Update the land's status
        $land->status_id = $status->id;
        $land->save();
    
        return response()->json(['message' => 'Land status updated successfully']);
    }
    
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
