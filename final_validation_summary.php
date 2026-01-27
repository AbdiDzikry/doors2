<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$logFile = __DIR__ . '/logpemesan_corrected.txt';
$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

echo "=== FINAL VALIDATION REPORT ===\n\n";

$stats = ['total' => 0, 'valid' => 0, 'already_correct' => 0, 'errors' => 0, 'conflicts' => 0];

foreach ($lines as $lineNum => $line) {
    $line = trim($line);
    if (empty($line) || $line === '.') continue;
    
    $stats['total']++;
    $parts = explode("\t", $line);
    if (count($parts) < 7) continue;
    
    $roomName = trim($parts[0]);
    $startTime = trim($parts[2]);
    $topic = trim($parts[4]);
    $organizerName = trim($parts[6]);
    
    $room = DB::table('rooms')->where('name', 'LIKE', "%{$roomName}%")->first();
    $organizer = DB::table('users')->where('name', 'LIKE', "%{$organizerName}%")->first();
    
    if (!$room || !$organizer) {
        $stats['errors']++;
        continue;
    }
    
    $existing = DB::table('meetings')
        ->where('room_id', $room->id)
        ->where('start_time', $startTime)
        ->where('topic', $topic)
        ->first();
    
    if ($existing) {
        $stats['already_correct']++;
    } else {
        $stats['valid']++;
    }
}

echo "Total meetings: {$stats['total']}\n";
echo "✅ Already correct: {$stats['already_correct']}\n";
echo "🆕 Valid (can create): {$stats['valid']}\n";
echo "❌ Errors: {$stats['errors']}\n";
echo "🔴 Conflicts: {$stats['conflicts']}\n\n";

if ($stats['errors'] == 0) {
    echo "🎉 ALL DATA VALID! Ready to proceed with seeder.\n";
} else {
    echo "⚠️  Still have {$stats['errors']} errors to fix.\n";
}
