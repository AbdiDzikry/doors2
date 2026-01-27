<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$logFile = __DIR__ . '/logpemesan.txt';
$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$report = [
    'total' => 0,
    'valid' => 0,
    'already_correct' => 0,
    'wrong_organizer' => 0,
    'conflicts' => 0,
    'errors' => 0,
    'details' => []
];

foreach ($lines as $lineNum => $line) {
    $line = trim($line);
    if (empty($line) || $line === '.') continue;
    
    $report['total']++;
    $parts = explode("\t", $line);
    if (count($parts) < 7) {
        $report['errors']++;
        $report['details'][] = [
            'line' => $lineNum + 1,
            'status' => 'ERROR',
            'message' => 'Format tidak valid'
        ];
        continue;
    }
    
    $roomName = trim($parts[0]);
    $startTime = trim($parts[2]);
    $endTime = trim($parts[3]);
    $topic = trim($parts[4]);
    $organizerName = trim($parts[6]);
    
    // Find room
    $room = DB::table('rooms')->where('name', 'LIKE', "%{$roomName}%")->first();
    if (!$room) {
        $report['errors']++;
        $report['details'][] = [
            'line' => $lineNum + 1,
            'status' => 'ERROR',
            'message' => "Ruangan tidak ditemukan: $roomName"
        ];
        continue;
    }
    
    // Find organizer
    $organizer = DB::table('users')->where('name', 'LIKE', "%{$organizerName}%")->first();
    if (!$organizer) {
        $report['errors']++;
        $report['details'][] = [
            'line' => $lineNum + 1,
            'status' => 'ERROR',
            'message' => "User tidak ditemukan: $organizerName",
            'topic' => $topic
        ];
        continue;
    }
    
    // Check existing
    $existing = DB::table('meetings')
        ->where('room_id', $room->id)
        ->where('start_time', $startTime)
        ->where('topic', $topic)
        ->first();
    
    if ($existing) {
        $existingUser = DB::table('users')->where('id', $existing->user_id)->first();
        if ($existingUser && stripos($existingUser->name, $organizerName) !== false) {
            $report['already_correct']++;
            $report['details'][] = [
                'line' => $lineNum + 1,
                'status' => 'ALREADY_CORRECT',
                'topic' => $topic,
                'organizer' => $organizerName,
                'room' => $roomName
            ];
        } else {
            $report['wrong_organizer']++;
            $report['details'][] = [
                'line' => $lineNum + 1,
                'status' => 'WRONG_ORGANIZER',
                'topic' => $topic,
                'expected_organizer' => $organizerName,
                'current_organizer' => $existingUser ? $existingUser->name : 'Unknown',
                'room' => $roomName,
                'meeting_id' => $existing->id
            ];
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
        $report['conflicts']++;
        foreach ($conflicts as $conflict) {
            $conflictUser = DB::table('users')->where('id', $conflict->user_id)->first();
            $report['details'][] = [
                'line' => $lineNum + 1,
                'status' => 'CONFLICT',
                'topic' => $topic,
                'organizer' => $organizerName,
                'room' => $roomName,
                'conflict_with' => $conflict->topic,
                'conflict_organizer' => $conflictUser ? $conflictUser->name : 'Unknown',
                'conflict_time' => $conflict->start_time . ' - ' . $conflict->end_time
            ];
        }
        continue;
    }
    
    $report['valid']++;
    $report['details'][] = [
        'line' => $lineNum + 1,
        'status' => 'VALID',
        'topic' => $topic,
        'organizer' => $organizerName,
        'room' => $roomName,
        'time' => $startTime . ' - ' . $endTime
    ];
}

file_put_contents('validation_report.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Report saved to validation_report.json\n";
echo "Total: {$report['total']}\n";
echo "Valid (can be created): {$report['valid']}\n";
echo "Already correct: {$report['already_correct']}\n";
echo "Wrong organizer (needs fix): {$report['wrong_organizer']}\n";
echo "Conflicts: {$report['conflicts']}\n";
echo "Errors: {$report['errors']}\n";
