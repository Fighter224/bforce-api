<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoice';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'id',
        'invoice_number',
        'invoice_date',
        'total_amount',
        'discount',
        'payment_method',
        'user_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function InvoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
