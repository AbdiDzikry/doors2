<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== QUADRAL CHECK AUDIT (Business Logic Integrity) ===\n\n";

$meetings = DB::table('meetings')
    ->whereBetween('start_time', ['2026-01-27 00:00:00', '2026-04-30 23:59:59'])
    ->get();

$errors = 0;
$checked = 0;

foreach ($meetings as $m) {
    $checked++;
    
    // 1. Time Logic Check
    $start = Carbon::parse($m->start_time);
    $end = Carbon::parse($m->end_time);
    
    if ($start >= $end) {
        echo "❌ TIME ERROR: Meeting ID {$m->id} starts after it ends! ({$m->start_time} - {$m->end_time})\n";
        $errors++;
    }
    
    // 2. Room Consistency Check (Reverse Lookup)
    $room = DB::table('rooms')->where('id', $m->room_id)->first();
    if (!$room) {
        echo "❌ ROOM ERROR: Meeting ID {$m->id} has invalid room_id {$m->room_id}\n";
        $errors++;
    }
    
    // 3. User Data Integrity
    $user = DB::table('users')->where('id', $m->user_id)->first();
    if (!$user) {
        echo "❌ USER ERROR: Meeting ID {$m->id} assigned to non-existent user {$m->user_id}\n";
        $errors++;
    } else {
        if (strpos($user->email, '@') === false) {
             echo "⚠️ USER WARNING: Organizer {$user->name} has invalid email ({$user->email})\n";
             // Not a critical error, just warning
        }
    }

    // 4. Status Check
    if ($m->status !== 'scheduled' && $m->status !== 'confirmed') {
        echo "⚠️ STATUS NOTICE: Meeting ID {$m->id} status is '{$m->status}'\n";
    }
}

echo "\nLogic Validity Check:\n";
if ($errors == 0) {
    echo "✅ PASSED: Logic consistency check (Time, Room, User Existence) for $checked meetings.\n";
} else {
    echo "❌ FAILED: Found $errors logic errors.\n";
}

// 5. Cross-Reference Room Names (Log vs DB) - "The Quadral Element"
// We check if the Room Name in Log matches the Room Name in DB for the same ID
echo "\nChecking Room Name Mapping Accuracy...\n";

$logFile = __DIR__ . '/logpemesan_final.txt';
$logLines = file($logFile, FILE_IGNORE_NEW_LINES);
$mapErrors = 0;

foreach ($logLines as $line) {
    if (strpos($line, "\t") === false) continue;
    $parts = explode("\t", $line);
    if (count($parts) < 7) continue;
    
    $logRoom = trim($parts[0]);
    $startTime = trim($parts[2]);
    
    $logTopic = trim($parts[4]);

    // Get meeting from DB by time AND topic (to handle multiple meetings at same time)
    $dbMeeting = DB::table('meetings')
        ->where('start_time', $startTime)
        // Topic fuzzy match? Or just try to find ONE that matches room?
        ->get(); // Get ALL meetings at this time
    
    $foundMatch = false;
    foreach ($dbMeeting as $meeting) {
        $dbRoom = DB::table('rooms')->where('id', $meeting->room_id)->first();
        if ($dbRoom) {
            // Check similarity
             $match = false;
             if (stripos($dbRoom->name, str_replace('Ruang ', '', $logRoom)) !== false) $match = true;
             if (stripos($logRoom, $dbRoom->name) !== false) $match = true;
             if ($logRoom == 'Ruang Arjuno' && stripos($dbRoom->name, 'Arjuna') !== false) $match = true;
             if ($logRoom == 'Ruang Arjuna' && stripos($dbRoom->name, 'Arjuna') !== false) $match = true;
             
             if ($match) {
                 $foundMatch = true;
                 break;
             }
        }
    }

    if (!$foundMatch) {
         echo "⚠️ MISMATCH: Log '$logRoom' (Time: $startTime) not found in DB rooms for this time slot.\n";
         $mapErrors++;
    }
}

if ($mapErrors == 0) {
    echo "✅ PASSED: All Log Rooms map correctly to Database Rooms.\n";
} else {
    echo "⚠️ WARNING: Found $mapErrors room mapping suspicious entries.\n";
}

echo "\n=== QUADRAL CHECK COMPLETE ===\n";
