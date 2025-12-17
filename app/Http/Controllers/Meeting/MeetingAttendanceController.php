<?php

namespace App\Http\Controllers\Meeting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Meeting;
use App\Models\User;
use App\Models\MeetingParticipant;

class MeetingAttendanceController extends Controller
{
    public function store(Request $request, Meeting $meeting)
    {
        $request->validate([
            'npk' => 'required|string',
        ]);

        if ($meeting->calculated_status === 'cancelled') {
            return back()->with('error', 'Meeting is cancelled. Cannot record attendance.');
        }

        $npk = $request->input('npk');

        // Find user by NPK
        $user = User::where('npk', $npk)->first();

        if (!$user) {
            return back()->with('error', 'NPK not found.');
        }

        // Check if user is a participant
        $participant = MeetingParticipant::where('meeting_id', $meeting->id)
            ->where('participant_id', $user->id)
            ->where('participant_type', User::class)
            ->first();

        if (!$participant) {
            return back()->with('error', 'You are not listed as a participant for this meeting.');
        }

        if ($participant->attended_at) {
            return back()->with('info', 'You have already marked your attendance.');
        }

        $participant->update([
            'attended_at' => now(),
            'status' => 'attended',
        ]);

        return back()->with('success', "Welcome, {$user->name}! Attendance recorded.");
    }
}
