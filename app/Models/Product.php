<?php

namespace App\Models;

use App\Models\BatteryBrand;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $table = 'products';
    public $incrementing = false;

    public $timestamps = true;

    protected $keyType = 'string';

    protected $fillable = [
        'id', 'bukku_id', 'description', 'cost_price', 'sale_price',
        'price', 'product_category_id', 'battery_brand_id','battery_size_id',
        'battery_brand_series_id', 'voltage', 'capacity',
        'stock', 'image', 'created_at', 'updated_at'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid(); // generate UUID automatically
            }
        });
    }

    protected $casts = [
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(BatteryBrand::class, 'battery_brand_id');
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class, 'product_id', 'id');
    }




}
