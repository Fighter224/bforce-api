<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Added for logging
use App\Models\UserCar;
use App\Models\Product;
use App\Models\CarCompatibleBattery; // Added
use App\Models\CarModel; // Added for car name

class BatterySuggestionController extends Controller
{
    /**
     * Suggest batteries based on the authenticated user's registered cars.
     */
    public function suggestByMyCars(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            Log::warning('BatterySuggestionController: Unauthenticated user attempt.');
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        Log::info("BatterySuggestionController: Processing suggestions for user ID: {$user->id}");

        // Eager load user cars with their car model details
        $userCars = UserCar::where('user_id', $user->id)->with('carModel.carBrand')->get();
        Log::info("BatterySuggestionController: User cars found: " . $userCars->count() . " for user ID: {$user->id}.");

        if ($userCars->isEmpty()) {
            Log::info("BatterySuggestionController: No cars registered for user ID: {$user->id}. Returning 404.");
            return response()->json(['message' => 'No cars registered for this user.'], 404);
        }

        $suggestedProducts = collect();
        $processedCarDetails = [];
        $allFoundBatterySizeIds = [];

        foreach ($userCars as $car) {
            $carModelName = $car->carModel ? ($car->carModel->carBrand ? $car->carModel->carBrand->name . ' ' : '') . $car->carModel->name : 'Unknown Model';
            $carDetail = [
                'car_id' => $car->id,
                'license_plate' => $car->license_plate,
                'car_model_name' => $carModelName,
                'car_model_id' => $car->car_model_id,
                'compatible_battery_sizes_found' => [],
                'suggestions_count' => 0,
                'status_message' => ''
            ];

            // Find compatible battery sizes for this car model
            $compatibleBatteries = CarCompatibleBattery::where('car_model_id', $car->car_model_id)->get();

            if ($compatibleBatteries->isEmpty()) {
                $carDetail['status_message'] = 'No compatible battery size information found for this car model.';
                $processedCarDetails[] = $carDetail;
                continue;
            }

            $batterySizeIdsForThisCar = $compatibleBatteries->pluck('battery_sizes')->unique()->toArray();
            $carDetail['compatible_battery_sizes_found'] = $batterySizeIdsForThisCar;
            $allFoundBatterySizeIds = array_merge($allFoundBatterySizeIds, $batterySizeIdsForThisCar);
            $processedCarDetails[] = $carDetail; // Add car detail even if products are not yet searched
        }

        $allFoundBatterySizeIds = array_unique($allFoundBatterySizeIds);

        if (empty($allFoundBatterySizeIds)) {
            Log::info("BatterySuggestionController: No compatible battery_size_ids found for user ID: {$user->id}. Returning 404.", ['processed_cars' => $processedCarDetails]);
             return response()->json([
                'message' => 'Could not determine compatible battery sizes for any of your registered cars.',
                'cars_processed_details' => $processedCarDetails
            ], 404);
        }
        Log::info("BatterySuggestionController: All found battery_size_ids for user ID {$user->id}: " . json_encode($allFoundBatterySizeIds));

        // Fetch products matching battery_size_ids (before checking sale_price)
        $productsMatchingSizes = Product::whereIn('battery_size_id', $allFoundBatterySizeIds)
                                        ->with(['batteryBrand', 'batteryBrandSeries', 'batterySize.batteryType'])
                                        ->get();
        Log::info("BatterySuggestionController: Products matching battery_size_ids (before sale_price check) for user ID {$user->id}: " . $productsMatchingSizes->count());

        // Fetch all products that match any of the found battery_size_ids AND have a sale_price
        $productsForSuggestions = $productsMatchingSizes->whereNotNull('sale_price');
        // Note: ->get() was already called, so filter on the collection or re-query if preferred.
        // For simplicity here, filtering the collection. If performance is an issue for very large $productsMatchingSizes, re-query.
        // $productsForSuggestions = Product::whereIn('battery_size_id', $allFoundBatterySizeIds)
        //                                 ->with(['batteryBrand', 'batteryBrandSeries', 'batterySize.batteryType'])
        //                                 ->whereNotNull('sale_price')
        //                                 ->get();
        Log::info("BatterySuggestionController: Products for suggestions (after sale_price check) for user ID {$user->id}: " . $productsForSuggestions->count());


        // Update suggestions_count in processedCarDetails
        foreach($processedCarDetails as $index => $detail) {
            $count = 0;
            if (!empty($detail['compatible_battery_sizes_found'])) {
                foreach($productsForSuggestions as $product) {
                    if (in_array($product->battery_size_id, $detail['compatible_battery_sizes_found'])) {
                        $count++;
                    }
                }
            }
            $processedCarDetails[$index]['suggestions_count'] = $count;
            if ($count === 0 && empty($detail['status_message'])) {
                 $processedCarDetails[$index]['status_message'] = 'No available products match the compatible battery sizes for this car.';
            } elseif ($count > 0) {
                 $processedCarDetails[$index]['status_message'] = 'Found suggestions.';
            }
        }


        if ($productsForSuggestions->isEmpty()) {
            Log::info("BatterySuggestionController: No products with sale_price found for user ID: {$user->id} after filtering. Returning 404.", ['processed_cars' => $processedCarDetails]);
            return response()->json([
                'message' => 'No battery products found matching the compatible sizes for your registered cars, or none have a sale price.',
                'cars_processed_details' => $processedCarDetails
            ], 404);
        }

        Log::info("BatterySuggestionController: Successfully found " . $productsForSuggestions->count() . " suggestions for user ID: {$user->id}.");
        return response()->json([
            'message' => 'Battery suggestions based on your cars.',
            'suggestions' => $productsForSuggestions->unique('id')->values(), // Ensure unique products
            'cars_processed_details' => $processedCarDetails
        ]);
    }
}
