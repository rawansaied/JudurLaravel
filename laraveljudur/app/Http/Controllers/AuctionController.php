<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\ItemDonation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AuctionController extends Controller
{
    // Get all ongoing auctions
    public function index()
    {
        $auctions = Auction::with('itemDonation')
            ->where('auction_status_id', 2) // Ongoing auctions
            ->where('end_date', '>', now()) // Not yet ended
            ->get();

        $formattedAuctions = $auctions->map(function ($auction) {
            $imageUrl = $auction->itemDonation->image
                ? asset('storage/' . $auction->itemDonation->image)
                : 'https://via.placeholder.com/150'; // Fallback image

            return [
                'id' => $auction->id,
                'title' => $auction->title,
                'description' => $auction->description,
                'starting_price' => $auction->starting_price,
                'current_highest_bid' => $auction->current_highest_bid ?? $auction->starting_price,
                'start_date' => $auction->start_date,
                'end_date' => $auction->end_date,
                'item_name' => $auction->itemDonation->item_name,
                'item_value' => $auction->itemDonation->value,
                'item_condition' => $auction->itemDonation->condition,
                'image_url' => $imageUrl,
            ];
        });

        return response()->json($formattedAuctions);
    }

    // Complete the auction and notify the highest bidder
    public function completeAuction($id)
    {
        $auction = Auction::findOrFail($id);
    
        // Check if the auction has ended
        if (now()->greaterThan($auction->end_date)) {
            // Mark the auction as completed
            $auction->update(['auction_status_id' => 3]);
    
            // Mark the item as sold
            $itemDonation = ItemDonation::find($auction->item_id);
            $itemDonation->update(['status_id' => 1]); // Mark the item as sold
    
            // Get the highest bid
            $highestBid = Bid::where('auction_id', $id)->orderBy('bid_amount', 'desc')->first();
    
            if ($highestBid) {
                Log::info('Highest bidder found', ['user_id' => $highestBid->user_id]);
    
                // Notify highest bidder via email
                $this->notifyHighestBidder($auction, $highestBid);
            } else {
                Log::info('No highest bidder found for auction', ['auction_id' => $id]);
            }
    
            return response()->json(['message' => 'Auction completed, item marked as sold, email sent to highest bidder.']);
        }
    
        return response()->json(['message' => 'Auction is still ongoing.'], 400);
    }
    

    // Notify the highest bidder via email
    public function notifyHighestBidder($auction, $highestBid)
    {
        $user = $highestBid->user;
    
        if (empty($user->email)) {
            Log::error('User with ID ' . $user->id . ' has no email address.');
            return;
        }
    
        Log::info('Attempting to send email to: ' . $user->email);
    
        $emailContent = [
            'auctionTitle' => $auction->title,
            'bidAmount' => $highestBid->bid_amount,
            'paymentLink' => 'http://localhost:4200/auction-payment?auctionId=' . $auction->id . '&userId=' . $highestBid->user_id,
        ];
        
        $htmlContent = '
            <h1>Congratulations! You won the auction: ' . $emailContent['auctionTitle'] . '</h1>
            <p>Your bid: $' . $emailContent['bidAmount'] . '</p>
            <p><a href="' . $emailContent['paymentLink'] . '">Click here to make the payment</a></p>
        ';
        
        try {
            Mail::send([], [], function ($message) use ($user, $htmlContent) {
                $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                    ->to($user->email)
                    ->subject('Congratulations! You won the auction')
                    ->setBody($htmlContent, 'text/html'); // Send as HTML
            });
        
            Log::info('Email sent successfully to: ' . $user->email);
        } catch (\Exception $e) {
            Log::error('Failed to send email to: ' . $user->email . '. Error: ' . $e->getMessage());
        }
    }
    
    
    


    // Test email sending
    public function Email()
    {
        $auction = Auction::find(1); // Replace with an actual auction ID
        $highestBid = Bid::where('auction_id', 1)->orderBy('bid_amount', 'desc')->first();

        if ($auction && $highestBid) {
            $this->notifyHighestBidder($auction, $highestBid);

            return response()->json(['message' => 'Test email sent successfully']);
        }

        return response()->json(['message' => 'Auction or highest bid not found'], 404);
    }
}
