<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarrantyGroup extends Model
{
    use HasFactory;
    protected $table = 'warranty_groups';

    protected $primaryKey = 'id';

    public $incrementing = false; // if using UUIDs

    protected $keyType = 'string'; // if using UUIDs

    protected $fillable = [
        'id',
        'battery_brand_id',
        'group_name',
        'battery_type',
        'notes'
    ];

    public function warranties()
    {
        return $this->hasMany(Warranty::class, 'warranty_group_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
