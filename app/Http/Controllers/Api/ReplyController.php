<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReplyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::check()) {
            $reply = Reply::with('question.user')->where('phone_number', auth()->user()->phone_number)->paginate(10);
            return response()->json($reply);
        } else {
            return response()->json('Un authenticated user');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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

        $reply->delete();

        return response()->json(['message' => 'Reply is deleted successfully!'], 200);
    }
}
