<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;
use App\Models\Donation;
use Illuminate\Support\Facades\Log;

class SMSController extends Controller
{
    public function receiveSMS(Request $request)
    {
        Log::info('Received SMS', $request->all()); // Log the incoming request

        // Extract message and sender info from Twilio's request
        $from = $request->input('From');
        $body = $request->input('Body');
        
        // Parse donation amount from the message
        if (preg_match('/donate (\d+)/i', $body, $matches)) {
            $amount = (int) $matches[1];
    
             Donation::create(['phone_number' => $from, 'amount' => $amount]);
    
            // Respond to the sender
            $this->sendResponse($from, "Thank you for donating $amount fake dollars!");
    
            // Return a JSON response for Postman testing
            return response()->json([
                'message' => "Thank you for donating $amount fake dollars!",
                'from' => $from,
                'amount' => $amount
            ]);
        } else {
            $this->sendResponse($from, "Invalid message. Please send 'Donate [amount]' to contribute.");
    
            // Return a JSON response for invalid input
            return response()->json([
                'message' => "Invalid message. Please send 'Donate [amount]' to contribute.",
                'from' => $from,
            ]);
        }
    }
    

    protected function sendResponse($to, $message)
    {
        $twilio = new Client(env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN'));
        $twilio->messages->create($to, [
            'from' => env('TWILIO_PHONE_NUMBER'),
            'body' => $message
        ]);
    }
}
