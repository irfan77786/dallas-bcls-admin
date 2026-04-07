<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'payment_method', 'payment_status',
        'transaction_id', 'amount',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
