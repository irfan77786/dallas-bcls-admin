<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Passenger extends Model
{
    // If you're not using HasFactory, you can skip it
    // use Illuminate\Database\Eloquent\Factories\HasFactory;
    // use HasFactory;

    protected $table = 'passengers'; // optional if table name is plural of model

    protected $fillable = [
        'booking_id',
        'full_name',
        'email',
        'phone',
        'seat_category',
        'seat_price',
        'seat_quantity',
        'total',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
    public function flightDetail()
{
    return $this->hasOne(FlightDetail::class);
}
}
