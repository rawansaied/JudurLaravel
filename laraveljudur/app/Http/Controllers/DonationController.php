<?php

namespace App\Http\Controllers;

use App\Models\Land;
use App\Models\ItemDonation;
use App\Models\Financial;
use App\Models\Donor;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Stripe\PaymentIntent;
use Stripe\Stripe;

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
            'status_id' => 'required|exists:land_statuses,id',
        ]);

        if ($validator->fails()) {
            Log::error('Validation errors:', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Default proof of ownership
        $defaultProofPath = 'default/ownership_proof.jpg';
        $proofPath = $defaultProofPath;

        if ($request->hasFile('proof_of_ownership')) {
            $proofPath = $request->file('proof_of_ownership')->store('ownership_proofs', 'public');
        }

        $donor = Donor::where('user_id', auth()->id())->first();
        if (!$donor) {
            return response()->json(['error' => 'Donor not found.'], 404);
        }

        // Create the land donation entry
        $land = Land::create([
            'donor_id' => $donor->id,
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

        return response()->json(['message' => 'Item donated successfully', 'item_donation' => $itemDonation], 201);
    }

    public function donateMoney(Request $request)
    {
        // Validate the incoming request data
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
        $donor = Donor::where('user_id', $userId)->first();
        if (!$donor) {
            return response()->json(['error' => 'Donor not found.'], 404);
        }

        // Create the financial record
        $financial = Financial::create([
            'donor_id' => $donor->id,
            'amount' => $request->amount,
            'currency' => $request->currency,
            'payment_method' => $request->payment_method,
        ]);

        // Set Stripe secret key
        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            // Create a PaymentIntent
            $paymentIntent = PaymentIntent::create([
                'amount' => $request->amount * 100, // Convert amount to cents
                'currency' => $request->currency,
                'payment_method_types' => ['card'],
            ]);

            // Create a payment entry
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
        // Set Stripe secret key
        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            // Create a PaymentIntent
            $paymentIntent = PaymentIntent::create([
                'amount' => $request->input('amount') * 100, // Convert amount to cents
                'currency' => $request->input('currency'),
                'payment_method_types' => ['card'],
            ]);

            return response()->json(['clientSecret' => $paymentIntent->client_secret, 'stripe_payment_id' => $paymentIntent->id]);
        } catch (\Exception $e) {
            Log::error('Payment creation error:', ['message' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
