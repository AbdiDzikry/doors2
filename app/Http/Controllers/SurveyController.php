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
        $averageScore = \App\Models\SurveyResponse::avg('sus_score') ?? 0;
        $totalResponses = \App\Models\SurveyResponse::count();

        return view('survey.index', compact('responses', 'averageScore', 'totalResponses'));
    }

    public function create()
    {
        return view('survey.create');
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'q1' => 'required|integer|min:1|max:5',
            'q2' => 'required|integer|min:1|max:5',
            'q3' => 'required|integer|min:1|max:5',
            'q4' => 'required|integer|min:1|max:5',
            'q5' => 'required|integer|min:1|max:5',
            'q6' => 'required|integer|min:1|max:5',
            'q7' => 'required|integer|min:1|max:5',
            'q8' => 'required|integer|min:1|max:5',
            'q9' => 'required|integer|min:1|max:5',
            'q10' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string|max:1000',
        ]);

        // Calculate SUS Score
        // Odd items (1, 3, 5, 7, 9): Score - 1
        $oddSum = ($validated['q1'] - 1) + ($validated['q3'] - 1) + ($validated['q5'] - 1) + ($validated['q7'] - 1) + ($validated['q9'] - 1);
        
        // Even items (2, 4, 6, 8, 10): 5 - Score
        $evenSum = (5 - $validated['q2']) + (5 - $validated['q4']) + (5 - $validated['q6']) + (5 - $validated['q8']) + (5 - $validated['q10']);
        
        // Total Score * 2.5
        $susScore = ($oddSum + $evenSum) * 2.5;

        \App\Models\SurveyResponse::create([
            'user_id' => auth()->id(),
            'q1' => $validated['q1'],
            'q2' => $validated['q2'],
            'q3' => $validated['q3'],
            'q4' => $validated['q4'],
            'q5' => $validated['q5'],
            'q6' => $validated['q6'],
            'q7' => $validated['q7'],
            'q8' => $validated['q8'],
            'q9' => $validated['q9'],
            'q10' => $validated['q10'],
            'sus_score' => $susScore,
            'comments' => $validated['comments'],
        ]);

        return redirect()->route('dashboard')->with('success', 'Thank you for your feedback! Your input helps us improve. 🚀');
    }
}
