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
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'is_booking_for_others',
        'booker_first_name',
        'booker_last_name',
        'booker_email',
        'booker_number',
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
