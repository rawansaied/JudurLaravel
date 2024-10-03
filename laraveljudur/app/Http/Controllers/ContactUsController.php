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
    public function showContactForm()
    {
        return view('contact');
    }

    public function sendContactMessage(Request $request)
    {
        // Validate incoming request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
        ]);

        try {
            // Log the attempt to send an email
            Log::info('Attempting to send contact email.', [
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
            ]);
            Contact::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'message' => $validatedData['message'],
            ]);

            // Create and send the email
            Mail::to('shroukeslam909@gmail.com')->send(new ContactUsMail(
                $validatedData['name'],
                $validatedData['email'],
                $validatedData['message']
            ));

            Log::info('Contact email sent successfully.', [
                'email' => $validatedData['email'],
            ]);

            return response()->json(['message' => 'Email sent successfully!'], 200);
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            Log::error('Failed to send contact email.', [
                'error' => $e->getMessage(),
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
            ]);

            // Return a user-friendly error message
            return response()->json([
                'message' => 'Failed to send email. Please try again later.'
            ], 500);
        }
    }
}
