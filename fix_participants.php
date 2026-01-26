<?php
use App\Models\Meeting;
use App\Models\MeetingParticipant;
use App\Models\User;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Starting Repair...\n";

// Find meetings from 2026 onwards that have NO participants
$meetings = Meeting::whereYear('start_time', '>=', 2026)
    ->withCount('meetingParticipants')
    ->having('meeting_participants_count', 0)
    ->get();

echo "Found " . $meetings->count() . " meetings with missing participants.\n";

foreach ($meetings as $meeting) {
    echo "Fixing Meeting ID {$meeting->id} (User: {$meeting->user_id})... ";
    
    try {
        MeetingParticipant::create([
            'meeting_id' => $meeting->id,
            'participant_id' => $meeting->user_id,
            'participant_type' => User::class,
            'is_pic' => true, // Legacy imports usually imply the organizer is the PIC
            'status' => $meeting->status === 'completed' ? 'attended' : 'confirmed',
            'checked_in_at' => $meeting->status === 'completed' ? $meeting->start_time : null,
            'attended_at' => $meeting->status === 'completed' ? $meeting->start_time : null,
        ]);
        echo "Done.\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

echo "Repair Complete.\n";
