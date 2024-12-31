<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Retrieve all questions
        $questions = Question::get();

        return response()->json([
            'message' => 'Questions retrieved successfully!',
            'data' => $questions
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|max:15',
            'question' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create a new question
        $question = Question::create([
            'phone_number' => $request->phone_number,
            'question' => $request->question,

        ]);

        return response()->json([
            'message' => 'Question submitted successfully!',
            'data' => $question
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $question = Question::find($id);

        if (!$question) {
            return response()->json(['message' => 'Question not found.'], 404);
        }

        return response()->json([
            'message' => 'Question retrieved successfully!',
            'data' => $question
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $question = Question::find($id);

        if (!$question) {
            return response()->json(['message' => 'question not found.'], 404);
        }

        $validator = Validator::make($request->all(), [

            'question' => 'sometimes|string|max:20',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $question->update($request->only(['reply']));

        return response()->json([
            'message' => 'Question updated successfully!',
            'data' => $question
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $question = Question::find($id);

        if (!$question) {
            return response()->json(['message' => 'Question not found.'], 404);
        }

        $question->delete();

        return response()->json(['message' => 'Question deleted successfully!'], 200);
    }
}
