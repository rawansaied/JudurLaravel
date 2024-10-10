<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BidsController extends Controller
{
    
    public function store(Request $request, $auctionId)
    {
        $auction = Auction::findOrFail($auctionId);

        $request->validate([
            'bid_amount' => 'required|numeric|min:' . ($auction->current_highest_bid + 1),
        ]);

        $bid = new Bid();
        $bid->auction_id = $auctionId;
        $bid->user_id = Auth::id();
        $bid->bid_amount = $request->input('bid_amount');
        $bid->save();

        $auction->current_highest_bid = $bid->bid_amount;
        $auction->highest_bidder_id = $bid->user_id;
        $auction->save();

        return response()->json(['message' => 'Bid placed successfully']);
    }

 
    public function placeBid(Request $request, $auctionId)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'You must be logged in to place a bid.'], 401);
        }

        $auction = Auction::findOrFail($auctionId);

        if ($auction->auction_status_id != 2 || now()->greaterThan($auction->end_date)) {
            return response()->json(['message' => 'Auction is no longer available for bidding.'], 400);
        }

        $bidAmount = $request->input('bid_amount');
        if ($bidAmount <= $auction->current_highest_bid) {
            return response()->json(['message' => 'Bid must be higher than current highest bid.'], 400);
        }

        $bid = new Bid([
            'auction_id' => $auction->id,
            'user_id' => Auth::id(),
            'bid_amount' => $bidAmount,
        ]);
        $bid->save();

        $auction->update([
            'current_highest_bid' => $bidAmount,
            'highest_bidder_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'Bid placed successfully']);
    }

    public function completeAuction($id)
    {
        $auction = Auction::findOrFail($id);

        if (now()->greaterThan($auction->end_date)) {
            $payment = Payment::where('user_id', $auction->highest_bidder_id)
                              ->where('auction_id', $id)
                              ->first();

            if (!$payment || $payment->status !== 'paid') {
                $secondHighestBid = Bid::where('auction_id', $id)
                                       ->where('user_id', '!=', $auction->highest_bidder_id)
                                       ->orderByDesc('bid_amount')
                                       ->first();

                if ($secondHighestBid) {
                    $auction->highest_bidder_id = $secondHighestBid->user_id;
                    $auction->current_highest_bid = $secondHighestBid->bid_amount;
                    $auction->save();
                }
            }

            $auction->update(['auction_status_id' => 3]);
            return response()->json(['message' => 'Auction completed']);
        }

        return response()->json(['message' => 'Auction is still ongoing'], 400);
    }

    
    public function donateMoney(Request $request)
    {
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

        $userId = Auth::id();
        Log::info('Authenticated User ID:', ['userId' => $userId]);

        $donor = Donor::where('user_id', $userId)->first();

        if (!$donor) {
            return response()->json(['error' => 'Donor not found.'], 404);
        }

        $financial = Financial::create([
            'donor_id' => $donor->id,
            'amount' => $request->amount,
            'currency' => $request->currency,
            'payment_method' => $request->payment_method,
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $request->amount * 100,
                'currency' => $request->currency,
                'payment_method_types' => ['card'],
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
}
