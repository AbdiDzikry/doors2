<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Meeting;
use App\Events\MeetingStatusUpdated;
use App\Events\RoomStatusUpdated;

class CancelUnattendedMeetings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meetings:cancel-unattended';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel meetings where no attendance was recorded 30 minutes after end time, or mark valid ones as completed.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // New Logic (Per User Request): 
        // Cancel if meeting has started > 30 mins ago (now > start_time + 30)
        // AND no attendance recorded.
        // This frees up the room if they are late.

        $threshold = now()->subMinutes(30);

        // We look for meetings where:
        // 1. Status is 'scheduled' or 'ongoing'
        // 2. Start Time was BEFORE the threshold (meaning 30 mins have passed since start)
        // 3. Current time is still BEFORE End Time (so we don't cancel meetings that just finished normally, 
        //    though if they finished without attendance they technically should be cancelled too? 
        //    Let's stick to the "Free up active room" logic first. 
        //    Actually, if the meeting is OVER and no one attended, it should also be cancelled/completed.
        //    
        //    Let's handle BOTH cases in one sweep or separate?
        //    Case A: Meeting started > 30 mins ago, still ongoing, no attendance -> CANCEL (Free up room).
        //    Case B: Meeting Ended > 30 mins ago, no attendance -> CANCEL (Admin cleanup).
        
        // Let's implement Case A (The "Free up room" logic) + Case B (The "Cleanup" logic).
        // A simple query: If start_time <= (Now - 30mins) AND no attendance -> Cancel.
        // This covers both "Late start" and "Already ended".

        $meetings = Meeting::whereIn('status', ['scheduled', 'ongoing'])
            ->where('start_time', '<=', $threshold)
            ->with(['meetingParticipants'])
            ->get();

        $countCancelled = 0;
        $countCompleted = 0; // Not really processing completions here anymore, mainly cancellations.

        foreach ($meetings as $meeting) {
            // Check for ANY attendance
            $hasAttendance = $meeting->meetingParticipants()
                ->whereNotNull('attended_at')
                ->exists();

            if ($hasAttendance) {
                // If attended, we check if it is already finished to mark as completed?
                // The logical place for "Mark Completed" is strictly after End Time.
                if (now()->gt($meeting->end_time)) {
                     $meeting->status = 'completed';
                     $meeting->save();
                     $countCompleted++;
                }
                // If still ongoing (between start and end), we leave it as is (or set to 'ongoing').
            } else {
                // NO ATTENDANCE + >30 mins since start = CANCEL
                $meeting->status = 'cancelled';
                // Optional: $meeting->cancellation_reason = 'System Auto-Cancel (No Show > 30m)';
                $meeting->save();
                
                $countCancelled++;
            }

            // Sync events
            MeetingStatusUpdated::dispatch($meeting->room);
            RoomStatusUpdated::dispatch($meeting->room_id);
        }

        $this->info("Processed meetings. Cancelled (No Show): {$countCancelled}, Marked Completed: {$countCompleted}.");
    }


}
