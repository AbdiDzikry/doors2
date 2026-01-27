<?php
// Script to REGENERATE FixedMeetingsSeeder.php with CONFLICT RESOLUTION
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$sourceFile = __DIR__ . '/database/seeders/LegacyMeetingsJanMar2026Seeder_BACKUP.php';
$targetFile = __DIR__ . '/database/seeders/FixedMeetingsSeeder.php';
$logFile = __DIR__ . '/logpemesan_final.txt'; // Using sanitized file

// 1. Extract Legacy Meetings from Backup using Regex or Require?
// Require is risky if class name check fails. Let's parse the array structure manually or use a helper.
// Actually, easier: Read the file, verify format, extract the big array.
$content = file_get_contents($sourceFile);
// Extract array using regex
if (preg_match('/\$meetings\s*=\s*(\[[\s\S]*?\]);/', $content, $matches)) {
    // We need to be careful with eval. Let's assume syntax is valid PHP array.
    // Adding 'return' to eval it safely as an expression
    $arrayContent = "return " . $matches[1] . ";";
    try {
        $legacyMeetings = eval($arrayContent);
    } catch (\Throwable $e) {
        die("Error parsing legacy array: " . $e->getMessage());
    }
} else {
    die("Could not find meetings array in source file");
}

// 2. Parse New Meetings
$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$newMeetings = [];

foreach ($lines as $line) {
    // ... Parsing logic ...
    $line = trim($line);
    if (strpos($line, "\t") === false) continue;
    $parts = explode("\t", $line);
    if (count($parts) < 7) continue;
    
    $roomName = trim($parts[0]);
    $start = trim($parts[2]); // Y-m-d H:i:s
    $end = trim($parts[3]);
    $topic = trim($parts[4]);
    $organizerName = trim($parts[6]);
    
    $room = DB::table('rooms')->where('name', 'LIKE', "%{$roomName}%")->first();
    $user = DB::table('users')->where('name', 'LIKE', "%{$organizerName}%")->first();
    
    // Check specific NPKs if name lookup fails
    if (!$user) {
        if (stripos($organizerName, 'Angelly') !== false) $user = DB::table('users')->where('npk', '11240080')->first(); // Angelly
        if (stripos($organizerName, 'Wisnu') !== false) $user = DB::table('users')->where('npk', '11220807')->first(); // Wisnu
        if (stripos($organizerName, 'Alpin') !== false) $user = DB::table('users')->where('npk', '11210003')->first(); // Alpin
        if (stripos($organizerName, 'Tri Witanto') !== false || stripos($organizerName, 'Aldian') !== false) $user = DB::table('users')->where('npk', '11230711')->first(); // Tri Witanto
    }
    
    if ($room && $user) {
        $started = Carbon::parse($start)->format('Y-m-d H:i:s');
        $ended = Carbon::parse($end)->format('Y-m-d H:i:s');
        $now = now()->format('Y-m-d H:i:s');
        
        $newMeetings[] = [
            'user_id' => $user->id,
            'room_id' => $room->id,
            'topic' => $topic,
            'start_time' => $started,
            'end_time' => $ended,
            'status' => 'scheduled',
            'created_at' => $now,
            'updated_at' => $now,
            'is_new' => true // Flag to skip conversion
        ];
    }
}

// 3. CONFLICT RESOLUTION
// Filter out Legacy meetings that conflict with New meetings
$finalLegacy = [];
$droppedCount = 0;

foreach ($legacyMeetings as $legacy) {
    $lStart = Carbon::parse($legacy['start_time']);
    $lEnd = Carbon::parse($legacy['end_time']);
    $lRoom = $legacy['room_id'];
    
    $conflict = false;
    foreach ($newMeetings as $new) {
        $nStart = Carbon::parse($new['start_time']);
        $nEnd = Carbon::parse($new['end_time']);
        $nRoom = $new['room_id'];
        
        if ($lRoom == $nRoom) {
            // Check overlap: StartA < EndB && EndA > StartB
            if ($lStart < $nEnd && $lEnd > $nStart) {
                $conflict = true;
                break;
            }
            // Check identical topic (handle shifted times)
            // Use case-insensitive check
            if (strcasecmp(trim($legacy['topic']), trim($new['topic'])) === 0) {
                 $conflict = true;
                 echo "Resolved Topic Conflict: {$legacy['topic']} (Legacy) vs {$new['topic']} (New)\n";
                 break;
            }
        }
    }
    
    if (!$conflict) {
        $finalLegacy[] = $legacy;
    } else {
        $droppedCount++;
    }
}

echo "Conflict Resolution:\n";
echo "- Original Legacy: " . count($legacyMeetings) . "\n";
echo "- Drop Conflicts: $droppedCount\n";
echo "- New Meetings: " . count($newMeetings) . "\n";
echo "- Final Combined: " . (count($finalLegacy) + count($newMeetings)) . "\n";

// 4. Generate PHP Code
$allMeetings = array_merge($finalLegacy, $newMeetings);

// Helper to export array nicely
function exportArray($arr) {
    $out = "[\n";
    foreach ($arr as $item) {
        $out .= "            [\n";
        foreach ($item as $k => $v) {
            if (is_int($v) || is_bool($v)) $out .= "                '$k' => $v,\n";
            else $out .= "                '$k' => '" . addslashes($v) . "',\n";
        }
        $out .= "            ],\n";
    }
    $out .= "        ]";
    return $out;
}

$meetingsCode = exportArray($allMeetings);

// 5. Template for Seeder
$seederTemplate = <<<EOT
<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixedMeetingsSeeder extends Seeder
{
    // NPK mapping
    private \$npkMapping = [
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

    public function run(): void
    {
        // Clear range but preserve unaffected data if needed
        // For safety, let's clear our target range completely
        DB::table('meetings')
            ->whereBetween('start_time', ['2026-01-15 00:00:00', '2026-04-30 23:59:59'])
            ->delete();

        \$meetings = $meetingsCode;

        // Process meetings and insert
        foreach (\$meetings as \$meeting) {
            if (isset(\$meeting['is_new']) && \$meeting['is_new']) {
                unset(\$meeting['is_new']);
                // user_id already verified correct
            } else {
                \$oldUserId = \$meeting['user_id'];
                \$newId = \$this->getUserIdFromOldId(\$oldUserId);
                // CLEAN POLICY: Skip if falls back to Super Admin (ID 1)
                if (\$newId == 1) continue; 
                \$meeting['user_id'] = \$newId;
            }
            
            DB::table('meetings')->insert(\$meeting);
        }

        // BACKFILL: Insert Organizer as Participant
        \$insertedMeetings = DB::table('meetings')
            ->whereBetween('start_time', ['2026-01-15 00:00:00', '2026-04-30 23:59:59'])
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
    }
}
EOT;

file_put_contents($targetFile, $seederTemplate);
echo "Regenerated FixedMeetingsSeeder.php with CONFLICT RESOLUTION successfully!\n";
