<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    // Explicitly set the table name
    protected $table = 'feedbacks';

    // Define the fields that are mass-assignable
    protected $fillable = ['employee_id', 'type', 'description'];

    // Define the relationship to Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
