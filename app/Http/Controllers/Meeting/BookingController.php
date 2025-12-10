<?php

namespace App\Http\Controllers\Meeting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\PriorityGuest;
use App\Models\User;
use App\Models\PantryItem;
use App\Models\ExternalParticipant;
use App\Models\Configuration; // Import the Configuration model

class BookingController extends RoomReservationController
{
    public function create(Request $request)
    {
        $rooms = Room::all();
        $priorityGuests = PriorityGuest::all();
        $users = User::all();
        $pantryItems = PantryItem::all();
        $externalParticipants = ExternalParticipant::all();
        $selectedRoomId = $request->query('room_id');
        $startTime = $request->query('start_time');
        $endTime = $request->query('end_time');

        // Retrieve default meeting duration from configurations
        $defaultMeetingDuration = Configuration::where('key', 'default_meeting_duration')->first();
        $defaultMeetingDurationValue = $defaultMeetingDuration ? (int)$defaultMeetingDuration->value : 60; // Default to 60 if not found

        $selectedRoom = Room::find($selectedRoomId);
        $current_meeting = null;
        if ($selectedRoom) {
            $now = now();
            $current_meeting = $selectedRoom->meetings()
                ->where('start_time', '<=', $now)
                ->where('end_time', '>=', $now)
                ->where('status', '!=', 'cancelled')
                ->first();
        }


        return view('meetings.booking.create', compact('rooms', 'priorityGuests', 'users', 'pantryItems', 'externalParticipants', 'selectedRoomId', 'startTime', 'endTime', 'defaultMeetingDurationValue', 'current_meeting', 'selectedRoom'));
    }
}
