<?php

namespace App\Http\Controllers;
use App\Models\Land;             // For the Land model
use App\Models\ItemDonation;     // For the ItemDonation model
use App\Models\Financial; 
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
            'status_id' => 'required|exists:land_statuses,id',
        ]);
    
        if ($validator->fails()) {
            \Log::error('Validation errors:', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
       
        $defaultProofPath = 'default/ownership_proof.jpg';
    
       
        if ($request->hasFile('proof_of_ownership')) {
          
            $proofPath = $request->file('proof_of_ownership')->store('ownership_proofs', 'public');
        } else {
           
            $proofPath = $defaultProofPath;
        }
    
   
        $land = Land::create([
            'donor_id' => auth()->id(),
            'description' => $request->input('description'),
            'land_size' => $request->input('land_size'),
            'address' => $request->input('address'),
            'proof_of_ownership' => $proofPath,  
            'status_id' => $request->input('status_id'),
        ]);
    
        return response()->json(['message' => 'Land donated successfully', 'land' => $land], 201);
    }
    
    public function donateItem(Request $request)
{
   
    $rawData = $request->getContent();
    $data = json_decode($rawData, true);

    \Log::info('Raw JSON Data:', ['raw' => $rawData, 'parsed' => $data]);


    if (!auth()->check()) {
        \Log::warning('User is not authenticated.');
        return response()->json(['error' => 'User is not authenticated'], 401);
    }

  
    $userId = auth()->id();

  
    $validator = \Validator::make($data, [
        'item_name' => 'required|string',
        'value' => 'required|numeric',
        'is_valuable' => 'required|boolean',
        'condition' => 'required|string',
        'status_id' => 'required|exists:item_statuses,id',
    ]);

   
    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

   
    $itemDonation = ItemDonation::create([
        'donor_id' => $userId,  
        'item_name' => $data['item_name'],
        'value' => $data['value'],
        'is_valuable' => $data['is_valuable'],
        'condition' => $data['condition'],
        'status_id' => $data['status_id'],
    ]);

   
    return response()->json([
        'message' => 'Item donated successfully',
        'item_donation' => $itemDonation
    ], 201);
}

public function donateMoney(Request $request)
{
    
    $rawData = $request->getContent();
    $data = json_decode($rawData, true);

    \Log::info('Raw JSON Data:', ['raw' => $rawData, 'parsed' => $data]);

  
    if (!auth()->check()) {
        \Log::warning('User is not authenticated.');
        return response()->json(['error' => 'User is not authenticated'], 401);
    }

  
    $userId = auth()->id();

  
    $validator = \Validator::make($data, [
        'amount' => 'required|numeric',
        'currency' => 'required|string',
        'payment_method' => 'required|string',
    ]);

   
    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

   
    $financial = Financial::create([
        'donor_id' => $userId,  

        'amount' => $data['amount'],
        'currency' => $data['currency'],
        'payment_method' => $data['payment_method'],
    ]);

    
    return response()->json([
        'message' => 'Money donated successfully',
        'financial' => $financial
    ], 201);
}


}
