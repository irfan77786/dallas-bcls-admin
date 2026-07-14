<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationRoutingDraft extends Model
{
    protected $fillable = [
        'user_id',
        'routing_information',
    ];

    protected $casts = [
        'routing_information' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
