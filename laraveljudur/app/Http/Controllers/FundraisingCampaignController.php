<?php

namespace App\Http\Controllers;

use App\Models\Financial;
use App\Models\FundraisingCampaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class FundraisingCampaignController extends Controller
{
   // Create a new campaign
   public function createCampaign(Request $request)
   {
       $validatedData = $request->validate([
           'title' => 'required|string|max:255',
           'description' => 'required|string',
           'target_amount' => 'required|numeric|min:1',
           'end_date' => 'required|date',
       ]);

       $campaign = FundraisingCampaign::create([
           'organizer_id' => Auth::id(),
           'title' => $validatedData['title'],
           'description' => $validatedData['description'],
           'target_amount' => $validatedData['target_amount'],
           'end_date' => $validatedData['end_date'],
       ]);

       return response()->json(['message' => 'Campaign created successfully', 'campaign' => $campaign], 201);
   }

   // View all active campaigns
   public function viewCampaigns()
   {
       $campaigns = FundraisingCampaign::where('is_active', true)->get();
       return response()->json($campaigns);
   }

   // Donate to a campaign
   public function donateToCampaign(Request $request, $campaignId)
   {
       $validatedData = $request->validate([
           'amount' => 'required|numeric|min:1',
           'currency' => 'required|string',
           'payment_method' => 'required|string', // This is optional if using Stripe's client-side library
       ]);

       $campaign = FundraisingCampaign::find($campaignId);

       if (!$campaign || !$campaign->is_active) {
           return response()->json(['error' => 'Campaign not found or inactive'], 404);
       }

       // Create a payment intent
       try {
           Stripe::setApiKey(env('STRIPE_SECRET')); // Set your Stripe secret key

           $paymentIntent = PaymentIntent::create([
               'amount' => $validatedData['amount'] * 100, // Amount in cents
               'currency' => $validatedData['currency'],
               'payment_method' => $validatedData['payment_method'],
               'confirmation_method' => 'automatic',
               'confirm' => true,
           ]);
       } catch (\Exception $e) {
           Log::error('Stripe error: ' . $e->getMessage());
           return response()->json(['error' => 'Payment failed: ' . $e->getMessage()], 500);
       }

       $donor = Auth::user();
       $financial = Financial::create([
           'donor_id' => $donor->id,
           'campaign_id' => $campaignId,
           'amount' => $validatedData['amount'],
           'currency' => $validatedData['currency'],
           'payment_method' => 'stripe', 
           'stripe_payment_id' => $paymentIntent->id,
       ]);

       // Update the raised amount
       $campaign->raised_amount += $validatedData['amount'];
       $campaign->save();

       return response()->json(['message' => 'Donation successful', 'financial' => $financial, 'paymentIntent' => $paymentIntent], 201);
   }
   public function createPaymentIntent(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        $paymentIntent = PaymentIntent::create([
            'amount' => $request->amount * 100,
            'currency' => 'usd', 
        ]);

        return response()->json(['clientSecret' => $paymentIntent->client_secret]);
    }
}
