<?php
namespace App\Http\Controllers;

use App\Models\Bid;
use App\Models\Auction;
use Illuminate\Http\Request;

class BidController extends Controller
{
//     public function store(Request $request, $auctionId)
// {
//     // Ensure the user is logged in
//     if (!auth()->check()) {
//         return response()->json(['message' => 'You must be logged in to place a bid.'], 401);
//     }

//     $userId = auth()->id();
//     $auction = Auction::findOrFail($auctionId);

//     // Check if the auction has ended
//     if (now()->greaterThan($auction->end_date)) {
//         return response()->json(['message' => 'The auction has ended.'], 400);
//     }

//     // Get the highest bid for the auction or fall back to the starting price if no bids exist
//     $currentHighestBid = Bid::where('auction_id', $auctionId)
//                             ->max('bid_amount') ?? $auction->starting_price;

//     // Ensure that the new bid is higher than the current highest bid or starting price
//     $newBidAmount = $request->input('bid_amount');
//     if ($newBidAmount <= $currentHighestBid) {
//         return response()->json(['message' => 'Your bid must be higher than the current highest bid or starting price.'], 400);
//     }

//     // Create the new bid
//     $bid = Bid::create([
//         'auction_id' => $auctionId,
//         'user_id' => $userId,
//         'bid_amount' => $newBidAmount,
//     ]);

//     // Update the current highest bid and highest bidder for the auction
//     $auction->update([
//         'current_highest_bid' => $newBidAmount,
//         'highest_bidder_id' => $userId,
//     ]);

//     return response()->json(['message' => 'Bid placed successfully', 'bid' => $bid]);
// }
public function store(Request $request, $auctionId)
{
    $auction = Auction::findOrFail($auctionId);

    // Validate bid amount
    $request->validate([
        'bid_amount' => 'required|numeric|min:' . ($auction->current_highest_bid + 1), // Ensure the bid is higher than current highest bid
    ]);

    // Create the bid
    $bid = new Bid();
    $bid->auction_id = $auctionId;
    $bid->user_id = auth()->id(); // Get the authenticated user's ID
    $bid->bid_amount = $request->input('bid_amount');
    $bid->save();

    // Update auction's highest bid and bidder
    $auction->current_highest_bid = $bid->bid_amount;
    $auction->highest_bidder_id = $bid->user_id;
    $auction->save();

    return response()->json(['message' => 'Bid placed successfully']);
}
public function placeBid(Request $request, $auctionId) {
    if (!auth()->check()) {
        return response()->json(['message' => 'You must be logged in to place a bid.'], 401);
    }

    $auction = Auction::findOrFail($auctionId);
    
    // Ensure the auction is ongoing
    if ($auction->auction_status_id != 2 || now()->greaterThan($auction->end_date)) {
        return response()->json(['message' => 'Auction is no longer available for bidding.'], 400);
    }

    // Validate bid amount
    $bidAmount = $request->input('bid_amount');
    if ($bidAmount <= $auction->current_highest_bid) {
        return response()->json(['message' => 'Bid must be higher than current highest bid.'], 400);
    }

    // Save the bid
    $bid = new Bid([
        'auction_id' => $auction->id,
        'user_id' => auth()->id(), // This should correctly retrieve the user ID
        'bid_amount' => $bidAmount,
    ]);
    $bid->save();

    // Update the auction's highest bid
    $auction->update([
        'current_highest_bid' => $bidAmount,
        'highest_bidder_id' => auth()->id(),
    ]);

    return response()->json(['message' => 'Bid placed successfully']);
}


public function completeAuction($id) {
    $auction = Auction::findOrFail($id);
    
    // Check if auction has ended
    if (now()->greaterThan($auction->end_date)) {
        // Mark auction as completed
        $auction->update(['auction_status_id' => 3]);
        
        // If the highest bidder fails to pay, move to the second highest bid (logic not implemented yet)
        
        return response()->json(['message' => 'Auction completed']);
    }

    return response()->json(['message' => 'Auction is still ongoing'], 400);
}
public function getAuctionWinnerAndStorePayment($auctionId)
{
    // Find the auction
    $auction = Auction::findOrFail($auctionId);
    
    // Ensure the auction has ended
    if (now()->lessThan($auction->end_date)) {
        return response()->json(['message' => 'The auction is still ongoing.'], 400);
    }

    // Get the highest bid for the auction
    $highestBid = Bid::where('auction_id', $auctionId)
                     ->orderBy('bid_amount', 'desc')
                     ->first();
    
    if (!$highestBid) {
        return response()->json(['message' => 'No bids were placed on this auction.'], 404);
    }

    // The winner is the user who placed the highest bid
    $winnerId = $highestBid->user_id;
    $amountToPay = $highestBid->bid_amount;

    // Store the payment information in the payments table
    $payment = new \App\Models\Payment();  // Assuming Payment is your model for the payments table
    $payment->auction_id = $auctionId;
    $payment->user_id = $winnerId;
    $payment->amount = $amountToPay;
    $payment->status = 'pending'; // Set the initial payment status
    $payment->save();

    // Return the winner information and payment details
    return response()->json([
        'message' => 'Auction completed successfully',
        'winner_id' => $winnerId,
        'amount_to_pay' => $amountToPay,
        'payment_status' => $payment->status,
    ]);
}


}
