<?php

namespace App\Livewire\Dashboard\Receptionist;

use Livewire\Component;
use App\Models\PantryOrder;
use Livewire\Attributes\On;

class PantryQueue extends Component
{
    #[On('echo:pantry-orders,.pantry.order.updated')]
    public function refreshQueue()
    {
        // This method handles the event. Livewire automatically re-renders the component
        // when an event listener is triggered, so no specific logic is needed here
        // unless we were updating a public property.
    }

    public function render()
    {
        // Fetch active pantry orders (pending or preparing)
        $activePantryOrders = PantryOrder::with(['meeting.room', 'meeting.user', 'pantryItem'])
            ->whereIn('status', ['pending', 'preparing'])
            ->whereHas('meeting', function ($query) {
                $query->where('status', '!=', 'cancelled')
                      ->where('start_time', '>=', now()->startOfDay());
            })
            ->get();

        // Group by meeting and sort by oldest order first
        $activePantryOrdersGroupedByMeeting = $activePantryOrders->groupBy('meeting_id')->sortBy(function ($orders) {
            return $orders->min('created_at');
        })->toBase();

        return view('livewire.dashboard.receptionist.pantry-queue', [
            'activePantryOrdersGroupedByMeeting' => $activePantryOrdersGroupedByMeeting
        ]);
    }
}
