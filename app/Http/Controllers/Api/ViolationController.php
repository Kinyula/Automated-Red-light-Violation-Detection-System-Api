<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Violation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Twilio\Rest\Client;
use App\Models\User;

class ViolationController extends Controller
{
    /**
     * Display a listing of the resource.
     * GET /api/violations
     */
    public function index()
    {
        if (auth()->user()->role_id !== '0') {
            $violations = Violation::with(['user'])->orderBy('created_at', 'desc')->get();


            return response()->json([
                'message' => 'Violations retrieved successfully!',
                'violations' => $violations,

            ], 200);
        } elseif (auth()->user()->role_id === '0') {
            $violation = Violation::with(['user'])->where('user_id', auth()->user()->id)->orderBy('created_at', 'desc')->get();
            return response()->json([
                'message' => 'Violations retrieved successfully!',
                'violation' => $violation,

            ], 200);
        } else {
            return response()->json([
                'message' => 'Access denied. Drivers are not authorized to view violations.',
            ], 403);
        }
    }


    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $violations = Violation::where('license_plate', 'LIKE', "%{$query}%")
            ->get();

        return response()->json($violations);
    }
    /**
     * Store a newly created resource in storage.
     * POST /api/violations
     */


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'license_plate' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $controlNumber = 'NAMBARI YA KUMBUKUMBU : ' . strtoupper(substr(md5(time() . $user->id), 0, 8));
        $fineAmount = 'TZS 50,000';

        $message = "🚨 Taarifa ya Ukiukaji wa trafiki 🚨\n\n" .
            "Mpendwa {$user->first_name} {$user->last_name},\n\n" .
            "Umebainika kukiuka sheria ya trafiki ya kuvuka wakati wa Taa nyekundu.\n\n" .
            " Nambari ya Leseni: {$request->license_plate}\n" .
            " Nambari ya Kudhibiti: {$controlNumber}\n" .
            " Kiasi cha Faini: {$fineAmount}\n\n" .
            " MALIPO KWA MTANDAO:\n" .
            "• Airtel Money: 0683878268\n" .
            "• M-Pesa: 0769531356\n\n" .
            "Tafadhali zingatia sheria za trafiki ili kuepuka adhabu zaidi.\n\n" .
            "Asante kwa ushirikiano wako.\n\n" .
            "© Mamlaka ya Usimamizi wa Trafiki";

        $violation = Violation::create([
            'user_id' => $request->user_id,
            'license_plate' => $request->license_plate,
            'message' => $message,
        ]);

        // Enhanced SMS notification handling
        if ($user->phone_number) {
            $smsSent = $this->sendSMSNotification($user->phone_number, $message);

            if (!$smsSent) {
                Log::error("Failed to send SMS notification for violation ID: {$violation->id}");
                // You might want to queue a retry here or notify admins
            }
        } else {
            Log::warning("No phone number found for user ID: {$user->id}");
        }

        return response()->json([
            'message' => 'Violation recorded successfully' . ($user->phone_number ? ' and notification sent!' : ' but no phone number available for notification'),
            'data' => $violation
        ], 201);
    }

    /**
     * Send an SMS notification using Africa's Talking with better error handling
     */
    private function sendSMSNotification($phoneNumber, $message)
    {
        $username = env('AFRICASTALKING_USERNAME');
        $apiKey = env('AFRICASTALKING_API_KEY');
        $senderId = env('AFRICASTALKING_SENDER_ID', 'INFORM'); // Default to 'INFORM' if not set

        if (empty($username) || empty($apiKey)) {
            Log::error('Africa\'s Talking credentials not configured');
            return false;
        }

        // Format phone number to international format
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);
        if (!$phoneNumber) {
            Log::error("Invalid phone number format: {$phoneNumber}");
            return false;
        }

        try {
            $AT = new \AfricasTalking\SDK\AfricasTalking($username, $apiKey);
            $sms = $AT->sms();

            $options = [
                'to' => $phoneNumber,
                'message' => $message,
                'enqueue' => true
            ];

            // Only add senderId if it's not empty (some accounts may not have this privilege)
            if (!empty($senderId)) {
                $options['from'] = $senderId;
            }

            $result = $sms->send($options);

            if ($result['status'] === 'success') {
                $recipient = $result['data']->SMSMessageData->Recipients[0] ?? null;

                Log::info("SMS sent successfully", [
                    'to' => $phoneNumber,
                    'sender_id' => $senderId ?? 'default',
                    'message_id' => $recipient->messageId ?? null,
                    'cost' => $recipient->cost ?? null,
                    'status' => $recipient->status ?? null
                ]);

                return true;
            } else {
                Log::error("Africa's Talking API error", [
                    'status' => $result['status'],
                    'error' => $result['data']->SMSMessageData->Recipients[0]->status ?? 'Unknown error',
                    'request' => $options
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('SMS sending failed', [
                'exception' => $e->getMessage(),
                'phone' => $phoneNumber,
                'sender_id' => $senderId
            ]);
            return false;
        }
    }

    /**
     * Format phone number to international format
     */
    private function formatPhoneNumber($phoneNumber)
    {
        // Return false if empty
        if (empty($phoneNumber)) {
            Log::error("Empty phone number provided");
            return false;
        }

        // Remove all non-digit characters
        $digits = preg_replace('/[^0-9]/', '', (string)$phoneNumber);

        // Check for minimum length (Tanzanian numbers are 9-12 digits after cleaning)
        if (strlen($digits) < 9 || strlen($digits) > 12) {
            Log::warning("Invalid phone number length", [
                'original' => $phoneNumber,
                'digits' => $digits,
                'length' => strlen($digits)
            ]);
            return false;
        }

        // Handle local Tanzanian format (068..., 078..., etc.)
        if ($digits[0] === '0' && strlen($digits) === 10) {
            // Convert to international format (255...)
            return '255' . substr($digits, 1);
        }

        // Handle international format (255...)
        if (substr($digits, 0, 3) === '255' && strlen($digits) === 12) {
            return $digits;
        }

        // Handle numbers with country code but missing leading zero (+25578...)
        if (strlen($digits) === 12 && $digits[0] !== '0') {
            return $digits;
        }

        // If we get here, the format isn't recognized
        Log::warning("Phone number format not recognized", [
            'original' => $phoneNumber,
            'digits' => $digits,
            'length' => strlen($digits)
        ]);
        return false;
    }

    /**
     * Display the specified resource.
     * GET /api/violations/{id}
     */
    public function show(string $id)
    {
        $violation = Violation::find($id);

        if (!$violation) {
            return response()->json(['message' => 'Violation not found.'], 404);
        }

        return response()->json([
            'message' => 'Violation retrieved successfully!',
            'data' => $violation
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     * PUT /api/violations/{id}
     */
    public function update(Request $request, string $id)
    {
        $violation = Violation::find($id);

        if (!$violation) {
            return response()->json(['message' => 'Violation not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'license_plate' => 'sometimes|string|max:20',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $violation->update($request->only(['license_plate', 'violation_time', 'traffic_light_state']));

        return response()->json([
            'message' => 'Violation updated successfully!',
            'data' => $violation
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /api/violations/{id}
     */
    public function destroy(string $id)
    {
        $violation = Violation::find($id);

        if (!$violation) {
            return response()->json(['message' => 'Violation not found.'], 404);
        }

        $violation->delete();

        return response()->json(['message' => 'Violation deleted! Now you need to '], 200);
    }
}
