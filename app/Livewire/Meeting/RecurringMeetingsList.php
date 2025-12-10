<?php

namespace App\Livewire\Meeting;

use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Meeting;
use App\Models\RecurringMeeting;
use Illuminate\Support\Facades\Auth;

class RecurringMeetingsList extends Component
{
    public $recurringMeetings;
    public function mount()
    {
        $this->loadRecurringMeetings();
    }

    #[On('recurringMeetingsTabActivated')]
    public function loadRecurringMeetings()
    {
        // Get all parent recurring meeting series created by the user
        $recurringSeries = RecurringMeeting::whereHas('meetings', function ($query) {
            $query->where('user_id', Auth::id());
        })
        ->with(['meetings.room']) // Eager load children meetings and their rooms
        ->get();

        // The view expects a collection where each item has topic, room, etc.
        // The current $recurringSeries doesn't have that. So, we transform the collection.
        $this->recurringMeetings = $recurringSeries->map(function ($series) {
            // Get the first child meeting to extract common properties
            $firstMeeting = $series->meetings->first();
            if (!$firstMeeting) {
                return null; // Skip if there are no associated meetings for some reason
            }

            // Dynamically add properties to the series object to match the view's expectations
            $series->topic = $firstMeeting->topic;
            $series->room = $firstMeeting->room;
            $series->children = $series->meetings->sortBy('start_time'); // Pass sorted children meetings
            $series->recurring_type = $series->frequency; // Align with view's property name
            $series->recurring_end_date = $series->ends_at; // Align with view's property name

            return $series;
        })->filter(); // filter() will remove any nulls from the collection
    }

    #[On('confirmMeeting')]
    public function confirmMeeting($meetingId)
    {
        $meeting = Meeting::find($meetingId);
        if ($meeting && ($meeting->user_id === Auth::id() || ($meeting->parent && $meeting->parent->user_id === Auth::id()))) {
            $meeting->update(['confirmation_status' => 'confirmed']);
            $this->loadRecurringMeetings();
        }
    }

    #[On('cancelMeeting')]
    public function cancelMeeting($meetingId)
    {
        $meeting = Meeting::find($meetingId);
        if ($meeting && ($meeting->user_id === Auth::id() || ($meeting->parent && $meeting->parent->user && $meeting->parent->user->id === Auth::id()))) {
            $meeting->update(['status' => 'cancelled']);
            $this->loadRecurringMeetings();
        }
    }

    public function render()
    {
        return view('livewire.meeting.recurring-meetings-list-view');
    }
}
