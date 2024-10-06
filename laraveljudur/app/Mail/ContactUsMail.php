<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use MailerSend\Helpers\Builder\Variable;
use MailerSend\LaravelDriver\MailerSendTrait;

class ContactUsMail extends Mailable
{
    use Queueable, SerializesModels, MailerSendTrait;

    public $name;
    public $email;
    public $messageContent;

    public function __construct($name, $email, $messageContent)
    {
        $this->name = $name;
        $this->email = $email;
        $this->messageContent = $messageContent;
    }

    public function build()
    {
        // Set the recipient's email address
        $to = $this->email;

        // Construct the email using MailerSend
        return $this->view('emails.contact')
            ->with([
                'name' => $this->name,
                'email' => $this->email,
                'messageContent' => $this->messageContent,
            ])
            ->mailersend(
                null, 
                [
                    new Variable($to, [
                        'name' => $this->name,
                        'message' => $this->messageContent,
                    ]),
                ],
                // Optional: Include tags or additional personalization if needed
                [], 
            );
    }
}
