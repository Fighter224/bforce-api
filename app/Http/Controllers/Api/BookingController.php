<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    protected $userStatsService;


    /**
     * Display a listing of the resource for the authenticated user.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $bookings = Booking::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    public function byTechnicianAndStatus($technician_id, $status)
    {
        $bookings = DB::table('bookings')
        ->join('user_cars', 'bookings.user_car_id', '=', 'user_cars.id')
        ->join('car_brands', 'user_cars.car_brand_id', '=', 'car_brands.id')
        ->join('car_models', 'user_cars.car_model_id', '=', 'car_models.id')
        ->join('users', 'user_cars.user_id', '=', 'users.id')
        ->Join('invoice', 'bookings.invoice_id', '=', 'invoice.id')
        ->Join('invoice_items', 'invoice.id', '=', 'invoice_items.invoice_id')
        ->Join('products', 'invoice_items.product_id', '=', 'products.id')
        ->leftJoin('warranty_groups', 'products.warranty_group_id', '=', 'warranty_groups.id')
        ->leftJoin('warranty', 'warranty.warranty_group_id', '=', 'warranty_groups.id')
        ->select(
            'bookings.id',
            'users.name',
            'users.phone',
            DB::raw('CONCAT(car_brands.name, " ", car_models.name) as car'),
            'user_cars.license_plate as plate',
            'products.description as product_name',
            'products.description as product_model',
            'products.sale_price as price',
            'warranty.duration_months AS warranty_months',
            'warranty.max_mileage_km AS warranty_km',
            'bookings.location as pickup',
            'bookings.preferred_date',
            'bookings.status'
        )
        ->where('bookings.technician_id', $technician_id)
        ->where(function ($query) use ($status) {
            if ($status === 'assigned') {
                $query->where('bookings.status', 'assigned');
            } elseif ($status === 'completed') {
                $query->where('bookings.status', 'completed');
            }
        })
        ->get();


        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }





    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'service_type' => 'required|string',
                'preferred_date' => 'required|date',
                'preferred_time' => 'required',
                'location' => 'required|string',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'notes' => 'nullable|string',
                'user_car_id' => 'required|exists:user_cars,id',
                'invoice_id' => 'nullable|uuid|exists:invoice,id' // <-- changed to invoice_id
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Warranty claim logic
            if ($request->service_type === 'warranty') {
                if (!$request->invoice_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invoice ID is required for warranty claim.'
                    ], 422);
                }

                // Check if invoice exists for this user and is an installation
                $purchase = Booking::where('user_id', auth()->id())
                    ->where('invoice_id', $request->invoice_id)
                    ->where('service_type', 'installation')
                    ->first();

                if (!$purchase) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No matching battery purchase found for this invoice.'
                    ], 404);
                }
            }

            $booking = new Booking();
            $booking->id = \Illuminate\Support\Str::uuid();
            $booking->user_id = auth()->id();
            $booking->user_car_id = $request->user_car_id;
            $booking->service_type = $request->service_type;
            $booking->preferred_date = $request->preferred_date;
            $booking->preferred_time = $request->preferred_time;
            $booking->location = $request->location;
            $booking->latitude = $request->latitude;
            $booking->longitude = $request->longitude;
            $booking->notes = $request->notes;
            $booking->invoice_id = $request->invoice_id ?: \Illuminate\Support\Str::uuid();
            $booking->status = 'pending';
            $booking->save();

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => $booking
            ], 201);
        } catch (\Exception $e) {
            Log::error('Booking creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $booking = Booking::where('id', $id)
                ->where('user_id', auth()->id())
                ->with('technician')
                ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $booking
            ]);
        } catch (\Exception $e) {
            Log::error('Booking retrieval error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Get Latest Booking.
     */

    public function latestBooking($userId)
    {
        $booking = Booking::where('user_id', $userId)
            ->with('technician')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc') // fallback sorting
            ->first();


        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $booking
        ]);
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

    public function updateLocation(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $booking = Booking::where('id', $id)
                ->where('user_id', auth()->id())
                ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            $booking->latitude = $request->latitude;
            $booking->longitude = $request->longitude;
            $booking->save();

            return response()->json([
                'success' => true,
                'message' => 'Location updated successfully',
                'data' => $booking
            ]);
        } catch (\Exception $e) {
            Log::error('Location update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update location',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $booking->status = $request->status;
        $booking->save();

        if ($request->status === 'completed') {
            $this->userStatsService->updateUserStats($booking);
        }

        return response()->json($booking);
    }

    public function assignTechnician(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'technician_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $booking = Booking::findOrFail($id);
            $booking->technician_id = $request->technician_id;
            $booking->save();

            return response()->json([
                'success' => true,
                'message' => 'Technician assigned successfully',
                'data' => $booking->load('technician')
            ]);
        } catch (\Exception $e) {
            Log::error('Technician assignment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign technician',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getTechnicianForBooking($bookingId)
    {
        $booking = \App\Models\Booking::with(['technician'])->find($bookingId);
        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }
        return response()->json([
            'technician' => [
                'name' => $booking->technician->name ?? 'Ali bin Abu',
                'avatar' => $booking->technician->avatar ?? url('assets/images/technician-avatar.png'),
                'assigned_at' => $booking->technician_assigned_at ?? $booking->created_at,
            ],
            'summary' => [
                'subtotal' => $booking->service_fee ?? 0,
                'fees' => $booking->fees ?? 0,
                'total' => ($booking->service_fee ?? 0) + ($booking->fees ?? 0),
                'payment_method' => $booking->payment_method ?? '**** **** **** 2951',
            ],
        ]);
    }
}
