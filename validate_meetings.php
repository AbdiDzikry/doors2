<?php
// Script to validate meeting data from logpemesan.txt
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Read logpemesan.txt
$logFile = __DIR__ . '/logpemesan.txt';
$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

echo "=== VALIDASI DATA PEMESANAN ===\n\n";

$expectedMeetings = [];
$issues = [];
$success = [];

foreach ($lines as $lineNum => $line) {
    $line = trim($line);
    if (empty($line) || $line === '.') continue;
    
    $parts = explode("\t", $line);
    if (count($parts) < 7) {
        $issues[] = "Line " . ($lineNum + 1) . ": Format tidak valid (kurang kolom) - $line";
        continue;
    }
    
    $roomName = trim($parts[0]);
    $date = trim($parts[1]);
    $startTime = trim($parts[2]);
    $endTime = trim($parts[3]);
    $topic = trim($parts[4]);
    $description = trim($parts[5]);
    $organizerName = trim($parts[6]);
    
    // Find room
    $room = DB::table('rooms')->where('name', 'LIKE', "%{$roomName}%")->first();
    if (!$room) {
        $issues[] = "❌ Line " . ($lineNum + 1) . ": Ruangan '$roomName' tidak ditemukan";
        continue;
    }
    
    // Find organizer by name
    $organizer = DB::table('users')->where('name', 'LIKE', "%{$organizerName}%")->first();
    if (!$organizer) {
        $issues[] = "❌ Line " . ($lineNum + 1) . ": User '$organizerName' tidak ditemukan di database";
        continue;
    }
    
    // Check if meeting already exists
    $existingMeeting = DB::table('meetings')
        ->where('room_id', $room->id)
        ->where('start_time', $startTime)
        ->where('end_time', $endTime)
        ->where('topic', $topic)
        ->first();
    
    if ($existingMeeting) {
        // Check if organizer matches
        $existingOrganizer = DB::table('users')->where('id', $existingMeeting->user_id)->first();
        if ($existingOrganizer && $existingOrganizer->name === $organizer->name) {
            $success[] = "✅ Line " . ($lineNum + 1) . ": Meeting sudah ada dengan organizer yang benar - '$topic' ($organizerName)";
        } else {
            $issues[] = "⚠️  Line " . ($lineNum + 1) . ": Meeting sudah ada tapi organizer SALAH - '$topic' (Seharusnya: $organizerName, Saat ini: " . ($existingOrganizer ? $existingOrganizer->name : 'Unknown') . ")";
        }
        continue;
    }
    
    // Check for conflicts (same room, overlapping time)
    $conflicts = DB::table('meetings')
        ->where('room_id', $room->id)
        ->where('status', '!=', 'cancelled')
        ->where(function($query) use ($startTime, $endTime) {
            $query->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function($q) use ($startTime, $endTime) {
                      $q->where('start_time', '<=', $startTime)
                        ->where('end_time', '>=', $endTime);
                  });
        })
        ->get();
    
    if ($conflicts->count() > 0) {
        foreach ($conflicts as $conflict) {
            $conflictOrganizer = DB::table('users')->where('id', $conflict->user_id)->first();
            $issues[] = "🔴 Line " . ($lineNum + 1) . ": BENTROK - '$topic' ($organizerName) bentrok dengan '" . $conflict->topic . "' (" . ($conflictOrganizer ? $conflictOrganizer->name : 'Unknown') . ") di " . $roomName . " pada " . Carbon::parse($conflict->start_time)->format('H:i') . "-" . Carbon::parse($conflict->end_time)->format('H:i');
        }
        continue;
    }
    
    // Check for duplicate topic on different date/room (weekly meetings)
    $sameTopic = DB::table('meetings')
        ->where('topic', $topic)
        ->where(function($query) use ($startTime, $room) {
            $query->where('start_time', '!=', $startTime)
                  ->orWhere('room_id', '!=', $room->id);
        })
        ->get();
    
    if ($sameTopic->count() > 0) {
        echo "ℹ️  Line " . ($lineNum + 1) . ": Topic '$topic' sudah ada di tanggal/ruangan lain (" . $sameTopic->count() . " meeting) - Kemungkinan meeting weekly/recurring\n";
    }
    
    // If we reach here, meeting is valid and can be created
    $success[] = "✅ Line " . ($lineNum + 1) . ": VALID - '$topic' ($organizerName) di $roomName pada " . Carbon::parse($startTime)->format('d M H:i') . "-" . Carbon::parse($endTime)->format('H:i');
}

echo "\n=== RINGKASAN ===\n";
echo "Total baris: " . count($lines) . "\n";
echo "Valid: " . count($success) . "\n";
echo "Issues: " . count($issues) . "\n\n";

if (count($success) > 0) {
    echo "=== MEETING YANG VALID ===\n";
    foreach ($success as $msg) {
        echo $msg . "\n";
    }
    echo "\n";
}

if (count($issues) > 0) {
    echo "=== ISSUES YANG DITEMUKAN ===\n";
    foreach ($issues as $msg) {
        echo $msg . "\n";
    }
}
