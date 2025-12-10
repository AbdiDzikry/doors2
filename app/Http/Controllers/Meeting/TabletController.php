<?php

namespace App\Http\Controllers\Meeting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Meeting;
use App\Models\MeetingParticipant; // Import MeetingParticipant
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; // Import Auth
use App\Events\MeetingStatusUpdated; // Import MeetingStatusUpdated event

class TabletController extends Controller
{
    public function showRoom(Room $room)
    {
        $currentMeeting = $room->meetings()
                               ->where('start_time', '<=', Carbon::now())
                               ->where('end_time', '>=', Carbon::now())
                               ->with('organizer', 'meetingParticipants.participant') // Eager load the organizer and participants
                               ->first();

        return view('tablet.room-display', compact('room', 'currentMeeting'));
    }

    public function bookNow(Request $request, Room $room)
    {
        $request->validate([
            'topic' => 'required|string|max:255',
            'duration' => 'required|integer|min:1', // Duration in minutes
        ]);

        // Ensure no overlapping meetings
        $startTime = Carbon::now();
        $endTime = $startTime->copy()->addMinutes($request->duration);

        $overlappingMeeting = $room->meetings()
                                   ->where(function ($query) use ($startTime, $endTime) {
                                       $query->whereBetween('start_time', [$startTime, $endTime])
                                             ->orWhereBetween('end_time', [$startTime, $endTime])
                                             ->orWhere(function ($query) use ($startTime, $endTime) {
                                                 $query->where('start_time', '<', $startTime)
                                                       ->where('end_time', '>', $endTime);
                                             });
                                   })
                                   ->first();

        if ($overlappingMeeting) {
            return response()->json(['message' => 'Room is already booked for this time slot.'], 409);
        }

        $meeting = Meeting::create([
            'room_id' => $room->id,
            'user_id' => Auth::id(), // Assuming a user is authenticated via API token or session
            'topic' => $request->topic,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'scheduled', // Or 'active' if it starts immediately
            'meeting_type' => 'on-the-spot',
        ]);

        // Dispatch event to update tablet display
        MeetingStatusUpdated::dispatch($room, $meeting->load('organizer'));

        return response()->json(['message' => 'Meeting booked successfully!', 'meeting' => $meeting->load('organizer')], 201);
    }

    public function checkIn(Request $request, Meeting $meeting)
    {
        $request->validate([
            'participant_id' => 'required|integer',
            'participant_type' => 'required|string', // e.g., 'App\Models\User' or 'App\Models\ExternalParticipant'
        ]);

        $meetingParticipant = MeetingParticipant::where('meeting_id', $meeting->id)
                                                ->where('participant_id', $request->participant_id)
                                                ->where('participant_type', $request->participant_type)
                                                ->first();

        if (!$meetingParticipant) {
            return response()->json(['message' => 'Participant not found for this meeting.'], 404);
        }

        $meetingParticipant->status = 'attended';
        $meetingParticipant->checked_in_at = Carbon::now(); // Assuming you have this column
        $meetingParticipant->save();

        // Optionally dispatch an event for real-time updates on a dashboard
        // ParticipantCheckedIn::dispatch($meetingParticipant);

        return response()->json(['message' => 'Participant checked in successfully!'], 200);
    }
}
