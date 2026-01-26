<?php

namespace App\Services;

use App\Models\Meeting;
use App\Models\MeetingParticipant;
use App\Models\PantryOrder;
use App\Models\PantryItem;
use App\Models\RecurringMeeting;
use App\Models\User;
use App\Models\ExternalParticipant;
use App\Mail\MeetingInvitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Exception;

class BookingService
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Check if a room is available for a given time range.
     * @param string $endTime Y-m-d H:i:s
     * @param int $roomId
     * @param int|null $excludeMeetingId
     * @return bool
     */
    public function isRoomAvailable(string $startTime, string $endTime, int $roomId, ?int $excludeMeetingId = null): bool
    {
        $query = Meeting::where('room_id', $roomId)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    // Overlap check
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
                });
            })
            // Exclude cancelled meetings from availability check
            ->where('status', '!=', 'cancelled');

        if ($excludeMeetingId) {
            $query->where('id', '!=', $excludeMeetingId);
        }

        return !$query->exists();
    }

    /**
     * Handle the creation of a meeting (single or recurring).
     *
     * @param array $data Validated data from the form
     * @param array $internalParticipants Values: IDs
     * @param array $externalParticipants Values: IDs
     * @param array $pantryOrders Array of ['pantry_item_id' => id, 'quantity' => qty]
     * @return void
     * @throws Exception
     */
    public function createMeeting(array $data, array $internalParticipants, array $externalParticipants, array $pantryOrders, array $picParticipants = [])
    {
        $recurring = $data['recurring'] ?? false;
        $roomId = $data['room_id'];
        $duration = $data['duration'];
        $startTimeString = $data['start_time'];
        
        DB::beginTransaction();
        try {
            if ($recurring) {
                $this->handleRecurringCreation($data, $internalParticipants, $externalParticipants, $pantryOrders, $picParticipants);
            } else {
                $this->handleSingleCreation($data, $internalParticipants, $externalParticipants, $pantryOrders, $picParticipants);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function handleSingleCreation($data, $internalParticipants, $externalParticipants, $pantryOrders, $picParticipants)
    {
        $newStartTime = new \DateTime($data['start_time']);
        $newEndTime = (clone $newStartTime)->add(new \DateInterval('PT' . $data['duration'] . 'M'));

        if (!$this->isRoomAvailable($newStartTime->format('Y-m-d H:i:s'), $newEndTime->format('Y-m-d H:i:s'), $data['room_id'])) {
            throw new Exception('The selected room is not available at the chosen time.');
        }

        $meeting = Meeting::create([
            'room_id' => $data['room_id'],
            'topic' => $data['topic'],
            'start_time' => $newStartTime,
            'end_time' => $newEndTime,
            'priority_guest_id' => $data['priority_guest_id'] ?? null,
            'meeting_type' => 'non-recurring',
            'user_id' => auth()->id(),
            'status' => 'scheduled',
        ]);

        $this->attachParticipantsAndPantryOrders($meeting, $internalParticipants, $externalParticipants, $pantryOrders, $picParticipants);
        $this->sendMeetingInvitation($meeting);
        \App\Events\MeetingStatusUpdated::dispatch($meeting->room);
        \App\Events\RoomStatusUpdated::dispatch($meeting->room_id);
    }

    protected function handleRecurringCreation($data, $internalParticipants, $externalParticipants, $pantryOrders, $picParticipants)
    {
        $startDate = new \DateTime($data['start_time']);
        $endDate = new \DateTime($data['ends_at']);
        $interval = new \DateInterval($this->getRecurringInterval($data['frequency']));
        // Recurring meetings include the start date, so we treat it as Period
        $period = new \DatePeriod($startDate, $interval, $endDate);

        // Pre-check availability for all slots
        foreach ($period as $date) {
            $recurringStartTime = $date;
            $recurringEndTime = (clone $recurringStartTime)->add(new \DateInterval('PT' . $data['duration'] . 'M'));
            
            if (!$this->isRoomAvailable($recurringStartTime->format('Y-m-d H:i:s'), $recurringEndTime->format('Y-m-d H:i:s'), $data['room_id'])) {
                // Formatting for readable error
                throw new Exception('The room is not available for the recurring schedule on ' . $recurringStartTime->format('d-m-Y H:i'));
            }
        }

        $recurringMeeting = RecurringMeeting::create([
            'frequency' => $data['frequency'],
            'ends_at' => $data['ends_at'],
        ]);

        foreach ($period as $date) {
            $recurringStartTime = $date;
            $recurringEndTime = (clone $recurringStartTime)->add(new \DateInterval('PT' . $data['duration'] . 'M'));
            
            $meeting = Meeting::create([
                'room_id' => $data['room_id'],
                'topic' => $data['topic'],
                'start_time' => $recurringStartTime,
                'end_time' => $recurringEndTime,
                'priority_guest_id' => $data['priority_guest_id'] ?? null,
                'meeting_type' => 'recurring',
                'recurring_meeting_id' => $recurringMeeting->id,
                'user_id' => auth()->id(),
                'status' => 'scheduled',
                'confirmation_status' => 'pending_confirmation',
            ]);

            $this->attachParticipantsAndPantryOrders($meeting, $internalParticipants, $externalParticipants, $pantryOrders, $picParticipants);
            $this->sendMeetingInvitation($meeting);
            \App\Events\MeetingStatusUpdated::dispatch($meeting->room);
            \App\Events\RoomStatusUpdated::dispatch($meeting->room_id);
        }
    }

    protected function attachParticipantsAndPantryOrders(Meeting $meeting, array $internalParticipants, array $externalParticipants, array $pantryOrders, array $picParticipants = [])
    {
        // Auto-add organizer as participant (if not already in the list)
        if (!in_array($meeting->user_id, $internalParticipants)) {
            MeetingParticipant::create([
                'meeting_id' => $meeting->id,
                'participant_id' => $meeting->user_id,
                'participant_type' => User::class,
            ]);
        }

        // Attach Internal Participants
        foreach ($internalParticipants as $userId) {
            MeetingParticipant::create([
                'meeting_id' => $meeting->id,
                'participant_id' => $userId,
                'participant_type' => User::class,
                'is_pic' => in_array($userId, $picParticipants),
            ]);
        }

        // Attach External Participants
        foreach ($externalParticipants as $participantId) {
            MeetingParticipant::create([
                'meeting_id' => $meeting->id,
                'participant_id' => $participantId,
                'participant_type' => ExternalParticipant::class,
            ]);
        }

        // Create Pantry Orders
        foreach ($pantryOrders as $order) {
            if (!empty($order['pantry_item_id']) && !empty($order['quantity'])) {
                $pantryOrder = PantryOrder::create([
                    'meeting_id' => $meeting->id,
                    'pantry_item_id' => $order['pantry_item_id'],
                    'quantity' => $order['quantity'],
                    'status' => 'pending',
                    'custom_items' => $order['custom_items'] ?? null,
                ]);

                // Definite dispatch for real-time updates
                \App\Events\PantryOrderStatusUpdated::dispatch($pantryOrder);
            }
        }

        // Deduct stock for this meeting using InventoryService
        $this->inventoryService->deductStock($pantryOrders);
    }

    protected function sendMeetingInvitation(Meeting $meeting)
    {
        $icsService = new \App\Services\IcsService();
        $icsContent = $icsService->generateIcsFile($meeting);

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

    public function updateMeeting(Meeting $meeting, array $data, array $internalParticipants, array $externalParticipants, array $pantryOrders, array $picParticipants = [])
    {
        DB::beginTransaction();
        try {
            $startTime = new \DateTime($data['start_time']);
            $endTime = (clone $startTime)->add(new \DateInterval('PT' . $data['duration'] . 'M'));

            // Check availability (exclude current meeting)
            if (!$this->isRoomAvailable($startTime->format('Y-m-d H:i:s'), $endTime->format('Y-m-d H:i:s'), $data['room_id'], $meeting->id)) {
                // Formatting for readable error (re-using matching logic)
                 throw new Exception('The selected room is not available at the chosen time (Time Collision).');
            }

            // Update Basic Info
            $meeting->update([
                'room_id' => $data['room_id'],
                'topic' => $data['topic'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'priority_guest_id' => $data['priority_guest_id'] ?? null,
                // Status logic: if formerly completed/cancelled, what happens? 
                // Usually editing implies re-scheduling or fixing details.
                // For now we keep existing status or reset to scheduled if it was cancelled?
                // Let's assume just updating details doesn't auto-change status unless explicitly handled.
                // But if time changes, we might want to ensure it's valid.
            ]);

            // Sync Participants
            // 1. Detach all (or smart sync). Simple detach+create is easier but loses check-in status.
            // Better: Sync Internal
            
            // Get current participants
            $currentInternalMap = $meeting->meetingParticipants()
                ->where('participant_type', User::class)
                ->get()
                ->keyBy('participant_id');

            // Process Internal Re-sync
            // We want to KEEP check-in status if the person is still invited.
            $keepParticipantIds = [];
            
            // Always ensure Organizer is kept/added
            if (!in_array($meeting->user_id, $internalParticipants)) {
                $internalParticipants[] = $meeting->user_id;
            }

            foreach ($internalParticipants as $userId) {
                if ($currentInternalMap->has($userId)) {
                    // Update existing (e.g. PIC status might change)
                    $mp = $currentInternalMap->get($userId);
                    $mp->update(['is_pic' => in_array($userId, $picParticipants)]);
                    $keepParticipantIds[] = $mp->id;
                } else {
                    // Create new
                    $mp = MeetingParticipant::create([
                        'meeting_id' => $meeting->id,
                        'participant_id' => $userId,
                        'participant_type' => User::class,
                        'is_pic' => in_array($userId, $picParticipants),
                        'status' => 'confirmed' // Default for new additions
                    ]);
                    $keepParticipantIds[] = $mp->id;
                }
            }

            // External Participants (Simple Sync: Delete & Re-add is usually fine as they don't have check-in accounts mostly, 
            // but if they do (future feature), smart sync is better. For now simple sync for external IDs is tricky 
            // because `externalParticipants` array from form usually contains IDs of `ExternalParticipant` models.
            
            $currentExternalMap = $meeting->meetingParticipants()
                ->where('participant_type', ExternalParticipant::class)
                ->get()
                ->keyBy('participant_id');

            foreach ($externalParticipants as $extId) {
                if ($currentExternalMap->has($extId)) {
                    $keepParticipantIds[] = $currentExternalMap->get($extId)->id;
                } else {
                    $mp = MeetingParticipant::create([
                        'meeting_id' => $meeting->id,
                        'participant_id' => $extId,
                        'participant_type' => ExternalParticipant::class,
                        'status' => 'confirmed'
                    ]);
                    $keepParticipantIds[] = $mp->id;
                }
            }

            // Remove participants not in the new list
            $meeting->meetingParticipants()->whereNotIn('id', $keepParticipantIds)->delete();


            // Sync Pantry Orders
            // Complex because of stock.
            // Strategy: 
            // 1. Restore stock of REMOVED orders.
            // 2. Deduct stock of NEW orders.
            // 3. Update changed quantities.
            
            // Simplified: Detach all & Restore Stock -> Create new & Deduct Stock.
            // Efficient enough for typical meeting edits.
            
            // A. Restore Stock from current orders
            foreach ($meeting->pantryOrders as $oldOrder) {
                $this->inventoryService->restoreStock($oldOrder->pantry_item_id, $oldOrder->quantity);
            }
            // B. Delete old orders
            $meeting->pantryOrders()->delete();

            // C. Create new & Deduct
            // Filter empty
            $validOrders = [];
            foreach ($pantryOrders as $order) {
                if (!empty($order['pantry_item_id']) && !empty($order['quantity'])) {
                    $validOrders[] = $order;
                     PantryOrder::create([
                        'meeting_id' => $meeting->id,
                        'pantry_item_id' => $order['pantry_item_id'],
                        'quantity' => $order['quantity'],
                        'status' => 'pending',
                        'custom_items' => $order['custom_items'] ?? null,
                    ]);
                }
            }
            $this->inventoryService->deductStock($validOrders);


            // Notify
            // $this->sendMeetingInvitation($meeting); // Optional: Only if time changed? Or always?
            // For now specific "Update" email might be better, or just silent update. 
            // Let's dispatch events for Tablet/Dashboard refresh.
            \App\Events\MeetingStatusUpdated::dispatch($meeting->room);
            \App\Events\RoomStatusUpdated::dispatch($meeting->room_id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function getRecurringInterval(string $pattern): string
    {
        return match ($pattern) {
            'daily' => 'P1D',
            'weekly' => 'P1W',
            'monthly' => 'P1M',
            default => 'P1D',
        };
    }
}
