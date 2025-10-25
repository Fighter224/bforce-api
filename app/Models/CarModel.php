<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // Added
use Illuminate\Support\Str;

class CarModel extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'car_models';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'car_brand_id', // Foreign key
        'name',         // Add other fillable fields as necessary
    ];

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });
    }

    /**
     * Get the brand that owns the model.
     */
    public function carBrand()
    {
        return $this->belongsTo(CarBrand::class, 'car_brand_id');
    }

    /**
     * Get the user cars associated with this model.
     */
    public function userCars()
    {
        return $this->hasMany(UserCar::class);
    }

}
