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

                $htmlContent = 'Dear ' . $user->name . ',<br><br>'
                    . 'We are excited to inform you that you have won the auction for: <strong>' . $emailContent['auctionTitle'] . '</strong>!<br>'
                    . 'Your winning bid amount is: $' . number_format($emailContent['bidAmount'], 2) . '.<br><br>'
                    . 'To finalize your payment, please click the link below:<br>'
                    . '<a href="' . $emailContent['paymentLink'] . '">Make Payment</a><br><br>'
                    . 'Thank you for your participation, and we look forward to seeing you in future auctions!<br>'
                    . 'Best regards,<br>'
                    . 'The Auction Team';

                try {
                    Mail::send([], [], function ($message) use ($user, $htmlContent) {
                        $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                            ->to($user->email)
                            ->subject('Auction Winner Notification')
                            ->html($htmlContent); // Use html() to send HTML content
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
