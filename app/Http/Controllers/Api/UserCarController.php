<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserCar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserCarController extends Controller
{
    /**
     * Display a listing of the resource for the authenticated user.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userCars = UserCar::where('user_id', $user->id)->with(['carBrand', 'carModel'])->get();
        return response()->json($userCars);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'car_brand_id' => 'required|uuid|exists:car_brands,id', // Assuming car_brands table and id column
            'car_model_id' => 'required|uuid|exists:car_models,id', // Assuming car_models table and id column
            'license_plate' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $userCar = UserCar::create([
            'user_id' => $user->id,
            'car_brand_id' => $request->car_brand_id,
            'car_model_id' => $request->car_model_id,
            'license_plate' => $request->license_plate,
        ]);

        return response()->json($userCar, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(UserCar $userCar)
    {
        // Optional: Add authorization to ensure the user owns this car
        if (Auth::id() !== $userCar->user_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        return response()->json($userCar->load(['carBrand', 'carModel']));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserCar $userCar)
    {
        // Optional: Add authorization
        if (Auth::id() !== $userCar->user_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validator = Validator::make($request->all(), [
            'car_brand_id' => 'sometimes|required|uuid|exists:car_brands,id',
            'car_model_id' => 'sometimes|required|uuid|exists:car_models,id',
            'license_plate' => 'sometimes|required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $userCar->update($request->only(['car_brand_id', 'car_model_id', 'license_plate']));

        return response()->json($userCar);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserCar $userCar)
    {
        // Optional: Add authorization
        if (Auth::id() !== $userCar->user_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $userCar->delete();

        return response()->json(null, 204);
    }
}
