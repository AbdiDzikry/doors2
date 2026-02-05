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
        // Check if auto-cancel is enabled
        $autoCancelEnabled = \App\Models\Configuration::where('key', 'auto_cancel_unattended_meetings')
            ->value('value');

        // Logic:
        // We fetch meetings that started at least 30 minutes ago.
        // If Auto-Cancel is ON:
        //    - No Attendance -> Cancel.
        //    - Has Attendance AND Ended -> Complete.
        // If Auto-Cancel is OFF:
        //    - Ended -> Complete (Implicitly assuming attendance or ignoring it).
        //    - Not Ended -> Do nothing.

        $threshold = now()->subMinutes(30);

        $meetings = Meeting::whereIn('status', ['scheduled', 'ongoing'])
            ->where('start_time', '<=', $threshold)
            ->with(['meetingParticipants'])
            ->get();

        $countCancelled = 0;
        $countCompleted = 0;

        foreach ($meetings as $meeting) {
            /** @var \App\Models\Meeting $meeting */
            $hasAttendance = $meeting->meetingParticipants()
                ->whereNotNull('attended_at')
                ->exists();

            $isEnded = now()->gt($meeting->end_time);

            if ($autoCancelEnabled === '1') {
                // strict mode
                if (!$hasAttendance) {
                    $meeting->status = 'cancelled';
                    $meeting->save();
                    $countCancelled++;

                    MeetingStatusUpdated::dispatch($meeting->room);
                    RoomStatusUpdated::dispatch($meeting->room_id);
                    continue; // Done with this meeting
                }

                // If attended, check if we should complete
                if ($isEnded) {
                    $meeting->status = 'completed';
                    $meeting->save();
                    $countCompleted++;

                    MeetingStatusUpdated::dispatch($meeting->room);
                    RoomStatusUpdated::dispatch($meeting->room_id);
                }
            } else {
                // Auto-cancel OFF (Relaxed mode)
                // We never auto-cancel. We only auto-complete if time is up.
                if ($isEnded) {
                    $meeting->status = 'completed';
                    $meeting->save();
                    $countCompleted++;

                    MeetingStatusUpdated::dispatch($meeting->room);
                    RoomStatusUpdated::dispatch($meeting->room_id);
                }
            }
        }

        $this->info("Processed meetings. Mode: " . ($autoCancelEnabled === '1' ? 'Strict' : 'Relaxed') . ". Cancelled: {$countCancelled}, Completed: {$countCompleted}.");
    }


}
