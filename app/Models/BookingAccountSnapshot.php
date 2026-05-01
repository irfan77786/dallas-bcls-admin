<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingAccountSnapshot extends Model
{
    protected $fillable = [
        'booking_id',
        'account_id',
        'account_company_number',
        'account_company_name',
        'account_company_email',
        'account_company_phone',
        'account_company_address',
        'account_billing_name',
        'account_billing_email',
        'account_billing_phone',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
