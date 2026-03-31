<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FlightDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'passenger_id',
        'pickup_flight_details',
        'flight_number',
        'meet_option',
        'no_flight_info',
        'inside_pickup_fee',
    ];

    public function passenger()
    {
        return $this->belongsTo(Passenger::class);
    }
}