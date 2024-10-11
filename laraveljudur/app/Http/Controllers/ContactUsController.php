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
    // public function showContactForm()
    // {
    //     return view('contact');
    // }

    // public function sendContactMessage(Request $request)
    // {
    //     // Validate incoming request data
    //     $validatedData = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|email|max:255',
    //         'message' => 'required|string',
    //     ]);

    //     try {
    //         // Log the attempt to send an email
    //         Log::info('Attempting to send contact email.', [
    //             'name' => $validatedData['name'],
    //             'email' => $validatedData['email'],
    //         ]);
    //         Contact::create([
    //             'name' => $validatedData['name'],
    //             'email' => $validatedData['email'],
    //             'message' => $validatedData['message'],
    //         ]);

    //         // Create and send the email
    //         Mail::to('shroukeslam909@gmail.com')->send(new ContactUsMail(
    //             $validatedData['name'],
    //             $validatedData['email'],
    //             $validatedData['message']
    //         ));

    //         Log::info('Contact email sent successfully.', [
    //             'email' => $validatedData['email'],
    //         ]);

    //         return response()->json(['message' => 'Email sent successfully!'], 200);
    //     } catch (\Exception $e) {
    //         // Log the error for debugging purposes
    //         Log::error('Failed to send contact email.', [
    //             'error' => $e->getMessage(),
    //             'name' => $validatedData['name'],
    //             'email' => $validatedData['email'],
    //         ]);

    //         // Return a user-friendly error message
    //         return response()->json([
    //             'message' => 'Failed to send email. Please try again later.'
    //         ], 500);
    //     }
    // }

    public function store(Request $request)
    {
        // Validate the input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'required|string',
        ]);

        // Store the contact message in the database
        $contact = new Contact();
        $contact->name = $request->name;
        $contact->email = $request->email;
        $contact->message = $request->message;
        $contact->save();

        // Prepare the data for the email to be sent to the charity
        $messageContent = "Name: " . $request->name . "\n"
                        . "Email: " . $request->email . "\n"
                        . "Message: " . $request->message;

        // Send the email to the charity
        Mail::raw($messageContent, function ($message) use ($request) {
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $message->to(env('MAIL_FROM_ADDRESS'))->subject('Contact Us Message');
        });

        // Prepare and send a confirmation email to the user
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
