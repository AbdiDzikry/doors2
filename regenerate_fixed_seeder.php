<?php
// Script to REGENERATE FixedMeetingsSeeder.php from scratch
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$sourceFile = __DIR__ . '/database/seeders/LegacyMeetingsJanMar2026Seeder_BACKUP.php';
$targetFile = __DIR__ . '/database/seeders/FixedMeetingsSeeder.php';
$logFile = __DIR__ . '/logpemesan_corrected.txt';

$content = file_get_contents($sourceFile);

// 1. Change Class Name
$content = str_replace('class LegacyMeetingsJanMar2026Seeder', 'class FixedMeetingsSeeder', $content);

// 2. Add NPK Mapping Property and Helper Method
$npkMapping = [
    1004 => '11230531',
    681 => '11220019',
    705 => '11220079',
    1179 => '11240175',
    352 => '11164043',
    1255 => '11250355',
    479 => '11185354',
    993 => '11230499',
    1610 => '31125379',
    517 => '11206120',
    450 => '11174947',
    344 => '11153962',
    1305 => '11940472',
    518 => '11206122',
    682 => '11220022',
    471 => '11185297',
    365 => '11164138',
    672 => '11210580',
    1016 => '11230580',
    786 => '11220821',
    488 => '11195585',
    531 => '11210064',
    1121 => '11240076',
    1335 => '99143739',
    976 => '11230399',
    1186 => '11240195',
    1871 => '31125576',
    743 => '11220177',
    373 => '11164217',
    494 => '11195719',
];

$logicCode = "
    // NPK mapping from old database
    private \$npkMapping = " . var_export($npkMapping, true) . ";
    private \$superAdminNpk = 'admin123';
    
    private function getUserIdFromOldId(\$oldUserId) {
        \$npk = \$this->npkMapping[\$oldUserId] ?? null;
        if (!\$npk) {
            \$superAdmin = DB::table('users')->where('npk', \$this->superAdminNpk)->first();
            return \$superAdmin ? \$superAdmin->id : 1;
        }
        \$user = DB::table('users')->where('npk', \$npk)->first();
        if (!\$user) {
            \$superAdmin = DB::table('users')->where('npk', \$this->superAdminNpk)->first();
            return \$superAdmin ? \$superAdmin->id : 1;
        }
        return \$user->id;
    }
";

$content = preg_replace('/class FixedMeetingsSeeder extends Seeder\s*\{/', "class FixedMeetingsSeeder extends Seeder\n{\n$logicCode", $content);

// 3. Prepare New Meetings Array
$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$newMeetingsPhp = "";

foreach ($lines as $line) {
    // ... same log parsing logic ...
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
    // Fallback if needed
    if (!$user && isset($organizerName) && $organizerName == 'Wisnu') { 
         // Manual correction if still 'Wisnu' in file (though we updated it)
         $user = DB::table('users')->where('npk', '11220807')->first();
    }

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
        // 'is_new' => true, // NO! We handle this by putting them in the array directly with correct IDs
        // Actually, if we put them in the same array, the loop will try to convert them.
        // So we DO need a flag, OR we put them in a separate array.
        // Let's use the flag and handle it correctly this time.
        $newMeetingsPhp .= "                'is_new' => true,\n";
        $newMeetingsPhp .= "            ],\n";
    }
}

// 4. Inject New Meetings into Array
$replace = "\$meetings = [\n$newMeetingsPhp";
$content = str_replace('$meetings = [', $replace, $content);

// 5. Replace Loop Logic with Correct Version
$cleanLoopLogic = "
        // Process meetings
        foreach (\$meetings as \$meeting) {
            if (isset(\$meeting['is_new']) && \$meeting['is_new']) {
                unset(\$meeting['is_new']);
                // user_id is already correct
            } else {
                \$oldUserId = \$meeting['user_id'];
                \$meeting['user_id'] = \$this->getUserIdFromOldId(\$oldUserId);
            }
            
            if (!isset(\$meeting['created_at'])) \$meeting['created_at'] = now();
            if (!isset(\$meeting['updated_at'])) \$meeting['updated_at'] = now();
            
            DB::table('meetings')->insert(\$meeting);
        }
        
        // BACKFILL: Insert Organizer as Participant
        \$insertedMeetings = DB::table('meetings')
            ->whereBetween('start_time', ['2026-01-15 00:00:00', '2026-03-31 23:59:59'])
            ->get();

        \$participants = [];
        foreach (\$insertedMeetings as \$meeting) {
            \$participants[] = [
                'meeting_id' => \$meeting->id,
                'participant_type' => 'App\\\\Models\\\\User',
                'participant_id' => \$meeting->user_id,
                'status' => \$meeting->status === 'completed' ? 'attended' : 'confirmed',
                'is_pic' => 1,
                'checked_in_at' => \$meeting->status === 'completed' ? \$meeting->start_time : null,
                'attended_at' => \$meeting->status === 'completed' ? \$meeting->start_time : null,
                'created_at' => \$meeting->created_at,
                'updated_at' => \$meeting->updated_at,
            ];
        }

        if (!empty(\$participants)) {
            DB::table('meeting_participants')->insert(\$participants);
        }
";

// Remove original insert
$content = preg_replace('/DB::table\(\'meetings\'\)->insert\(\$meetings\);/', '', $content);
// Append new loop logic at end of run method (before closing brace)
$content = preg_replace('/    \}\n\}/', "    $cleanLoopLogic\n    }\n}", $content);


// 6. Ensure Log facade
if (!str_contains($content, 'use Illuminate\Support\Facades\Log;')) {
    $content = str_replace('use Illuminate\Support\Facades\DB;', "use Illuminate\Support\Facades\DB;\nuse Illuminate\Support\Facades\Log;", $content);
}

file_put_contents($targetFile, $content);
echo "Regenerated FixedMeetingsSeeder.php successfully!\n";
