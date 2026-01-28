<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Meeting Data
$roomName = 'Ruang Ciremai';
$startTime = '2026-01-27 08:30:00';
$endTime = '2026-01-27 13:00:00';
$topic = 'Meeting with Grakindo';
$description = 'Internal';
$organizerName = 'Sidiq Cahyo Priabudi';

// Find Room
$room = DB::table('rooms')->where('name', 'LIKE', "%{$roomName}%")->first();
if (!$room) {
    die("Error: Room '{$roomName}' not found.\n");
}

// Find User by Name
$user = DB::table('users')->where('name', $organizerName)->first();
if (!$user) {
    // Try LIKE search
    $user = DB::table('users')->where('name', 'LIKE', "%{$organizerName}%")->first();
}

if (!$user) {
    die("Error: User '{$organizerName}' not found.\n");
}

// Check for conflicts
$conflict = DB::table('meetings')
    ->where('room_id', $room->id)
    ->where('status', '!=', 'cancelled')
    ->where(function($q) use ($startTime, $endTime) {
        $q->whereBetween('start_time', [$startTime, $endTime])
          ->orWhereBetween('end_time', [$startTime, $endTime])
          ->orWhere(function($q2) use ($startTime, $endTime) {
              $q2->where('start_time', '<=', $startTime)
                 ->where('end_time', '>=', $endTime);
          });
    })
    ->first();

if ($conflict) {
    die("Error: Room conflict detected with meeting ID {$conflict->id} ({$conflict->topic}).\n");
}

// Insert Meeting
$meetingId = DB::table('meetings')->insertGetId([
    'user_id' => $user->id,
    'room_id' => $room->id,
    'topic' => $topic,
    'start_time' => $startTime,
    'end_time' => $endTime,
    'status' => 'scheduled',
    'created_at' => now(),
    'updated_at' => now(),
]);

// Insert Organizer as Participant
DB::table('meeting_participants')->insert([
    'meeting_id' => $meetingId,
    'participant_type' => 'App\Models\User',
    'participant_id' => $user->id,
    'status' => 'confirmed',
    'is_pic' => 1,
    'created_at' => now(),
    'updated_at' => now(),
]);

echo "✅ Meeting created successfully!\n";
echo "   ID: {$meetingId}\n";
echo "   Topic: {$topic}\n";
echo "   Room: {$room->name}\n";
echo "   Organizer: {$user->name} (NPK: {$user->npk})\n";
echo "   Time: {$startTime} - {$endTime}\n";
