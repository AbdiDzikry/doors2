<?php
// Simple validation script for meeting data
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$logFile = __DIR__ . '/logpemesan.txt';
$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$results = [];

foreach ($lines as $lineNum => $line) {
    $line = trim($line);
    if (empty($line) || $line === '.') continue;
    
    $parts = explode("\t", $line);
    if (count($parts) < 7) continue;
    
    $roomName = trim($parts[0]);
    $startTime = trim($parts[2]);
    $endTime = trim($parts[3]);
    $topic = trim($parts[4]);
    $organizerName = trim($parts[6]);
    
    $lineNo = $lineNum + 1;
    
    // Find room
    $room = DB::table('rooms')->where('name', 'LIKE', "%{$roomName}%")->first();
    if (!$room) {
        $results[] = "Line $lineNo|ERROR|Ruangan '$roomName' tidak ditemukan";
        continue;
    }
    
    // Find organizer
    $organizer = DB::table('users')->where('name', 'LIKE', "%{$organizerName}%")->first();
    if (!$organizer) {
        $results[] = "Line $lineNo|ERROR|User '$organizerName' tidak ditemukan";
        continue;
    }
    
    // Check existing meeting
    $existing = DB::table('meetings')
        ->where('room_id', $room->id)
        ->where('start_time', $startTime)
        ->where('topic', $topic)
        ->first();
    
    if ($existing) {
        $existingUser = DB::table('users')->where('id', $existing->user_id)->first();
        if ($existingUser && stripos($existingUser->name, $organizerName) !== false) {
            $results[] = "Line $lineNo|OK|Meeting sudah ada dengan organizer benar: $topic";
        } else {
            $results[] = "Line $lineNo|WRONG_ORGANIZER|Meeting ada tapi organizer salah: $topic (Seharusnya: $organizerName, Saat ini: " . ($existingUser ? $existingUser->name : 'Unknown') . ")";
        }
        continue;
    }
    
    // Check conflicts
    $conflicts = DB::table('meetings')
        ->where('room_id', $room->id)
        ->where('status', '!=', 'cancelled')
        ->where(function($q) use ($startTime, $endTime) {
            $q->where(function($q2) use ($startTime, $endTime) {
                $q2->where('start_time', '<', $endTime)
                   ->where('end_time', '>', $startTime);
            });
        })
        ->get();
    
    if ($conflicts->count() > 0) {
        foreach ($conflicts as $conflict) {
            $conflictUser = DB::table('users')->where('id', $conflict->user_id)->first();
            $results[] = "Line $lineNo|CONFLICT|$topic bentrok dengan '" . $conflict->topic . "' (" . ($conflictUser ? $conflictUser->name : 'Unknown') . ")";
        }
        continue;
    }
    
    $results[] = "Line $lineNo|VALID|$topic oleh $organizerName di $roomName";
}

foreach ($results as $result) {
    echo $result . "\n";
}
