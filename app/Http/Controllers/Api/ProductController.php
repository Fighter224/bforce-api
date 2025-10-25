<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BatteryBrand;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Get all products.
     */
    public function index(): JsonResponse
    {
        $products = Product::with([
            'batteryBrand', 
            'productCategory', 
            'batteryBrandSeries', 
            'batterySize'
        ])->get();

        $formattedProducts = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'battery_brand_id' => $product->battery_brand_id,
                'product_category_id' => $product->product_category_id,
                'battery_brand_series_id' => $product->battery_brand_series_id,
                'battery_size_id' => $product->battery_size_id,
                'description' => $product->description,
                'image' => $product->image,
                'cost_price' => $product->cost_price,
                'sale_price' => $product->sale_price,
                'capacity' => $product->capacity,
                'cca' => $product->cca,
                'voltage' => $product->voltage,
                'battery_brand' => $product->batteryBrand->name ?? null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $formattedProducts,
        ]);
    }

    /**
     * Get products by battery brand.
     */
    public function getByBatteryBrand(string $batteryBrandId): JsonResponse
    {
        $batteryBrand = BatteryBrand::findOrFail($batteryBrandId);
        $products = $batteryBrand->products()->with([
            'productCategory', 
            'batteryBrandSeries', 
            'batterySize'
        ])->get();

        $formattedProducts = $products->map(function ($product) use ($batteryBrand) {
            return [
                'id' => $product->id,
                'battery_brand_id' => $product->battery_brand_id,
                'product_category_id' => $product->product_category_id,
                'battery_brand_series_id' => $product->battery_brand_series_id,
                'battery_size_id' => $product->battery_size_id,
                'description' => $product->description,
                'image' => $product->image,
                'cost_price' => $product->cost_price,
                'sale_price' => $product->sale_price,
                'capacity' => $product->capacity,
                'cca' => $product->cca,
                'voltage' => $product->voltage,
                'battery_brand' => $batteryBrand->name,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $formattedProducts,
        ]);
    }

    /**
     * Get a specific product.
     */
    public function show(string $id): JsonResponse
    {
        $product = Product::with([
            'batteryBrand', 
            'productCategory', 
            'batteryBrandSeries', 
            'batterySize'
        ])->findOrFail($id);
        
        $formattedProduct = [
            'id' => $product->id,
            'battery_brand_id' => $product->battery_brand_id,
            'product_category_id' => $product->product_category_id,
            'battery_brand_series_id' => $product->battery_brand_series_id,
            'battery_size_id' => $product->battery_size_id,
            'description' => $product->description,
            'image' => $product->image,
            'cost_price' => $product->cost_price,
            'sale_price' => $product->sale_price,
            'capacity' => $product->capacity,
            'cca' => $product->cca,
            'voltage' => $product->voltage,
            'battery_brand' => $product->batteryBrand->name ?? null,
        ];

        return response()->json([
            'status' => 'success',
            'data' => $formattedProduct,
        ]);
    }
}