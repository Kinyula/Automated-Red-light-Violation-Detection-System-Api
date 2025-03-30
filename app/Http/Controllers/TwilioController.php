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
        $request->validate([
            'phone_number' => 'required',
            'message' => 'required',
        ]);

        $this->twilio->sendSms($request->phone_number, $request->message);

        return response()->json(['message' => 'SMS sent successfully!']);
    }
}
