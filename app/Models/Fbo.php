<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fbo extends Model
{
    protected $fillable = [
        'name',
        'airport_code',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'zip',
        'phone',
        'notes',
        'country',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function displayLabel(): string
    {
        $city = trim((string) $this->city);

        return $city !== '' ? $this->name.' ('.$city.')' : (string) $this->name;
    }
}
