<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CarCompatibleBattery;
use App\Models\BatteryBrand;
use App\Models\BatteryBrandSeries;
use App\Models\BatterySize;
use App\Models\UserCar;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class CarCompatibleBatteryController extends Controller
{
    public function getCompatibleBatteries($car_id)
    {
        $userCar = UserCar::find($car_id);
        if (!$userCar) {
            return response()->json(['message' => 'Car not found.'], 404);
        }

        $batterySizeIds = DB::table('car_compatible_battery')
            ->where('car_model_id', $userCar->car_model_id)
            ->distinct()
            ->pluck('battery_sizes') // ✅ check column name here
            ->toArray();

            Log::info('Battery size IDs:', $batterySizeIds);



        if (empty($batterySizeIds)) {
            return response()->json([]);
        }

        $batterySizes = DB::table('battery_sizes')
            ->whereIn('id', $batterySizeIds)
            ->get(['id', 'name']);

            Log::info('Battery size IDs:', (array)$batterySizes);


        $result = [];
        foreach ($batterySizes as $size) {
            $brands = DB::table('products')
                ->join('battery_brands', 'products.battery_brand_id', '=', 'battery_brands.id')
                ->where('products.battery_size_id', $size->id)
                ->pluck('battery_brands.name')
                ->unique()
                ->values()
                ->toArray();

            $result[] = [
                'id' => $size->id,
                'name' => $size->name,
                'brands' => $brands
            ];
        }

        return response()->json($result);
    }

}
