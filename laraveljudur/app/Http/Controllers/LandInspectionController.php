<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LandInspection;
class LandInspectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {  
     
        // Fetch reports with examiner details
        $reports = LandInspection::with('examiner', 'land')->get();

        return response()->json($reports);
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
    public function show($id) {
        $report = LandInspection::with('examiner', 'land', 'inspections')->findOrFail($id);
        return response()->json($report);
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
