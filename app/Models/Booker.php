<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booker extends Model
{
    use HasFactory;
    protected $table = 'booker';
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
