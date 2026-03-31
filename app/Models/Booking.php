<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'vehicle_id', 'pickup_location', 'dropoff_location',
        'pickup_date', 'pickup_time', 'total_price', 'payment_status','note'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function vehicle() {
        return $this->belongsTo(Vehicle::class);
    }

    public function passengers() {
        return $this->hasMany(Passenger::class);
    }

    public function payments() {
        return $this->hasMany(Payment::class);
    }
    public function booker(){
        return $this->belongsTo(Booker::class);
    }
    
    public function flightDetails(){
        return $this->hasManyThrough(FlightDetail::class, Passenger::class, 'booking_id', 'passenger_id', 'id', 'id');
    }
    
    
    public function returnService()
    {
        return $this->belongsTo(ReturnService::class, 'return_service_id');
    }
    
    public function breakdown()
{
    return $this->hasOne(BookingBreakdown::class, 'booking_id', 'id');
}
}
