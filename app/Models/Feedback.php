<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    // Project DB uses singular table name.
    protected $table = 'feedback';

    // Define the fields that are mass-assignable
    protected $fillable = ['employee_id', 'type', 'description'];

    // Define the relationship to Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
