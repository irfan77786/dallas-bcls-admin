<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReturnService extends Model
{
    use HasFactory;

    protected $table = 'return_service';
    protected $fillable = [
        'booking_id',
        'vehicle_id',
        'pickup_location',
        'dropoff_location',
        'pickup_date',
        'pickup_time',
    ];

   
    public function booking()
    {
        return $this->hasOne(Booking::class, 'return_service_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
