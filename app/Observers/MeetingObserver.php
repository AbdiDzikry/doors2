<?php

namespace App\Observers;

use App\Models\Meeting;
use Illuminate\Support\Facades\Log;

class MeetingObserver
{
    /**
     * Handle the Meeting "created" event.
     */
    public function created(Meeting $meeting): void
    {
        if ($meeting->priority_guest_id) {
            $this->autoMarkAttendance($meeting);
        }
    }

    /**
     * Handle the Meeting "updated" event.
     */
    public function updated(Meeting $meeting): void
    {
        // If priority guest was just added (changed from null to a value)
        if ($meeting->isDirty('priority_guest_id') && $meeting->priority_guest_id) {
            $this->autoMarkAttendance($meeting);
        }
    }

    /**
     * Auto-mark all participants as attended for VIP meetings
     */
    private function autoMarkAttendance(Meeting $meeting): void
    {
        $updated = $meeting->meetingParticipants()
            ->whereNull('attended_at')
            ->update([
                'attended_at' => now(),
                'status' => 'attended'
            ]);

        if ($updated > 0) {
            Log::info("VIP Meeting Auto-Attend: Marked {$updated} participants as attended for meeting ID {$meeting->id}");
        }
    }
}
