<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TourController extends Controller
{
    /**
     * Mark the onboarding tour as seen for the current user.
     */
    public function markAsSeen(Request $request)
    {
        $user = auth()->user();

        if ($user) {
            $user->update(['has_seen_tour' => true]);
        }

        return response()->json(['success' => true]);
    }
}
