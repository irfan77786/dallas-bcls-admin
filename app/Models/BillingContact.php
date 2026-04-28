<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingContact extends Model
{
    protected $fillable = [
        'account_id',
        'name',
        'email',
        'phone',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
