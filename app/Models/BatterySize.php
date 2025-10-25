<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasUuids; // Assuming you have a UUID trait

class BatterySize extends Model
{
    use HasFactory; // Added HasUuids

    protected $table = 'battery_sizes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'battery_type_id',
    ];

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Get the battery type that this size belongs to.
     */
    public function batteryType(): BelongsTo
    {
        return $this->belongsTo(BatteryType::class, 'battery_type_id');
    }

    /**
     * Get the products associated with this battery size.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'battery_size_id');
    }

    /**
     * Get the car compatibility records for this battery size.
     */
    public function carCompatibleBatteries(): HasMany
    {
        // Note: The foreign key on car_compatible_battery table is 'battery_sizes'
        return $this->hasMany(CarCompatibleBattery::class, 'battery_sizes');
    }
}
