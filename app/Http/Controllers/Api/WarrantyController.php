<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserCar;
use App\Models\InvoiceItem;
use app\Models\Warranty;
use App\Models\Booking;
use Carbon\Carbon;

class WarrantyController extends Controller
{
    /**
     * GET /api/warranties/by-plate?user_id={userId}&license_plate={plate}
     *
     * Returns warranty info for products purchased by the user,
     * but filtered to bookings that reference the given user_car (license plate).
     */
    public function byPlate(Request $request)
    {
        $userId = $request->query('user_id');
        $plate  = $request->query('license_plate');

        if (!$userId || !$plate) {
            return response()->json([
                'message' => 'user_id and license_plate are required'
            ], 400);
        }

        // normalize plate
        $plate = strtoupper(str_replace(' ', '', $plate));

        // Find bookings for this user where the booking's userCar has this plate.
        $bookings = Booking::where('user_id', $userId)
            ->whereHas('userCar', function ($q) use ($plate) {
                $q->where('license_plate', $plate);
            })
            ->with([
                'userCar:id,license_plate,user_id',
                'invoice.invoiceItems.product.warrantyGroup.warranties' // load warranties through group
            ])
            ->orderByDesc('created_at')
            ->get();

        if ($bookings->isEmpty()) {
            return response()->json([
                'message' => 'No bookings found for this user and license plate'
            ], 404);
        }

        $results = [];
        $userCars = UserCar::where('user_id', $userId)
            ->where('license_plate', $plate)
            ->first();

        foreach ($bookings as $booking) {
            $userCar = $booking->userCar;
            $invoice = $booking->invoice;

            if (!$invoice) {
                continue;
            }

            foreach ($invoice->invoiceItems as $ii) {
                $product = $ii->product;
                $wg = $product->warrantyGroup ?? null;
                $warranty = null;
                if ($wg) {
                    // Get the vehicle type from the user's car
                    $userVehicleType = $userCars->vehicle_type ?? null;

                    // Only include warranties that match the vehicle type
                    $matchingWarranties = $wg->warranties
                        ->where('vehicle_type', $userVehicleType)
                        ->map(fn($w) => [
                            'id' => $w->id,
                            'duration_months' => $w->duration_months,
                            'max_mileage_km' => $w->max_mileage_km,
                        ])
                        ->values() // reindex array keys
                        ->all();

                    $warranty = [
                        'id' => $wg->id,
                        'vehicle_type' => $wg->vehicle_type,
                        'condition_note' => $wg->condition_note,
                        'warranties' => $matchingWarranties
                    ];
                }

                $purchaseDate = $ii->created_at ? Carbon::parse($ii->created_at) : null;

                // Calculate warranty validity if you want (use first warranty as example)
                $durationMonths = $warranty['warranties'][0]['duration_months'] ?? 0;
                if ($purchaseDate) {
                    $endDate = (clone $purchaseDate)->addMonths($durationMonths);
                    $remainingDays = Carbon::now()->diffInDays($endDate, false);
                    $isValid = $remainingDays >= 0;
                } else {
                    $endDate = null;
                    $remainingDays = null;
                    $isValid = false;
                }

                // Add validity info inside warranty
                if ($warranty) {
                    $warranty['start_date'] = $purchaseDate ? $purchaseDate->toDateString() : null;
                    $warranty['end_date'] = $endDate ? $endDate->toDateString() : null;
                    $warranty['remaining_days'] = $remainingDays !== null && $remainingDays >= 0 ? $remainingDays : null;
                    $warranty['is_valid'] = $isValid;
                }

                $results[] = [
                    'booking_id'       => $booking->id,
                    'license_plate'    => $userCar->license_plate ?? null,
                    'vehicle_type'     => $userCars->vehicle_type ?? null,
                    'invoice_id'       => $invoice->id,
                    'invoice_number'   => $invoice->invoice_number ?? null,
                    'invoice_date'     => $invoice->invoice_date ?? ($invoice->created_at ?? null),
                    'invoice_item_id'  => $ii->id,
                    'product_id'       => $product->id ?? null,
                    'product_name'     => $product->description ?? $product->name ?? null,
                    'warranty_group'   => $wg->id ?? null,
                    'warranty' => $warranty,
                ];
            }
        }

        // After building $results, filter & pick best
        $validWarranties = collect($results)
            ->filter(fn($r) => $r['warranty'] && $r['warranty']['is_valid'])
            ->sortByDesc(fn($r) => $r['warranty']['end_date'])
            ->values();

        if ($validWarranties->isNotEmpty()) {
            $results = [$validWarranties->first()];
        } else {
            // No valid warranties, show latest by invoice_date
            $results = collect($results)
                ->sortByDesc(fn($r) => $r['invoice_date'])
                ->take(1)
                ->values();
        }

        return response()->json($results);


        if (empty($results)) {
            return response()->json([
                'message' => 'No invoice items/products with warranty found for this plate'
            ], 404);
        }

        return response()->json($results);
    }
}
