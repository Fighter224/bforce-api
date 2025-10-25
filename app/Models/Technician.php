<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Technician extends Model
{
    use HasFactory;

    protected $table = 'users';
    protected $fillable = [
        'name',
        'email',
        'role',
        'work_status',
        'password',
        'phone',
        'ic',
        'otp',
        'otp_expires_at',
        'two_fa_enabled',
        'remember_token',
        'total_inspections',
        'total_battery_installations',
        'terms_accepted',
        'terms_accepted_at',
        'profile_image',
        'two_factor_code',
        'two_factor_expires_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $keyType = 'string';   // UUID is a string
    public $incrementing = false;    // No auto increment


    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_expires_at' => 'datetime',
    ];

}
