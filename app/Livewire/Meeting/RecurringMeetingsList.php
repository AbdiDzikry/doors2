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
    public $filter = 'all'; // Default to all
    public $startDate;
    public $endDate;

    public function mount()
    {
        $this->loadRecurringMeetings();
    }
    
    public function updatedFilter()
    {
        // Reset custom dates if switching away from custom
        if ($this->filter !== 'custom') {
            $this->startDate = null;
            $this->endDate = null;
        }
        $this->loadRecurringMeetings();
    }

    public function updatedStartDate() { $this->loadRecurringMeetings(); }
    public function updatedEndDate() { $this->loadRecurringMeetings(); }

    #[On('recurringMeetingsTabActivated')]
    public function loadRecurringMeetings()
    {
        $recurringSeries = RecurringMeeting::whereHas('meetings', function ($query) {
            $query->where('user_id', Auth::id());
        })
        ->with(['meetings.room'])
        ->get();

        // Calculate Date Range
        $effectiveStartDate = null;
        $effectiveEndDate = null;

        if ($this->filter === 'custom') {
             $effectiveStartDate = $this->startDate ? \Carbon\Carbon::parse($this->startDate)->startOfDay() : null;
             $effectiveEndDate = $this->endDate ? \Carbon\Carbon::parse($this->endDate)->endOfDay() : null;
        } else {
            switch ($this->filter) {
                case 'day':
                    $effectiveStartDate = now()->startOfDay();
                    $effectiveEndDate = now()->endOfDay();
                    break;
                case 'week':
                    $effectiveStartDate = now()->startOfWeek();
                    $effectiveEndDate = now()->endOfWeek();
                    break;
                case 'month':
                    $effectiveStartDate = now()->startOfMonth();
                    $effectiveEndDate = now()->endOfMonth();
                    break;
                case 'year':
                    $effectiveStartDate = now()->startOfYear();
                    $effectiveEndDate = now()->endOfYear();
                    break;
                case 'all':
                    // Keep null
                    break;
            }
        }

        $this->recurringMeetings = $recurringSeries->map(function ($series) use ($effectiveStartDate, $effectiveEndDate) {
            $firstMeeting = $series->meetings->first();
            if (!$firstMeeting) return null;

            // Filter Children by Date Range
            $filteredChildren = $series->meetings->filter(function ($meeting) use ($effectiveStartDate, $effectiveEndDate) {
                if (!$effectiveStartDate || !$effectiveEndDate) return true; // Show all if no range
                return $meeting->start_time >= $effectiveStartDate && $meeting->start_time <= $effectiveEndDate;
            })->sortBy('start_time');

            if ($filteredChildren->isEmpty()) {
                return null;
            }

            $series->topic = $firstMeeting->topic;
            $series->room = $firstMeeting->room;
            $series->children = $filteredChildren;
            $series->recurring_type = $series->frequency;
            $series->recurring_end_date = $series->ends_at;

            return $series;
        })->filter()->values(); // Reset keys after filtering
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
