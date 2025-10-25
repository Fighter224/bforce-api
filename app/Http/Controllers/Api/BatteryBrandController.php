<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BatteryBrand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BatteryBrandController extends Controller
{
    /**
     * Get all battery brands.
     */
    public function index(): JsonResponse
    {
        $batteryBrands = BatteryBrand::orderBy('seq')->get();

        $formattedBatteryBrands = $batteryBrands->map(function ($batteryBrand) {
            return [
                'id' => $batteryBrand->id,
                'name' => $batteryBrand->name,
                'image' => $batteryBrand->image,
                'seq' => $batteryBrand->seq,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $formattedBatteryBrands,
        ]);
    }

    /**
     * Get a specific battery brand.
     */
    public function show(string $id): JsonResponse
    {
        $batteryBrand = BatteryBrand::findOrFail($id);
        
        $formattedBatteryBrand = [
            'id' => $batteryBrand->id,
            'name' => $batteryBrand->name,
            'image' => $batteryBrand->image,
            'seq' => $batteryBrand->seq,
        ];

        return response()->json([
            'status' => 'success',
            'data' => $formattedBatteryBrand,
        ]);
    }
}