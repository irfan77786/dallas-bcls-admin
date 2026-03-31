<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;  // Assuming you have a Feedback model
use App\Models\Employee;  // Assuming you have an Employee model

class FeedbackController extends Controller
{
    // Display the list of feedbacks (complaints and suggestions)
    public function index()
    {
        $feedbacks = Feedback::with('employee')->latest()->get();
        return view('pages.feedback.index', compact('feedbacks'));
    }

    // Show the feedback form to submit a complaint or suggestion
    public function create()
    {
        // Get all employees (assuming feedback is related to employees)
        $employees = Employee::all();
        return view('pages.feedback.create', compact('employees'));
    }

    // Store the feedback (either complaint or suggestion)
    public function store(Request $request)
    {
        // Validate the form data
        $request->validate([
            'employee_id' => 'required|exists:employees,id', // Ensure valid employee
            'type' => 'required|in:complaint,suggestion', // Either complaint or suggestion
            'description' => 'required|string|max:500', // Description of the feedback
        ]);

        // Create a new feedback record
        Feedback::create([
            'employee_id' => $request->employee_id,
            'type' => $request->type,
            'description' => $request->description,
        ]);

        // Redirect with a success message
        return redirect()->route('feedback.create')->with('success', 'Feedback submitted successfully');
    }
}
