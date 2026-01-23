<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Meeting;
use Carbon\Carbon;

class CancelUnconfirmedMeetings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meeting:cancel-unconfirmed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancels unconfirmed meetings whose start time has passed.';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\InventoryService $inventoryService)
    {
        $this->info('Checking for unconfirmed meetings to cancel...');

        $meetingsToCancel = Meeting::where('confirmation_status', 'pending_confirmation')
            ->where('start_time', '<', Carbon::now()->subMinutes(30)) // Grace period 30 mins
            ->where('status', '!=', 'cancelled')
            ->get();

        if ($meetingsToCancel->isEmpty()) {
            $this->info('No unconfirmed meetings found to cancel.');
            return 0;
        }

        foreach ($meetingsToCancel as $meeting) {
            $inventoryService->refundStockForMeeting($meeting);
            $meeting->update(['status' => 'cancelled']);
            $this->warn("Meeting ID {$meeting->id} ('{$meeting->topic}') has been cancelled due to non-confirmation.");
        }

        $this->info('Cancellation process complete.');

        return 0;
    }
}
