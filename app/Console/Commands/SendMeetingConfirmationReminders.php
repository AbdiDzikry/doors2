<?php

namespace App\Console\Commands;

use App\Models\Meeting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\MeetingConfirmationReminder;

class SendMeetingConfirmationReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meetings:send-confirmation-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send confirmation reminders for recurring meetings.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sending meeting confirmation reminders...');

        $meetings = Meeting::where('confirmation_status', 'pending_confirmation')
            ->where('start_time', '<=', now()->addDay())
            ->where('start_time', '>', now())
            ->get();

        foreach ($meetings as $meeting) {
            Mail::to($meeting->organizer->email)->send(new MeetingConfirmationReminder($meeting));
        }

        $this->info('Done.');
    }
}
