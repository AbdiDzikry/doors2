<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function index()
    {
        // Enforce Superadmin access
        if (!auth()->user()->hasRole('superadmin')) {
            abort(403, 'Unauthorized access.');
        }

        $responses = \App\Models\SurveyResponse::with('user')->latest()->paginate(10);
        
        // Calculate stats
        $averageRating = \App\Models\SurveyResponse::avg('rating');
        $totalResponses = \App\Models\SurveyResponse::count();

        return view('survey.index', compact('responses', 'averageRating', 'totalResponses'));
    }

    public function create()
    {
        return view('survey.create');
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string|max:1000',
        ]);

        \App\Models\SurveyResponse::create([
            'user_id' => auth()->id(),
            'rating' => $validated['rating'],
            'comments' => $validated['comments'],
        ]);

        return redirect()->route('dashboard')->with('success', 'Thank you for your feedback! 🌟');
    }
}
