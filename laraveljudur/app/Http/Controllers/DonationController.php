<?php

namespace App\Http\Controllers;

use App\Models\Land;            
use App\Models\ItemDonation;     
use App\Models\Financial;       
use App\Models\Donor;   
use App\Models\LandStatus;        
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DonationController extends Controller
{
    public function donateLand(Request $request)
    {
        // Validate the incoming request data
        $validator = \Validator::make($request->all(), [
            'description' => 'required|string',
            'land_size' => 'required|numeric',
            'address' => 'required|string',
            'proof_of_ownership' => 'sometimes|file|mimes:jpg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            \Log::error('Validation errors:', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if there is a pending status in the land_statuses table
        $pendingStatus = LandStatus::where('name', 'pending')->first();
        if (!$pendingStatus) {
            return response()->json(['error' => 'Pending status not found. Please add it to the land_statuses table.'], 500);
        }

        // Default proof of ownership path
        $proofPath = 'default/ownership_proof.jpg';
        if ($request->hasFile('proof_of_ownership')) {
            $proofPath = $request->file('proof_of_ownership')->store('ownership_proofs', 'public');
        }

        // Retrieve the donor
        $donor = Donor::where('user_id', auth()->id())->first();
        if (!$donor) {
            return response()->json(['error' => 'Donor not found.'], 404);
        }

        // Create the land donation entry with a status of pending
        $land = Land::create([
            'donor_id' => $donor->id,
            'description' => $request->input('description'),
            'land_size' => $request->input('land_size'),
            'address' => $request->input('address'),
            'proof_of_ownership' => $proofPath,
            'status_id' => $pendingStatus->id,  // Automatically set the status to 'pending'
        ]);

        return response()->json(['message' => 'Land donated successfully', 'land' => $land], 201);
    }

    
    
    

    public function donateItem(Request $request)
    {
    

        $validatedData = $request->validate([
            'item_name' => 'required|string',
            'value' => 'required|numeric',
            'is_valuable' => 'required|boolean',
            'condition' => 'required|string',
            'status_id' => 'required|exists:item_statuses,id',
        ]);

      
        $userId = auth()->id();

       
        $donor = Donor::where('user_id', $userId)->first();
        if (!$donor) {
            return response()->json(['error' => 'Donor not found.'], 404);
        }

        $itemDonation = ItemDonation::create([
            'donor_id' => $donor->id,  
            'item_name' => $validatedData['item_name'],
            'value' => $validatedData['value'],
            'is_valuable' => $validatedData['is_valuable'],
            'condition' => $validatedData['condition'],
            'status_id' => $validatedData['status_id'],
        ]);

        return response()->json([
            'message' => 'Item donated successfully',
            'item_donation' => $itemDonation,
        ], 201);
    }

    public function donateMoney(Request $request)
    {
       

        $validatedData = $request->validate([
            'amount' => 'required|numeric',
            'currency' => 'required|string',
            'payment_method' => 'required|string',
        ]);

      
        $userId = auth()->id();

        
        $donor = Donor::where('user_id', $userId)->first();
        if (!$donor) {
            return response()->json(['error' => 'Donor not found.'], 404);
        }

        $financial = Financial::create([
            'donor_id' => $donor->id,  
            'amount' => $validatedData['amount'],
            'currency' => $validatedData['currency'],
            'payment_method' => $validatedData['payment_method'],
        ]);

        return response()->json([
            'message' => 'Money donated successfully',
            'financial' => $financial,
        ], 201);
    }




    
}
