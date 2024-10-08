<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\ItemDonation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mime\Part\HtmlPart;

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

        if (now()->greaterThan($auction->end_date)) {
            $auction->update(['auction_status_id' => 3]); // Mark as completed
            $itemDonation = ItemDonation::find($auction->item_id);
            $itemDonation->update(['status_id' => 1]); // Mark the item as sold

            // Get the highest bid for the auction
            $highestBid = Bid::where('auction_id', $id)->orderBy('bid_amount', 'desc')->first();

            return response()->json(['message' => 'Auction completed, item marked as sold.']);
        }

        return response()->json(['message' => 'Auction is still ongoing.'], 400);
    }
    public function getCompletedAuctions()
{
    $userId = auth()->id();

    $completedAuctions = Auction::where('end_date', '<', now())
        ->with(['bids', 'itemDonation']) 
        ->get();

            if ($highestBid) {
                Log::info('Highest bidder found', ['user_id' => $highestBid->user_id]);

                // Notify highest bidder via email
                $this->notifyHighestBidder($auction, $highestBid);
            } else {
                Log::info('No highest bidder found for auction', ['auction_id' => $id]);
            }

            return response()->json(['message' => 'Auction completed, item marked as sold, email sent to highest bidder.']);
    foreach ($completedAuctions as $auction) {
        $highestBid = $auction->bids()->orderBy('bid_amount', 'desc')->first();

        if ($highestBid && $highestBid->user_id == $userId) {
            $imageUrl = $auction->itemDonation->image
                ? asset('storage/' . $auction->itemDonation->image)
                : 'https://via.placeholder.com/150'; 

            $auctionWinners[] = [
                'auction_id' => $auction->id,
                'auction_status_id'=>$auction->auction_status_id,
                'auction_title' => $auction->title,
                'auction_image' => $imageUrl,
                'highest_bidder_id' => $highestBid->user_id,
                'highest_bidder_name' => $highestBid->user->name,
                'bid_amount' => $highestBid->bid_amount, 
            ];
        }

        return response()->json(['message' => 'Auction is still ongoing'], 400);
    }
}

    // Notify the highest bidder via email
    protected function notifyHighestBidder($auction, $highestBid)
    {
        $user = $highestBid->user;

        Log::info('Attempting to send email to: ' . $user->email);

        $emailContent = [
            'auctionTitle' => $auction->title,
            'bidAmount' => $highestBid->bid_amount,
            'paymentLink' => 'http://your-angular-app.com/payment?auctionId=' . $auction->id,
        ];

        // Prepare the HTML content for the email
        $htmlContent = 'Congratulations! You won the auction: ' . $emailContent['auctionTitle'] . ' '
            . 'Your bid: $' . $emailContent['bidAmount'] . ' '
            . '<a href="' . $emailContent['paymentLink'] . '">Click here to make the payment</a>';

        // Send the email using Mail::send()
        Mail::raw($htmlContent, function ($message) use ($user) {
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                ->to($user->email)
                ->subject('Congratulations! You won the auction');
        });

        Log::info('Email sent successfully to: ' . $user->email);
    }
}

