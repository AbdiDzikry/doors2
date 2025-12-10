<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $roomId;
    public $status; // 'available', 'in_use', 'under_maintenance'
    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct($roomId)
    {
        $this->roomId = $roomId;
        $room = \App\Models\Room::find($roomId);

        if (!$room) {
            return;
        }

        if ($room->status === 'under_maintenance') {
            $this->status = 'under_maintenance';
            $this->message = 'Maintenance';
        } else {
            // Check if there is an active meeting right now
            $isInUse = $room->meetings()
                ->where('start_time', '<=', now())
                ->where('end_time', '>=', now())
                ->where('status', '!=', 'cancelled')
                ->exists();

            if ($isInUse) {
                $this->status = 'in_use';
                $this->message = 'In Use';
            } else {
                $this->status = 'available';
                $this->message = 'Available';
            }
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('rooms'),
        ];
    }
}
