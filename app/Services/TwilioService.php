<?php

namespace App\Services;

use AfricasTalking\SDK\AfricasTalking;
use Exception;
use Illuminate\Support\Facades\Log;

class AfricaTalkingService
{
    protected $at;
    protected $sms;

    public function __construct()
    {
        $this->initializeService();
    }

    protected function initializeService()
    {
        $username = config('africastalking.username');
        $apiKey = config('africastalking.api_key');

        if (empty($username) || empty($apiKey)) {
            throw new \RuntimeException('Africa\'s Talking credentials not configured');
        }

        $this->at = new AfricasTalking($username, $apiKey);
        $this->sms = $this->at->sms();
    }

    /**
     * Send SMS to recipients
     *
     * @param string|array $recipients Phone number(s) to receive SMS
     * @param string $message SMS content
     * @return mixed
     * @throws \Exception
     */
    public function sendSMS($recipients, $message)
    {
        try {
            // Format recipients if needed
            $recipients = $this->formatRecipients($recipients);

            $result = $this->sms->send([
                'to'      => $recipients,
                'message' => $message,
                'from'    => config('africastalking.sender_id', '')
            ]);

            Log::info('SMS sent via Africa\'s Talking', [
                'recipients' => $recipients,
                'result' => $result
            ]);

            return $result;
        } catch (Exception $e) {
            Log::error('Africa\'s Talking SMS failed', [
                'error' => $e->getMessage(),
                'recipients' => $recipients
            ]);
            throw new \Exception("SMS sending failed: " . $e->getMessage());
        }
    }

    /**
     * Format phone numbers for Africa's Talking API
     */
    protected function formatRecipients($recipients)
    {
        if (is_array($recipients)) {
            return array_map([$this, 'formatPhoneNumber'], $recipients);
        }

        return $this->formatPhoneNumber($recipients);
    }

    /**
     * Format single phone number
     */
    protected function formatPhoneNumber($phoneNumber)
    {
        // Remove all non-digit characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Add country code if missing (assuming Tanzania numbers)
        if (strpos($cleaned, '255') !== 0 && strlen($cleaned) === 9) {
            $cleaned = '255' . $cleaned;
        }

        return '+' . $cleaned;
    }

    // USSD, Airtime, Payments etc. can be added below...
}
