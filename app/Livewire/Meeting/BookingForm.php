<?php

namespace App\Livewire\Meeting;

use Livewire\Component;
use App\Models\Room;
use App\Models\PriorityGuest;
use App\Models\PantryItem;
use App\Models\User;
use App\Models\ExternalParticipant;
use App\Models\Meeting;
use App\Models\MeetingParticipant;
use App\Models\PantryOrder;
use App\Models\RecurringMeeting;
use App\Services\BookingService;
use App\Mail\MeetingInvitation;
use App\Services\IcsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request; // Although not directly used as a type-hinted parameter in Livewire methods, it's good to have for context if needed.

class BookingForm extends Component
{
    // Form properties
    public $selectedRoomId;
    public $room_id;
    public $topic;
    public $start_time;
    public $duration = 60; // Default duration
    public $priority_guest_id;
    public $recurring = false;
    public $frequency = 'daily';
    public $ends_at;

    // Data for dropdowns/lists
    public $rooms;
    public $priorityGuests;
    public $defaultMeetingDurationValue = 60; // Default value for duration select

    // Participants and Pantry Items
    public $internalParticipants = []; // Array of user IDs
    public $externalParticipants = []; // Array of external participant IDs
    public $pantryOrders = []; // Array of ['pantry_item_id' => id, 'quantity' => qty]

    public $current_meeting;
    public $selectedRoom;

    // Listeners for child components
    protected $listeners = [
        'internalParticipantsUpdated' => 'updateInternalParticipants',
        'externalParticipantsUpdated' => 'updateExternalParticipants',
        'pantryOrdersUpdated' => 'updatePantryOrders',
    ];
    public function mount($selectedRoomId = null)
    {
        $this->selectedRoomId = $selectedRoomId;
        $this->room_id = $selectedRoomId; // Pre-select room if provided

        $this->rooms = Room::all();
        $this->priorityGuests = PriorityGuest::all();
        
        // Set default start time based on business hours (7 AM to 6 PM)
        $currentTime = now();
        $minute = $currentTime->minute;
        $remainder = $minute % 15;
        if ($remainder !== 0) {
            $currentTime->addMinutes(15 - $remainder)->second(0);
        }

        $hour = (int) $currentTime->format('H');

        if ($hour < 7) {
            // If before 7 AM, set default to 7:00 AM today
            $currentTime->hour(7)->minute(0);
        } elseif ($hour >= 18) {
            // If 6 PM or later, set default to 7:00 AM tomorrow
            $currentTime->addDay()->hour(7)->minute(0);
        }

        $this->start_time = $currentTime->format('Y-m-d\TH:i');
        $this->ends_at = now()->addDays(7)->format('Y-m-d'); // Default end date for recurring

        // Fetch selected room and current meeting logic
        $this->selectedRoom = Room::find($this->selectedRoomId);
        $this->current_meeting = null;
        if ($this->selectedRoom) {
            $now = now();
            $this->current_meeting = $this->selectedRoom->meetings()
                ->where('start_time', '<=', $now)
                ->where('end_time', '>=', $now)
                ->where('status', '!=', 'cancelled')
                ->first();
        }
    }

    public function render()
    {
        // $selectedRoom is now a public property, no need to re-fetch
        return view('livewire.meeting.booking-form', [
            // 'selectedRoom' => $this->selectedRoom, // No longer needed here
            'rooms' => $this->rooms,
            'priorityGuests' => $this->priorityGuests,
            'defaultMeetingDurationValue' => $this->defaultMeetingDurationValue,
        ]);
    }

    public function submitForm(BookingService $bookingService)
    {
        $this->validate([
            'room_id' => 'required|exists:rooms,id',
            'topic' => 'required|string|max:255',
            'start_time' => 'required|date',
            'duration' => 'required|integer|min:1',
            'priority_guest_id' => 'nullable|exists:priority_guests,id',
            'recurring' => 'nullable|boolean',
            'frequency' => 'required_if:recurring,true|string',
            'ends_at' => 'required_if:recurring,true|date|after:start_time',
        ]);

        $newStartTime = new \DateTime($this->start_time);
        $newEndTime = (clone $newStartTime)->add(new \DateInterval('PT' . $this->duration . 'M'));

        // Check if meeting ends after 6:00 PM
        if ($newEndTime->format('Hi') > '1800') {
            $this->addError('duration', 'The meeting cannot end after 6:00 PM.');
            return;
        }

        // Validate Pantry Item Stock
        foreach ($this->pantryOrders as $index => $order) {
            if (!empty($order['pantry_item_id']) && !empty($order['quantity'])) {
                $item = PantryItem::find($order['pantry_item_id']);
                if (!$item || $item->stock < $order['quantity']) {
                    $this->addError('pantryOrders.' . $index . '.quantity', 'Insufficient stock for ' . ($item ? $item->name : 'item') . '. Available: ' . ($item ? $item->stock : 0) . '.');
                    return;
                }
            }
        }

        try {
            // Prepare data for service
            $data = [
                'room_id' => $this->room_id,
                'topic' => $this->topic,
                'start_time' => $this->start_time,
                'duration' => $this->duration,
                'priority_guest_id' => $this->priority_guest_id,
                'recurring' => $this->recurring,
                'frequency' => $this->frequency,
                'ends_at' => $this->ends_at,
            ];

            $bookingService->createMeeting(
                $data,
                $this->internalParticipants,
                $this->externalParticipants,
                $this->pantryOrders
            );

            session()->flash('success', 'Meeting scheduled successfully!');
            return redirect()->route('meeting.meeting-lists.index');
        } catch (\Exception $e) {
            // Use Livewire's validation error system to show the message on the form
            $this->addError('room_id', $e->getMessage());
        }
    }

    // Listener methods to update participants and pantry orders from child components
    public function updateInternalParticipants($participants)
    {
        $this->internalParticipants = $participants;
    }

    public function updateExternalParticipants($participants)
    {
        $this->externalParticipants = $participants;
    }

    public function updatePantryOrders($orders)
    {
        $this->pantryOrders = $orders;
    }

    public function getRoomMeetingsProperty()
    {
        if (!$this->room_id) {
            return collect();
        }

        return Meeting::where('room_id', $this->room_id)
            ->where('start_time', '>=', now()->startOfDay())
            ->where('start_time', '<=', now()->endOfDay()) // Restrict to today only
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_time')
            ->take(10)
            ->with('user') // Eager load user for "Booked By"
            ->get();
    }
}
