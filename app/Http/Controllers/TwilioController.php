<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TwilioService;

class TwilioController extends Controller
{
    protected $twilio;

    public function __construct(TwilioService $twilio)
    {
        $this->twilio = $twilio;
    }

    public function sendSms(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'phone_number' => 'required',
            'message' => 'required',
            'user_id' => 'required|integer',  // Assuming user_id is an integer
        ]);

        // Send the SMS via Twilio service
        $this->twilio->sendSms($request->phone_number, $request->message);

        // Return a JSON response with the message, user_id, and phone number
        return response()->json([
            'message' => 'SMS sent successfully!',
            'user_id' => $request->user_id,
            'phone_number' => $request->phone_number,
            'message_content' => $request->message,
        ]);
    }
}
