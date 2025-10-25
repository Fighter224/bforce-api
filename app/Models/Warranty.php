<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warranty extends Model
{
    use HasFactory;
    protected $table = 'warranty';

    protected $fillable = [
        'id',
        'warranty_group_id',
        'vehicle_type',
        'duration_months',
        'max_mileage_km',
        'condition_note'
    ];

    public function warrantyGroup()
    {
        return $this->belongsTo(WarrantyGroup::class, 'warranty_group_id', 'id');
    }
}
