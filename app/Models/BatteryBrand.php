<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BatteryBrand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'seq',
    ];

    protected $casts = [
        'seq' => 'integer',
    ];

    /**
     * Get the products for the battery brand.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'battery_brand_id');
    }
}
