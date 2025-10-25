<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\batteryBrands;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    public function index(): JsonResponse
    {
        // return ProductCategory::all();        
        $outlets = Outlet::with(['district', 'mapLinks'])->get();

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:product_categories,name',
        ]);

        // Since 'id' is a UUID and should be generated automatically if not provided.
        // If you are using a trait like HasUuids, it will handle it.
        // Otherwise, you might need to generate it here before creating.
        // For simplicity, assuming 'id' is handled by the model or database.
        $productCategory = ProductCategory::create($validated);
        return response()->json($productCategory, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductCategory $productCategory)
    {
        return $productCategory;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductCategory $productCategory)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:product_categories,name,' . $productCategory->id,
        ]);

        $productCategory->update($validated);
        return response()->json($productCategory);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductCategory $productCategory)
    {
        $productCategory->delete();
        return response()->json(null, 204);
    }
}
