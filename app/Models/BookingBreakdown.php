<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingBreakdown extends Model
{
    protected $table = 'booking_breakdwon'; // Matches your provided table name

    protected $fillable = [
        'booking_id',
        'base_fare',
        'per_km_rate',
        'total_kms',
        'hourly_fare',
        'total_hours',
        'return_base_fare',
        'return_per_km_rate',
        'return_total_kms',
    ];

   
    public function booking()
{
    return $this->belongsTo(Booking::class, 'booking_id', 'id');
}
}
