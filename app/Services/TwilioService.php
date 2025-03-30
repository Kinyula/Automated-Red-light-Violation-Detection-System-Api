<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
    }

    public function sendSms($phone_number, $message)
    {
        $this->client->messages->create(
            $phone_number,
            [
                "from" => env('TWILIO_PHONE_NUMBER'), 
                "body" => $message,
            ]
        );
    }
}
