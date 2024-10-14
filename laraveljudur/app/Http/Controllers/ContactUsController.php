<?php

namespace App\Http\Controllers;

use App\Mail\ContactUsMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\AcknowledgmentMail;
use App\Models\Contact;

class ContactUsController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'required|string',
        ]);

        $contact = new Contact();
        $contact->name = $request->name;
        $contact->email = $request->email;
        $contact->message = $request->message;
        $contact->save();

        $messageContent = "Name: " . $request->name . "\n"
                        . "Email: " . $request->email . "\n"
                        . "Message: " . $request->message;

        Mail::raw($messageContent, function ($message) use ($request) {
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $message->to(env('MAIL_FROM_ADDRESS'))->subject('Contact Us Message');
        });

        $confirmationMessage = "Dear " . $request->name . ",\n\n"
                             . "Thank you for contacting Judur. We have received your message and will get back to you within 24 hours.\n\n"
                             . "Best regards,\n"
                             . "Judur Team";

        Mail::raw($confirmationMessage, function ($message) use ($request) {
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $message->to($request->email)->subject('Thank You for Contacting Judur');
        });

        return response()->json(['message' => 'Contact message sent successfully.'], 201);
    }
}
