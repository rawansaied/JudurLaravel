<?php

namespace App\Mail;


use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;



use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;


class AuctionWinnerMail extends Mailable
{

   
        use Queueable, SerializesModels;
    
        public $auction;
        public $bidder;
        public $amount;
        public $frontendUrl;
    
        /**
         * Create a new message instance.
         *
         * @return void
         */
        public function __construct($auction, $bidder, $amount)
        {
            $this->auction = $auction;
            $this->bidder = $bidder;
            $this->amount = $amount;
    
            // Frontend URL for the Angular app
            $this->frontendUrl = "http://localhost:4200/auction/{$auction->id}/payment"; // Adjust this based on your Angular route
        }
    
        /**
         * Build the message.
         *
         * @return $this
         */
        public function build()
        {
            return $this->from(config('mail.from.address'), config('mail.from.name'))
                        ->subject('Congratulations! You have won the auction')
                        ->html("
                            <h1>Dear {$this->bidder->name},</h1>
                            <p>Congratulations, you have won the auction for \"{$this->auction->name}\" with a bid of \${$this->amount}.</p>
                            <p>Please <a href=\"{$this->frontendUrl}\">click here</a> to complete your payment.</p>
                            <p>Thank you for participating!</p>
                        ");
    }
    
    /**
     * Create a new message instance.
     */
    // public function __construct()
    // {
    //     //
    // }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Auction Winner Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'view.name',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
