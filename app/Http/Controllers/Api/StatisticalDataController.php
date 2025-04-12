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
        $statisticalData = StatisticalData::with(['user'])->orderBy('created_at','desc')->get();
        return response()->json(['data' => $statisticalData]);
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
        $statisticalData = StatisticalData::findOrFail($id);

        if ($statisticalData->exists()) {
            $statisticalData->update([
                'statistical_data' => $request->input('statistical_data'),
            ]);
        }

        return response()->json(['data' => $statisticalData], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $deleteStatisticalData = StatisticalData::findOrFail($id);
        if ($deleteStatisticalData->exists()) {
            $deleteStatisticalData->delete();
        }
        return response()->json(['message' => 'Statistical data deleted successfully.'], 200);
    }
}
