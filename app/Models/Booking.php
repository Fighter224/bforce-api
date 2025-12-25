<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'orderID',
        'user_id',
        'user_car_id',
        'invoice_id',
        'technician_id',
        'service_type',
        'preferred_date',
        'preferred_time',
        'location',
        'latitude',
        'longitude',
        'notes',
        'warranty_terms_agreed',
        'status',
        'alternator_image',
        'starter_image',
        'odometer_image',
        'plate_image',
        'battery_image'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function car()
    {
        // 👇 Notice: user_car_id references id in user_cars table
        return $this->belongsTo(UserCar::class, 'user_car_id', 'id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
    }

    public function technician()
    {
        return $this->belongsTo(Technician::class, 'technician_id', 'id');
    }

    // Static method to get available technicians - FIXED untuk table user_bk
    public static function getAvailableTechnicians()
    {
        return \DB::table('user_bk')  // Guna table sebenar user_bk
            ->where('role', 'technician')
            ->leftJoin('technician_outlets', 'user_bk.id', '=', 'technician_outlets.technician_id')
            ->leftJoin('outlets', 'technician_outlets.outlet_id', '=', 'outlets.id')
            ->leftJoin('user_profiles', 'user_bk.id', '=', 'user_profiles.user_id')
            ->select(
                'user_bk.*',
                'outlets.name as outlet_name',
                'outlets.city as outlet_city',
                'outlets.state as outlet_state',
                'user_profiles.profile_image',
                'technician_outlets.status as outlet_status'
            )
            ->orderBy('user_bk.name')
            ->get()
            ->map(function ($technician) {
                // Add calculated fields
                $technician->availability_status = self::getTechnicianAvailability($technician->id);
                $technician->current_bookings = self::getTechnicianActiveBookings($technician->id);
                $technician->rating = self::getDefaultRating();
                $technician->specialty = self::getDefaultSpecialty();
                $technician->is_busy = $technician->current_bookings > 0;
                return $technician;
            });
    }

    // Method to check technician availability
    public static function getTechnicianAvailability($technicianId)
    {
        $activeBookings = self::where('technician_id', $technicianId)
            ->whereIn('status', ['pending', 'in_progress', 'assigned'])
            ->count();

        return $activeBookings == 0 ? 'available' : 'busy';
    }

    // Method to get technician's current bookings count
    public static function getTechnicianActiveBookings($technicianId)
    {
        return self::where('technician_id', $technicianId)
            ->whereIn('status', ['pending', 'in_progress', 'assigned'])
            ->count();
    }

    // Helper methods untuk default values
    private static function getDefaultRating()
    {
        return rand(40, 50) / 10; // Random rating between 4.0 - 5.0
    }

    private static function getDefaultSpecialty()
    {
        $specialties = [
            'Battery Specialist',
            'Electrical Expert',
            'General Technician',
            'Diagnostic Expert',
            'Installation Specialist'
        ];

        return $specialties[array_rand($specialties)];
    }
}
