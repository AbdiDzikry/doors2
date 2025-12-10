<?php

namespace App\Http\Controllers\Meeting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Meeting;
use App\Models\MeetingParticipant;
use App\Models\PantryOrder;
use App\Models\RecurringMeeting;
use App\Http\Requests\MeetingRequest;
use App\Mail\MeetingInvitation;
use App\Services\IcsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Events\MeetingStatusUpdated; // Import the event


class RoomReservationController extends Controller
{

    public function index(Request $request)
    {
        $now = now();
        $query = Room::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('facilities', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'in_use') {
                $query->whereHas('meetings', function ($q) use ($now) {
                    $q->where('start_time', '<=', $now)
                      ->where('end_time', '>=', $now)
                      ->where('status', '!=', 'cancelled');
                });
            } elseif ($status === 'available') {
                $query->whereDoesntHave('meetings', function ($q) use ($now) {
                    $q->where('start_time', '<=', $now)
                      ->where('end_time', '>=', $now)
                      ->where('status', '!=', 'cancelled');
                })->where('status', 'available');
            } else {
                $query->where('status', $status);
            }
        }

        $rooms = $query->with(['meetings' => function ($q) use ($now) {
            $q->where('start_time', '<=', $now)
              ->where('end_time', '>=', $now)
              ->where('status', '!=', 'cancelled');
        }])->get();

        $rooms->each(function ($room) {
            $room->is_in_use = $room->meetings->isNotEmpty();
            $room->current_meeting = $room->meetings->first();
        });

        return view('meetings.reservation.index', compact('rooms'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $selectedRoomId = $request->query('room_id');
        return view('meetings.booking.create', compact('selectedRoomId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Basic validation, since MeetingRequest might not see all the livewire data.
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'topic' => 'required|string|max:255',
            'start_time' => 'required|date',
            'duration' => 'required|integer|min:1',
            'priority_guest_id' => 'nullable|exists:priority_guests,id',
            'recurring' => 'nullable|string',
            'frequency' => 'required_if:recurring,on|string',
            'ends_at' => 'required_if:recurring,on|date|after:start_time',
        ]);

        DB::beginTransaction();
        try {
            $meetingData = $request->only([
                'room_id',
                'topic',
                'start_time',
                'priority_guest_id',
            ]);
            // Determine meeting_type based on recurring checkbox
            $meetingData['meeting_type'] = $request->has('recurring') ? 'recurring' : 'non-recurring';

            $meetingData['end_time'] = (new \DateTime($request->start_time))->add(new \DateInterval('PT' . $request->duration . 'M'))->format('Y-m-d H:i:s');
            $meetingData['user_id'] = auth()->id();
            $meetingData['status'] = 'scheduled';

            if ($meetingData['meeting_type'] === 'recurring') {
                $recurringMeeting = RecurringMeeting::create([
                    'frequency' => $request->frequency,
                    'ends_at' => $request->ends_at,
                ]);
                $meetingData['recurring_meeting_id'] = $recurringMeeting->id;

                // Logic to create multiple meeting entries based on recurrence
                $startDate = new \DateTime($request->start_time);
                $endDate = new \DateTime($request->ends_at);
                $interval = new \DateInterval($this->getRecurringInterval($request->frequency));
                $period = new \DatePeriod($startDate, $interval, $endDate);

                foreach ($period as $date) {
                    $newStartTime = $date;
                    $newEndTime = (clone $newStartTime)->add(new \DateInterval('PT' . $request->duration . 'M'));

                    $meeting = Meeting::create(array_merge($meetingData, [
                        'start_time' => $newStartTime->format('Y-m-d H:i:s'),
                        'end_time' => $newEndTime->format('Y-m-d H:i:s'),
                    ]));
                    $this->attachParticipantsAndPantryOrders($meeting, $request);
                    $this->sendMeetingInvitation($meeting); // Re-enabled email sending
                    MeetingStatusUpdated::dispatch($meeting->room, $meeting); // Dispatch event
                }
            } else {
                $meeting = Meeting::create($meetingData);
                $this->sendMeetingInvitation($meeting); // Re-enabled email sending
                MeetingStatusUpdated::dispatch($meeting->room, $meeting); // Dispatch event
            }

            DB::commit();
            return redirect()->route('meeting.meeting-lists.index')->with('success', 'Meeting scheduled successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to schedule meeting: ' . $e->getMessage()]);
        }
    }

    private function attachParticipantsAndPantryOrders(Meeting $meeting, Request $request)
    {
        // Attach Internal Participants
        if ($request->has('internal_participants')) {
            foreach ($request->internal_participants as $userId) {
                MeetingParticipant::create([
                    'meeting_id' => $meeting->id,
                    'participant_id' => $userId, // Changed from user_id to participant_id
                    'participant_type' => 'App\Models\User', // Use full class name for polymorphic type
                ]);
            }
        }

        // Attach External Participants
        if ($request->has('external_participants')) {
            foreach ($request->external_participants as $participantId) {
                MeetingParticipant::create([
                    'meeting_id' => $meeting->id,
                    'participant_id' => $participantId, // Changed from external_participant_id to participant_id
                    'participant_type' => 'App\Models\ExternalParticipant', // Use full class name for polymorphic type
                ]);
            }
        }

        // Create Pantry Orders
        if ($request->has('pantry_orders')) {
            foreach ($request->pantry_orders as $order) {
                if (!empty($order['pantry_item_id']) && !empty($order['quantity'])) {
                    PantryOrder::create([
                        'meeting_id' => $meeting->id,
                        'pantry_item_id' => $order['pantry_item_id'],
                        'quantity' => $order['quantity'],
                        'status' => 'pending',
                    ]);
                }
            }
        }
    }

    private function sendMeetingInvitation(Meeting $meeting)
    {
        $icsService = new IcsService();
        $icsContent = $icsService->generateIcsFile($meeting);

        // Get all participants (internal and external)
        $participants = collect();
        if ($meeting->user) {
            $participants->push($meeting->user);
        }
        $participants = $participants->merge($meeting->meetingParticipants->map(function ($mp) {
            return $mp->user ?? $mp->externalParticipant;
        })->filter());

        foreach ($participants as $participant) {
            if ($participant->email) {
                Mail::to($participant->email)->send(new MeetingInvitation($meeting, $icsContent));
            }
        }
    }

    private function getRecurringInterval(string $pattern): string
    {
        return match ($pattern) {
            'daily' => 'P1D',
            'weekly' => 'P1W',
            'monthly' => 'P1M',
            default => 'P1D',
        };
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
