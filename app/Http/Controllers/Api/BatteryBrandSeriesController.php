<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BatteryBrandSeries;
use Illuminate\Http\Request;

class BatteryBrandSeriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return BatteryBrandSeries::orderBy('seq')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'battery_brand_id' => 'required|exists:battery_brands,id',
            'seq' => 'nullable|integer',
        ]);

        $batteryBrandSeries = BatteryBrandSeries::create($validated);
        return response()->json($batteryBrandSeries, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(BatteryBrandSeries $batteryBrandSeries)
    {
        return $batteryBrandSeries;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BatteryBrandSeries $batteryBrandSeries)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'battery_brand_id' => 'sometimes|required|exists:battery_brands,id',
            'seq' => 'nullable|integer',
        ]);

        $batteryBrandSeries->update($validated);
        return response()->json($batteryBrandSeries);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BatteryBrandSeries $batteryBrandSeries)
    {
        $batteryBrandSeries->delete();
        return response()->json(null, 204);
    }

    /**
     * Display a listing of the resource by battery brand.
     */
    public function getByBatteryBrand(string $batteryBrandId)
    {
        return BatteryBrandSeries::where('battery_brand_id', $batteryBrandId)
            ->orderBy('seq')
            ->get();
    }
}
