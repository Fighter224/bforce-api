<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Outlet extends Model
{
    use HasFactory;

    public $incrementing = false; // because it's not auto-increment
    protected $keyType = 'string'; // because it's a UUID

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid(); // generate UUID automatically
            }
        });
    }

    protected $fillable = [
        'name', 'address1', 'address2', 'city', 'state', 'postcode', 'contact', 'code', 'outlet_name','bukku_id','channel_id',
    ];

    public function technicianOutlets()
    {
        return $this->hasMany(TechnicianOutlet::class);
    }

    public function technicians()
    {
        return $this->belongsToMany(User::class, 'technician_outlets', 'outlet_id', 'technician_id');
    }
}
