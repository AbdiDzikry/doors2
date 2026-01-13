<?php

namespace App\Livewire\Analytics;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Meeting;
use Illuminate\Support\Facades\Auth;

class MeetingDetailsModal extends Component
{
    use WithPagination;

    public $showModal = false;
    
    // Filters passed from parent
    public $startDate;
    public $endDate;
    public $division;
    public $department;
    public $statusFilter = 'all';

    protected $listeners = ['openAnalyticsModal' => 'open'];

    public function open($filters = [])
    {
        $this->startDate = $filters['startDate'] ?? null;
        $this->endDate = $filters['endDate'] ?? null;
        $this->division = $filters['division'] ?? null;
        $this->department = $filters['department'] ?? null;
        $this->showModal = true;
        $this->resetPage(); // Reset pagination when opening
    }

    public function close()
    {
        $this->showModal = false;
    }

    public function getMeetingsProperty()
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('Super Admin');

        $query = Meeting::query()
            ->with(['room', 'user', 'organizer'])
            // Global Filter: Exclude cancelled
            ->where('meetings.status', '!=', 'cancelled');

        // Date Range
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('start_time', [
                \Carbon\Carbon::parse($this->startDate)->startOfDay(),
                \Carbon\Carbon::parse($this->endDate)->endOfDay()
            ]);
        }

        // Role-Based Filtering
        if (!$isSuperAdmin) {
            // Regular employees only see meetings they are involved in or from their department
            // Logic mirrored from AnalyticsController: "Invited by Department" for them means meetings they attend.
            // But usually "My Meetings" logic is simpler:
            $query->where(function ($q) use ($user) {
                // Own meetings
                $q->where('user_id', $user->id)
                  // Or invited
                  ->orWhereHas('meetingParticipants', function ($sub) use ($user) {
                      $sub->where('participant_id', $user->id)
                          ->where('participant_type', \App\Models\User::class);
                  });
            });
        }

        // Admin Filters
        if ($isSuperAdmin) {
            if ($this->division) {
                $query->whereHas('user', function ($q) {
                    $q->where('division', $this->division);
                });
            }
            if ($this->department) {
                $query->whereHas('user', function ($q) {
                    $q->where('department', $this->department);
                });
            }
        }

        return $query->orderBy('start_time', 'desc')->paginate(10);
    }

    public function render()
    {
        return view('livewire.analytics.meeting-details-modal', [
            'meetings' => $this->showModal ? $this->meetings : []
        ]);
    }
}
