<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'picture',
        'vehicle_type',
        'car_make',
        'car_model',
        'year',
        'color',
        'capacity',
        'plate_number',
        'vin',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'capacity' => 'integer',
    ];

    public function pictureUrl(): ?string
    {
        if (! $this->picture) {
            return null;
        }

        return asset('storage/' . ltrim($this->picture, '/'));
    }

    public function vehicleLabel(): string
    {
        $parts = array_filter([
            $this->year,
            $this->car_make,
            $this->car_model,
            $this->color ? '(' . $this->color . ')' : null,
        ], fn ($v) => filled($v));

        return implode(' ', $parts);
    }
}
