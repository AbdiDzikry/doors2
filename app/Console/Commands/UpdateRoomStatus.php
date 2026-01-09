<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Room;
use App\Events\RoomStatusUpdated;
use Illuminate\Support\Facades\Cache;

class UpdateRoomStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-room-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update room status based on current time (Starts/Ends meetings)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rooms = Room::all();
        $updatesCount = 0;

        foreach ($rooms as $room) {
            // Logic to determine current valid status
            // Standard logic: If Maintenance -> Maintenance. Else if Active Meeting -> In Use. Else -> Available.
            
            $newStatus = 'available';
            $message = 'Available';

            if ($room->status === 'under_maintenance') {
                $newStatus = 'under_maintenance';
                $message = 'Maintenance';
            } else {
                // Check for active meeting
                $activeMeeting = $room->meetings()
                    ->where('start_time', '<=', now())
                    ->where('end_time', '>=', now())
                    ->where('status', '!=', 'cancelled')
                    ->first();

                if ($activeMeeting) {
                    $newStatus = 'in_use';
                    $message = 'In Use';
                }
            }

            // Check against Cached Status
            // Key: room_status_{id}
            $cacheKey = "room_status_{$room->id}";
            $lastStatus = Cache::get($cacheKey);

            // If status changed OR if we just want to reinforce (e.g. maybe just changed minute)
            // Ideally, we only broadcast if status CHANGED to avoid network spam.
            if ($lastStatus !== $newStatus) {
                $this->info("Room {$room->name} status changed: {$lastStatus} -> {$newStatus}");
                
                // Update Cache
                Cache::put($cacheKey, $newStatus, 600); // 10 mins expiry

                // Fire Event
                // We construct the event which calculates status internally in __construct, 
                // but that's fine, it will reach the same conclusion.
                event(new RoomStatusUpdated($room->id));
                
                $updatesCount++;
            }
        }

        $this->info("Room status update completed. Broadcasted {$updatesCount} updates.");
    }
}
