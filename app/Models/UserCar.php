<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCar extends Model
{
    use HasFactory;

    protected $table = 'user_cars';

    protected $fillable = [
        'user_id',
        'car_brand_id',
        'car_model_id',
        'vehicle_type',
        'license_plate',
    ];

    // Relationships

    public function user()
    {
        return $this->belongsTo(Technician::class,'user_id', 'id');
    }

    public function brand()
    {
        return $this->belongsTo(CarBrand::class, 'car_brand_id');
    }

    public function model()
    {
        return $this->belongsTo(CarModel::class, 'car_model_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'user_car_id');
    }
}
