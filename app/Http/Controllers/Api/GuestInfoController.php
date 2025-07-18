<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class GuestInfoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = [];

        $users = User::where('position', '=', 'Software Engineer')->orWhere( 'position', '=', 'Quality Analyst' )->orWhere( 'position', '=', 'Hardware Expert' )->orWhere( 'position', '=', 'Project Manager' )->get();

        return response()->json([

            'users' => $users
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

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
        //
    }
}
