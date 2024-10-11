<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Auction;
use App\Models\Bid;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CheckAuctionStatus extends Command
{
    protected $signature = 'auction:check-status';
    protected $description = 'Check and complete auctions that have ended';

    public function handle()
    {
        Log::info('Starting to check auctions');

        // Get all ongoing auctions whose end date has passed
        $completedAuctions = Auction::where('end_date', '<', now())
            ->where('auction_status_id', 2) // Ongoing auctions
            ->get();

        if ($completedAuctions->isEmpty()) {
            Log::info('No auctions to complete');
            return Command::SUCCESS;
        }

        foreach ($completedAuctions as $auction) {
            $auction->update(['auction_status_id' => 3]); // Mark as completed
            $highestBid = Bid::where('auction_id', $auction->id)->orderBy('bid_amount', 'desc')->first();

            if ($highestBid) {
                $user = $highestBid->user;

                if (empty($user->email)) {
                    Log::error('User with ID ' . $user->id . ' has no email address.');
                    continue; // Skip this user if there's no valid email
                }

                Log::info('Attempting to send email to: ' . $user->email);

                $emailContent = [
                    'auctionTitle' => $auction->title,
                    'bidAmount' => $highestBid->bid_amount,
                   'paymentLink' => 'http://localhost:4200/auction-payment?auctionId=' . $auction->id . '&userId=' . $highestBid->user_id,

                ];

                $htmlContent = 'Congratulations! You won the auction: ' . $emailContent['auctionTitle'] . ' '
                    . 'Your bid: $' . $emailContent['bidAmount'] . ' '
                    . '<a href="' . $emailContent['paymentLink'] . '">Click here to make the payment</a>';

                try {
                    Mail::raw($htmlContent, function ($message) use ($user) {
                        $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                            ->to($user->email)
                            ->subject('Congratulations! You won the auction');
                    });

                    Log::info('Email sent successfully to: ' . $user->email);
                } catch (\Exception $e) {
                    Log::error('Failed to send email to: ' . $user->email . '. Error: ' . $e->getMessage());
                }
            } else {
                Log::info('No bids found for auction ID ' . $auction->id);
            }
        }

        Log::info('Auction status check completed');
        return Command::SUCCESS;
    }
}
