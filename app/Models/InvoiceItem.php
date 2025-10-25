<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
    }


    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warranty()
    {
        return $this->hasOne(Warranty::class, 'product_id', 'product_id');
    }
}
