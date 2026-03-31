<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Employee;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::with('employee')->latest()->get();
        return view('pages.reviews.index', compact('reviews'));
    }

    public function create()
    {
        $employees = Employee::all();
        return view('pages.reviews.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        Review::create([
            'employee_id' => $request->employee_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()->route('review.thank_you');
    }
}
