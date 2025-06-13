<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PoliceOfficer;
use Illuminate\Http\Request;

class PoliceOfficerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $officers = PoliceOfficer::get();

        return response()->json([
            'officers' => $officers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone_number' => "required|string",
            'gender' => 'required|string',
            'police_post' => 'required|string',
        ]);

        // Create a new police officer
        $officer = new PoliceOfficer();
        $officer->first_name = $request->first_name;
        $officer->last_name = $request->last_name;
        $officer->phone_number = $request->phone_number;
        $officer->email = $request->email;
        $officer->gender = $request->gender;
        $officer->police_post = $request->police_post;
        $officer->save();

        return response()->json(['message' => 'Police officer created successfully'], 201);
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
        //
    }
}
