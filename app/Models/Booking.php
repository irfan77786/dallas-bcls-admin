<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $casts = [
        'from_admin_reservation' => 'boolean',
        'stop_locations' => 'array',
    ];

    protected $fillable = [
        'user_id',
        'booking_id',
        'booker_id',
        'vehicle_id',
        'pickup_location',
        'dropoff_location',
        'stop_locations',
        'pickup_date',
        'pickup_time',
        'total_price',
        'buffer_amount',
        'payment_status',
        'return_service_id',
        'note',
        'child_seat_type',
        'child_seat_quantity',
        'child_seat_fee',
        'pax_count',
        'luggage_count',
        'service_option',
        'from_admin_reservation',
        'stripe_customer_id',
        'stripe_payment_method_id',
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

    public function accountSnapshot()
    {
        return $this->hasOne(BookingAccountSnapshot::class);
    }
}
