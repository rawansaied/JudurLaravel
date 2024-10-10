<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Bid;
use Illuminate\Http\Request;

class BidController extends Controller
{
    // Place a bid
    public function store(Request $request, $auctionId)
    {
        // Ensure the user is authenticated
        if (!auth()->check()) {
            return response()->json(['message' => 'You must be logged in to place a bid.'], 401);
        }

        // Get the auction by its ID
        $auction = Auction::findOrFail($auctionId);

        // Validate the bid amount
        $request->validate([
            'bid_amount' => 'required|numeric|min:' . ($auction->current_highest_bid + 1), // Ensure bid is higher than current highest bid
        ]);

        // Create a new bid
        $bid = new Bid();
        $bid->auction_id = $auctionId;
        $bid->user_id = auth()->id(); // Get the authenticated user's ID
        $bid->bid_amount = $request->input('bid_amount');
        $bid->save();

        // Update the auction's current highest bid and highest bidder
        $auction->current_highest_bid = $bid->bid_amount;
        $auction->highest_bidder_id = $bid->user_id;
        $auction->save();

        return response()->json(['message' => 'Bid placed successfully']);
    }

    // Complete auction check
    public function completeAuction($id)
    {
        $auction = Auction::findOrFail($id);

        if (now()->greaterThan($auction->end_date)) {
            $auction->update(['auction_status_id' => 3]);

            // Return completion success
            return response()->json(['message' => 'Auction completed']);
        }

        return response()->json(['message' => 'Auction is still ongoing'], 400);
    }
}
