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

        // Get the user first to access their phone number
        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $message = "🚨 Traffic Violation Notice 🚨\n\n" .
            "Dear {$user->first_name} {$user->last_name},\n\n" .
            "You have been recorded violating a red light traffic rule.\n\n" .
            "License Plate: {$request->license_plate}\n" .
            "Please pay a penalty fee of TSh 50,000 via M-Pesa to the following number:\n\n" .
            "📱 +255 712 345 678\n\n" .
            "Payment Deadline: Within 7 days of receiving this notice\n\n" .
            "⚠️ Failure to pay within the given time will result in termination of your vehicle's license registration.\n\n" .
            "Drive responsibly. This is an automated message from the Red Light Violation Detection System.";




        $violation = Violation::create([
            'user_id' => $request->user_id,
            'license_plate' => $request->license_plate,
            'message' => $message,
        ]);


        if ($user->phone_number) {
            // Send SMS notification using Africa's Talking
            $this->sendSMSNotification($user->phone_number, $message);
        } else {
            Log::warning("No phone number found for user ID: {$user->id}");
        }

        return response()->json([
            'message' => 'Violation recorded successfully' . ($user->phone_number ? ' and notification sent!' : ' but no phone number available for notification'),
            'data' => $violation
        ], 201);
    }

    /**
     * Send an SMS notification using Africa's Talking.
     */
    private function sendSMSNotification($phoneNumber, $message)
    {
        $username = env('AFRICASTALKING_USERNAME');
        $apiKey = env('AFRICASTALKING_API_KEY');

        // Remove any non-digit characters from the phone number
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Add country code if not present (assuming Tanzania numbers)
        if (strpos($phoneNumber, '255') !== 0) {
            $phoneNumber = '255' . substr($phoneNumber, -9);
        }

        try {
            // Initialize Africa's Talking SDK
            $AT = new \AfricasTalking\SDK\AfricasTalking($username, $apiKey);
            $sms = $AT->sms();

            // Send the SMS
            $result = $sms->send([
                'to'      => $phoneNumber,
                'message' => $message,
                // 'from' is optional if you have a shortcode or sender ID configured
            ]);

            // Log the response for debugging
            Log::info('Africa\'s Talking SMS response:', (array)$result);
        } catch (\Exception $e) {
            Log::error('Africa\'s Talking SMS failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to send SMS notification.'], 500);
        }
    }

    /**
     * Send an SMS notification using Twilio.
     */


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
