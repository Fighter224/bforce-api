<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicianOutlet extends Model
{
    use HasFactory;

    protected $table = 'technician_outlets';

    protected $fillable = [
        'technician_id', 'outlet_id', 'status'
    ];

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class, 'outlet_id');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

}
