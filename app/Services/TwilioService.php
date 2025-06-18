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
        $username = env('AFRICASTALKING_USERNAME');
        $apiKey = env('AFRICASTALKING_API_KEY');

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
     * @param string|null $senderId Override default sender ID
     * @return mixed
     * @throws \Exception
     */
    public function sendSMS($recipients, $message, $senderId = null)
    {
        try {
            $recipients = $this->formatRecipients($recipients);
            $senderId = $senderId ?? env('AFRICASTALKING_SENDER_ID', 'INFORM');

            $options = [
                'to' => $recipients,
                'message' => $message,
            ];

            // Only add sender ID if it's not empty (Africa's Talking requirement)
            if (!empty($senderId)) {
                $options['from'] = $senderId;
            }

            $result = $this->sms->send($options);

            Log::info('Africa\'s Talking SMS sent successfully', [
                'recipients' => $recipients,
                'sender_id' => $senderId,
                'message' => $message,
                'result' => $result
            ]);

            return $result;
        } catch (Exception $e) {
            Log::error('Africa\'s Talking SMS failed', [
                'error' => $e->getMessage(),
                'recipients' => $recipients,
                'sender_id' => $senderId ?? 'default'
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
            return implode(',', array_map([$this, 'formatPhoneNumber'], $recipients));
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

        return $cleaned;
    }
}
