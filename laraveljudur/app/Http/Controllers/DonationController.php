<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use App\Models\Land;
use App\Models\ItemDonation;
use App\Models\Financial;
use App\Models\Donor;
use App\Models\Inventory;
use App\Models\LandStatus;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Treasury;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DonationController extends Controller
{
    public function donateLand(Request $request)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
            'land_size' => 'required|numeric',
            'address' => 'required|string',
            'proof_of_ownership' => 'sometimes|file|mimes:jpg,png,pdf|max:2048',
            'availability_time' => 'required|date', // Validate availability_time
        ]);

        if ($validator->fails()) {
            Log::error('Validation errors:', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Default proof of ownership path
        $proofPath = 'default/ownership_proof.jpg';
        if ($request->hasFile('proof_of_ownership')) {
            $proofPath = $request->file('proof_of_ownership')->store('ownership_proofs', 'public');
        }

        // Check if there is a pending status in the land_statuses table
        $pendingStatus = LandStatus::where('name', 'pending')->first();
        if (!$pendingStatus) {
            return response()->json(['error' => 'Pending status not found. Please add it to the land_statuses table.'], 500);
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
            'availability_time' => Carbon::parse($request->input('availability_time'))->toDateString(), // Store only the date
        ]);

        $admins = User::where('role_id', 1)->get();
        $organizers = User::where('role_id', 6)->get();
        $user = User::find($donor->user_id);
        
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,  
                'message' => 'A new land donation has been made by ' . $user->name . '. Please review the details through the Land Donations management page.',
                'is_read' => false,
            ]);
        }
        
        foreach ($organizers as $organizer) {
            Notification::create([
                'user_id' => $organizer->id,  
                'message' => 'A new land has been donated by ' . $user->name . '. Kindly check the Land Donations management page for more information.',
                'is_read' => false,
            ]);
        }
        

        return response()->json(['message' => 'Land donated successfully', 'land' => $land], 201);
    }



    public function donateItem(Request $request)
    {
        try {
            // Validate the input data
            $validatedData = $request->validate([
                'item_name' => 'required|string',
                'condition' => 'required|string',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Image is required
                'is_valuable' => 'required|boolean',
                'value' => $request->is_valuable ? 'required|numeric' : 'nullable|numeric',
                'quantity' => 'required|integer|min:1'  // Validate quantity
            ]);

            // Get the logged-in user ID
            $userId = auth()->id();

            // Retrieve the donor based on the user ID
            $donor = Donor::where('user_id', $userId)->first();

            // Check if donor exists
            if (!$donor) {
                return response()->json(['error' => 'Donor not found.'], 404);
            }

            // Handle the uploaded image
            $imagePath = $request->file('image')->store('item_images', 'public'); // Save the image

            // Set the status ID based on is_valuable value
            if ($validatedData['is_valuable']) {
                // If valuable, set status to 'pending'
                $status = \App\Models\ItemStatus::where('status', 'pending')->first();
                if (!$status) {
                    return response()->json(['error' => 'Status "pending" not found.'], 404);
                }
                $statusId = $status->id;
                $value = $validatedData['value'];
                $admins = User::where('role_id', 1)->get();
                $organizers = User::where('role_id', 6)->get();
                $user = User::find($donor->user_id);
                
                foreach ($admins as $admin) {
                    Notification::create([
                        'user_id' => $admin->id,  
                        'message' => 'A new valuable items donation has been made by ' . $user->name . '. Please review the details through the Valuable Donations management page.',
                        'is_read' => false,
                    ]);
                }
                
                foreach ($organizers as $organizer) {
                    Notification::create([
                        'user_id' => $organizer->id,  
                        'message' => 'A new donation of valuable items has been made by ' . $user->name . '. Kindly check the Valuable Donations management page for more information.',
                        'is_read' => false,
                    ]);
                }
                
            } else {
                $inventory = Inventory::where('id', 1)->first();
                if (!$inventory) {
                    return response()->json(['error' => 'Inventory not found.'], 404);
                }
                $old_value = $inventory->items;
                $new_value = $old_value + $request->quantity;
                $inventory->update(['items' => $new_value]);

                // If not valuable, set status to 'normal'
                $status = \App\Models\ItemStatus::where('status', 'normal')->first();
                if (!$status) {
                    return response()->json(['error' => 'Status "normal" not found.'], 404);
                }
                $statusId = $status->id;
                $value = 0.00;
            }

            $itemDonation = ItemDonation::create([
                'donor_id' => $donor->id,
                'item_name' => $validatedData['item_name'],
                'value' => $value,
                'is_valuable' => $validatedData['is_valuable'],
                'condition' => $validatedData['condition'],
                'status_id' => $statusId,
                'image' => $imagePath,
            ]);

            return response()->json([
                'message' => 'Item donated successfully',
                'item_donation' => $itemDonation,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error donating item: ' . $e->getMessage()], 500);
        }
    }





    
    public function donateMoney(Request $request)
    {
        // Log the incoming request data
        Log::info('Incoming donation request:', $request->all());

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|string',
            'payment_method' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::error('Validation errors:', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userId = auth()->id();
        Log::info('Authenticated User ID:', ['userId' => $userId]); 

        DB::enableQueryLog();

        $donor = Donor::where('user_id', $userId)->first();
        
        Log::info('Executed query to find donor:', DB::getQueryLog());
        
        if (!$donor) {
            return response()->json(['error' => 'Donor not found.'], 404);
        }

        $financial = Financial::create([
            'donor_id' => $donor->id,
            'amount' => $request->amount,
            'currency' => $request->currency,
            'payment_method' => $request->payment_method,
        ]);

        $admins = User::where('role_id', 1)->get();
        $user = User::find($donor->user_id);
        
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,  
                'message' => 'A new financial donation has been made by ' . $user->name . '. Please review the details through the main page in dashboard.',
                'is_read' => false,
            ]);
        }
        

        $treasury = Treasury::where('id', 1)->first();
        $old_money = $treasury->money;
        $new_money = $old_money + $request->amount;
        $treasury->update(['money' => $new_money]);

        // Use config() instead of env()
        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $request->amount * 100,
                'currency' => $request->currency,
                'payment_method_types' => ['card'],
            ]);

            Log::info('Stripe Payment Intent created:', [
                'paymentIntent' => $paymentIntent,
            ]);

            Payment::create([
                'stripe_payment_id' => $paymentIntent->id,
                'user_id' => $userId,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'status' => $paymentIntent->status,
            ]);

            return response()->json(['message' => 'Money donated successfully', 'financial' => $financial, 'paymentIntent' => $paymentIntent], 201);
        } catch (\Exception $e) {
            Log::error('Payment error:', ['message' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createPayment(Request $request)
    {
        Log::info('Incoming payment creation request:', $request->all());


        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $request->input('amount') * 100,
                'currency' => $request->input('currency'),
                'payment_method_types' => ['card'],
            ]);

            Log::info('Stripe Payment Intent created:', [
                'paymentIntent' => $paymentIntent,
            ]);

            return response()->json(['clientSecret' => $paymentIntent->client_secret, 'stripe_payment_id' => $paymentIntent->id]);
        } catch (\Exception $e) {
            Log::error('Payment creation error:', ['message' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function createAuctionPayment(Request $request)
    {
        Log::info('Incoming payment creation request:', $request->all());
        $userId = auth()->id();
        Log::info('Authenticated User ID:', ['userId' => $userId]);
    
        if (is_null($userId)) {
            return response()->json(['error' => 'User is not authenticated.'], 401);
        }
    
        $validated = $request->validate([
            'auction_id' => 'required|integer',
            'amount' => 'required|numeric',
            'currency' => 'required|string',
            'payment_method' => 'string',
        ]);
     

        Stripe::setApiKey(config('services.stripe.secret'));
    
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' =>intval( $request->input('amount')) , 
                'currency' => $request->input('currency'),
                'payment_method_types' => ['card'],
            ]);
    
            Log::info('Stripe Payment Intent created:', [
                'paymentIntent' => $paymentIntent,
            ]);
    
            $payment = Payment::create([
                'stripe_payment_id' => $paymentIntent->id,
                'user_id' => $userId, 
                'amount' => intval( $request->input('amount')),
                'currency' => $request->input('currency'),
                'status' => $paymentIntent->status,
            ]);
            $treasury = Treasury::where('id', 1)->first();
            $old_money = $treasury->money;
            $new_money = $old_money + $request->amount;
            $treasury->update(['money' => $new_money]);
    
            Log::info('Payment stored in database:', ['payment' => $payment]);
    
            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
                'stripe_payment_id' => $paymentIntent->id,
                'user_id' => $userId,
                'paymentData' => $validated,
            ]);
        } catch (\Exception $e) {
            Log::error('Payment creation error:', ['message' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function updateLandAvailability(Request $request, $landId)
    {
        $userId = auth()->id();
        Log::info('Updating availability for land ID: ', ['landId' => $landId]);
        Log::info('Authenticated user ID: ', ['userId' => $userId]);
    
        // Find the land by ID and check if it belongs to the authenticated donor
        $land = Land::where('id', $landId)
                    ->where('donor_id', Donor::where('user_id', $userId)->value('id'))
                    ->first();
    
        if (!$land) {
            Log::error('Land not found or does not belong to the donor.');
            return response()->json(['error' => 'Land not found or does not belong to the donor.'], 404);
        }
    
        // Update the availability date
        $land->availability_time = Carbon::parse($request->input('availability_time'))->toDateString();
        $land->save();
    
        return response()->json(['message' => 'Availability date updated successfully', 'land' => $land], 200);
    }
    
    
    public function confirmPayment(Request $request)
    {
        Log::info('Confirming auction payment with data:', $request->all());
        $validated = $request->validate([
            'auction_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string',
            'payment_method' => 'string',
        ]);
       
        try {
            
             $auction = Auction::find($validated['auction_id']);
              $auction->auction_status_id = 5 ;
             $auction->save();

            return response()->json([
                'message' => 'Auction payment confirmed successfully',
                'status' => 'success',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error confirming auction payment', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error confirming auction payment',
                'status' => 'error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
