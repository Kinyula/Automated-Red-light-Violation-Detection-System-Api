<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StatisticalData;
use Illuminate\Http\Request;

class StatisticalDataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (auth()->user()->role_id === '2') {
            $statisticalData = StatisticalData::with(['user'])->where('user_id', auth()->user()->id)->get();
            return response()->json(['data' => $statisticalData]);
        } else {
            $statisticalData = StatisticalData::with(['user'])->get();
            return response()->json(['data' => $statisticalData]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'statistical_data' => 'required|string',
        ]);

        $statisticalData = new StatisticalData();
        $statisticalData->user_id = auth()->user()->id;
        $statisticalData->statistical_data = $validatedData['statistical_data'];
        $statisticalData->save();

        return response()->json(['data' => $statisticalData], 201);




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
