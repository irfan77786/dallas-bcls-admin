<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'rating',
        'comment',
        'email',
        'phone',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
