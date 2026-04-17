<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;
    protected $fillable = [
        'sort_order',
        'vehicle_name',
        'vehicle_image',
        'vehicle_code',
        'number_of_passengers',
        'luggage_capacity',
        'active',
        'greeting_fee',
        'description',
        'slug',
        'base_fare',
        'base_hourly_fare',
        'per_km_rate'
    ];

    
 
    public function carSeat()
    {
        return $this->hasMany(CarSeat::class);
    }

    // public function getBreakDownAttribute()
    // {
    //     return $this->rateVehicle->breakDown ?? null;
    // }
}
