<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Violation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Twilio\Rest\Client;

class ViolationController extends Controller
{
    /**
     * Display a listing of the resource.
     * GET /api/violations
     */
    public function index()
    {
        if (auth()->user()->position !== 'driver') {
            $violations = Violation::with(['user'])->get();


            return response()->json([
                'message' => 'Violations retrieved successfully!',
                'violations' => $violations,

            ], 200);
        } elseif (auth()->user()->position === 'driver') {
            $violation = Violation::with(['user'])->where('user_id', auth()->user()->id)->get();
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


    /**
     * Store a newly created resource in storage.
     * POST /api/violations
     */
    public function store(Request $request)
    {
        // Create a new violation
        $violation = Violation::create([

            'user_id' => $request->user_id,
            'license_plate' => $request->license_plate,
            'message' => "🚨 Traffic Violation Notice 🚨

You have been recorded violating a red light traffic rule.

Please pay a penalty fee of **TSh 50,000** via **M-Pesa** to the following number:

📱 **+255 712 345 678**

Payment Deadline: **Within 7 days of receiving this notice**

⚠️ Failure to pay within the given time will result in **termination of your vehicle's license registration**.

Drive responsibly. This is an automated message from the Red Light Violation Detection System.",
        ]);

        // // Send SMS notification using Twilio
        // $this->sendSMSNotification($request->phone_number, $violation);

        return response()->json([
            'message' => 'Violation recorded successfully and notification sent!',
            'data' => $violation
        ], 201);
    }

    /**
     * Send an SMS notification using Twilio.
     */
    private function sendSMSNotification($phoneNumber, $violation)
    {
        $twilioSid = env('TWILIO_SID');
        $twilioAuthToken = env('TWILIO_AUTH_TOKEN');
        $twilioPhoneNumber = env('TWILIO_PHONE_NUMBER');

        try {
            $twilio = new Client($twilioSid, $twilioAuthToken);

            // SMS content
            $messageBody = "Traffic Violation Alert!\n" .
                "License Plate: {$violation->license_plate}\n" .
                "Violation Time: {$violation->violation_time}\n" .
                "Traffic Light State: {$violation->traffic_light_state}";

            // Send the SMS
            $twilio->messages->create(
                $phoneNumber, // Destination phone number
                [
                    'from' => $twilioPhoneNumber,
                    'body' => $messageBody
                ]
            );
        } catch (\Exception $e) {
            Log::error('Twilio SMS failed: ' . $e->getMessage());
        }
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
