<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Reply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ReplyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::check()) {
            if (auth()->user()->role_id === '0') {
                $data = Reply::with(['question', 'user'])->where('phone_number', auth()->user()->phone_number)->orderBy('created_at', 'desc')->paginate(10);
                return response()->json($data);
            }

            if (auth()->user()->role_id === '2') {
                $data = Reply::with(['question', 'user'])->orderBy('created_at', 'desc')->paginate(10);
                return response()->json($data);
            }
        } else {
            return response()->json('Un authenticated user');
        }
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $replies = Reply::where(function ($q) use ($query) {
            $q->where('phone_number', 'LIKE', '%' . $query . '%')
              ->orWhere('reply', 'LIKE', '%' . $query . '%');
        })

        ->get();


        return response()->json(['replies' => $replies]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate incoming request data
        // $validator = Validator::make($request->all(), [
        //     'phone_number' => 'required|string|max:15',
        //     'question_id' => 'required|string',
        //     'reply' => 'required|string|max:255',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json(['errors' => $validator->errors()], 422);
        // }


        $reply = Reply::create([
            'phone_number' => $request->phone_number,
            'question_id' => $request->question_id,
            'reply' => $request->reply,
            'user_id' => auth()->user()->id
        ]);


        $question = Question::find($request->question_id);
        if ($question) {
            $question->reply_status = 'replied';
            $question->update();
        }
        // Send SMS notification
        $this->sendSMSNotification($request->phone_number, $request->reply);
        // Log the reply for debugging
        Log::info('Reply created:', [
            'reply' => $reply,
            'user_id' => auth()->user()->id,
            'question_id' => $request->question_id
        ]);
        return response()->json([
            'message' => 'Reply message is sent successfully',
            'data' => $reply
        ], 201);
    }

    private function sendSMSNotification($phoneNumber, $replyData)
    {
        $username = env('AFRICASTALKING_USERNAME');
        $apiKey = env('AFRICASTALKING_API_KEY');

        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);


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
                'message' => $replyData,
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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $reply = Reply::find($id);

        if (!$reply) {
            return response()->json(['message' => 'Reply not found.'], 404);
        }

        $question = Question::find($reply->question_id);
        if ($question) {
            $question->reply_status = 'not replied';
            $question->update();
        }
        $reply->delete();


        return response()->json(['message' => 'Reply is deleted successfully!'], 200);
    }
    public function updateQuestionStatus($questionId)
    {
        $question = Question::find($questionId);

        if (!$question) {
            return response()->json(['message' => 'Question not found.'], 404);
        }

        $question->reply_status = 'not replied';
        $question->save();

        return response()->json(['message' => 'Question status updated successfully.'], 200);
    }
}
