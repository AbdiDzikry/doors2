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
            'participant_ids' => 'array',
            'participant_ids.*' => 'exists:meeting_participants,id',
        ]);

        if ($meeting->calculated_status === 'cancelled') {
            return back()->with('error', 'Meeting is cancelled. Cannot record attendance.');
        }

        // Permission Check: Only Admin, Organizer, or PIC can mark attendance
        $currentUser = auth()->user();
        $isOrganizer = $meeting->user_id === $currentUser->id;
        $isSuperAdmin = $currentUser->hasRole('Super Admin');
        $isPic = $meeting->meetingParticipants()
                    ->where('participant_id', $currentUser->id)
                    ->where('participant_type', User::class)
                    ->where('is_pic', true)
                    ->exists();

        if (!$isSuperAdmin && !$isOrganizer && !$isPic) {
             return back()->with('error', 'Unauthorized. Only the Organizer, Admin, or PIC can record attendance.');
        }

        // Time Window Check (Start Time to End Time + 30 mins)
        // Super Admin can bypass this check
        if (!$isSuperAdmin) {
            $now = now();
            $startTime = \Carbon\Carbon::parse($meeting->start_time);
            $endTimePlus30 = \Carbon\Carbon::parse($meeting->end_time)->addMinutes(30);

            if ($now->lt($startTime)) {
                return back()->with('error', 'Attendance cannot be recorded before the meeting starts.');
            }

            if ($now->gt($endTimePlus30)) {
                return back()->with('error', 'Attendance window closed (30 minutes after meeting ended).');
            }
        }


        // Retrieve submitted participant IDs (those checked as present)
        $presentIds = $request->input('participant_ids', []);

        // 1. Mark selected as attended
        if (!empty($presentIds)) {
            MeetingParticipant::whereIn('id', $presentIds)
                ->where('meeting_id', $meeting->id) // Security check: ensure they belong to this meeting
                ->whereNull('attended_at') // Only update if not already set (preserve original timestamp)
                ->update([
                    'attended_at' => now(),
                    'status' => 'attended'
                ]);
        }

        // 2. Mark unselected as NOT attended (Reset)
        // We get all participant IDs for this meeting that are NOT in the presentIds array
        MeetingParticipant::where('meeting_id', $meeting->id)
            ->whereNotIn('id', $presentIds)
            ->whereNotNull('attended_at') // Only update if currently marked as attended
            ->update([
                'attended_at' => null,
                'status' => 'scheduled' // Or 'absent', but 'scheduled' is default/pending
            ]);

        // Fix: If status logic relies on 'status' column, ensure 'scheduled' is the correct rollback state.
        // Assuming default is null or 'scheduled'.

        return back()->with('success', "Attendance updated successfully.");
    }
}
