<?php
// Script to append new meetings to FixedMeetingsSeeder
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$seederFile = __DIR__ . '/database/seeders/FixedMeetingsSeeder.php';
$content = file_get_contents($seederFile);

$logFile = __DIR__ . '/logpemesan_corrected.txt';
$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$newMeetingsPhp = "";

foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line) || $line === '.') continue;
    
    $parts = explode("\t", $line);
    if (count($parts) < 7) continue;
    
    $roomName = trim($parts[0]);
    $start = trim($parts[2]);
    $end = trim($parts[3]);
    $topic = trim($parts[4]);
    $organizerName = trim($parts[6]);
    
    // Find room
    $room = DB::table('rooms')->where('name', 'LIKE', "%{$roomName}%")->first();
    if (!$room) continue; // Should not happen as we verified
    
    // Find user
    $user = DB::table('users')->where('name', $organizerName)->first();
    // Fallback like search if exact match fails (e.g. casing)
    if (!$user) $user = DB::table('users')->where('name', 'LIKE', "%{$organizerName}%")->first();
    
    if (!$user) continue; // Should not happen
    
    // We can just use a fake 'user_id' that we map in NPK mapping?
    // OR since we modified the loop to use getUserIdFromOldId, we can just hack it:
    // We already have the REAL user_id.
    // But the loop does: $meeting['user_id'] = $this->getUserIdFromOldId($oldUserId);
    
    // So we should modify the loop to allowed 'pre-resolved' user_id?
    // Let's modify the loop logic in the file first?
    // OR we can add a 'skip_conversion' flag?
    
    // Let's modify the loop in the seeder file slightly using str_replace here
    // But easier: The getUserIdFromOldId checks $npkMapping[$oldUserId].
    // If we pass the REAL user_id and it's NOT in mapping, it falls back to Super Admin.
    // That's BAD.
    
    // So distinct strategy:
    // We used a customized loop in create_fixed_seeder.php:
    // $oldUserId = $meeting['user_id'];
    // $meeting['user_id'] = $this->getUserIdFromOldId($oldUserId);
    
    // Check if we can change logic to:
    // if (isset($meeting['is_new'])) { use provided user_id } else { convert }
    
    // Let's add this logic to seeder content first
}

// 1. Modify loop logic to handle new meetings
$oldLoop = "\$oldUserId = \$meeting['user_id'];
            \$meeting['user_id'] = \$this->getUserIdFromOldId(\$oldUserId);";

$newLoop = "if (isset(\$meeting['is_new']) && \$meeting['is_new']) {
                // New meeting, user_id is already correct
            } else {
                \$oldUserId = \$meeting['user_id'];
                \$meeting['user_id'] = \$this->getUserIdFromOldId(\$oldUserId);
            }";

$content = str_replace(
    '$meeting[\'user_id\'] = $this->getUserIdFromOldId($oldUserId);', 
    $newLoop, // This replace might fail if whitespace differs.
    $content
);

// Actually, create_fixed_seeder used specific string.
// Let's just do a rough replace of the loop we inserted.
// The loop was:
// foreach ($meetings as $meeting) {
//     $oldUserId = $meeting['user_id'];
//     $meeting['user_id'] = $this->getUserIdFromOldId($oldUserId);
//     ...

$content = preg_replace(
    '/foreach \(\$meetings as \$meeting\) \{\s*\$oldUserId = \$meeting\[\'user_id\'\];\s*\$meeting\[\'user_id\'\] = \$this->getUserIdFromOldId\(\$oldUserId\);/',
    "foreach (\$meetings as \$meeting) {\n            if (isset(\$meeting['is_new'])) {\n                // Keep existing user_id\n            } else {\n                \$oldUserId = \$meeting['user_id'];\n                \$meeting['user_id'] = \$this->getUserIdFromOldId(\$oldUserId);\n            }",
    $content
);


// 2. Generate new meetings array
foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line) || $line === '.') continue;
    
    $parts = explode("\t", $line);
    if (count($parts) < 7) continue;
    
    $roomName = trim($parts[0]);
    $start = trim($parts[2]);
    $end = trim($parts[3]);
    $topic = trim($parts[4]);
    $organizerName = trim($parts[6]);
    
    $room = DB::table('rooms')->where('name', 'LIKE', "%{$roomName}%")->first();
    $user = DB::table('users')->where('name', 'LIKE', "%{$organizerName}%")->first();

    if ($room && $user) {
        $started = Carbon::parse($start)->format('Y-m-d H:i:s');
        $ended = Carbon::parse($end)->format('Y-m-d H:i:s');
        $now = now()->format('Y-m-d H:i:s');
        
        $newMeetingsPhp .= "            [\n";
        $newMeetingsPhp .= "                'user_id' => {$user->id},\n";
        $newMeetingsPhp .= "                'room_id' => {$room->id},\n";
        $newMeetingsPhp .= "                'topic' => '" . addslashes($topic) . "',\n";
        $newMeetingsPhp .= "                'start_time' => '$started',\n";
        $newMeetingsPhp .= "                'end_time' => '$ended',\n";
        $newMeetingsPhp .= "                'status' => 'scheduled',\n";
        $newMeetingsPhp .= "                'created_at' => '$now',\n";
        $newMeetingsPhp .= "                'updated_at' => '$now',\n";
        $newMeetingsPhp .= "                'is_new' => true,\n";
        $newMeetingsPhp .= "            ],\n";
    }
}

// 3. Inject into array
// Find the last closing bracket of the array `];` before the run method closes?
// The array is `$meetings = [ ... ];`
// We can search for `    ];` at the end of definitions.
// Since the file structure is simple, we can search for the last occurrence of `];` before the `foreach` loop.

// A safer way:
$search = "            [\n                'user_id' => 4220,"; // Find the last item or just insert at start of array?
// Insert at start is easier: `$meetings = [`
$replace = "\$meetings = [\n$newMeetingsPhp";
$content = str_replace('$meetings = [', $replace, $content);

file_put_contents($seederFile, $content);
echo "Injected " . substr_count($newMeetingsPhp, 'user_id') . " new meetings into FixedMeetingsSeeder.php\n";
