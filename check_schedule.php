<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Meeting;
use App\Models\Room;

$roomName = 'Ruang Arjuna';
$date = '2026-01-19';

$room = Room::where('name', $roomName)->first();
if (!$room) {
    echo "Room not found\n";
    exit;
}

echo "Schedule for $roomName on $date:\n";
$meetings = Meeting::where('room_id', $room->id)
    ->whereDate('start_time', $date)
    ->get();

if ($meetings->isEmpty()) {
    echo "No meetings found.\n";
} else {
    foreach ($meetings as $m) {
        echo "ID: {$m->id} | Topic: {$m->topic} | Time: {$m->start_time} - {$m->end_time} | UserID: {$m->user_id}\n";
    }
}
