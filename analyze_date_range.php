<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Carbon\Carbon;

$logFile = __DIR__ . '/logpemesan_corrected.txt';
$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$dates = [];
$meetings = [];

foreach ($lines as $lineNum => $line) {
    $line = trim($line);
    if (empty($line) || $line === '.') continue;
    
    $parts = explode("\t", $line);
    if (count($parts) < 7) continue;
    
    $roomName = trim($parts[0]);
    $date = trim($parts[1]);
    $startTime = trim($parts[2]);
    $topic = trim($parts[4]);
    $organizerName = trim($parts[6]);
    
    $dates[] = $date;
    $meetings[] = [
        'date' => $date,
        'room' => $roomName,
        'topic' => $topic,
        'organizer' => $organizerName,
        'start_time' => $startTime
    ];
}

$uniqueDates = array_unique($dates);
sort($uniqueDates);

echo "=== RENTANG DATA PEMESANAN ===\n\n";
echo "Total meetings: " . count($meetings) . "\n";
echo "Tanggal mulai: " . min($uniqueDates) . "\n";
echo "Tanggal akhir: " . max($uniqueDates) . "\n";
echo "Total hari: " . count($uniqueDates) . " hari\n\n";

echo "=== BREAKDOWN PER TANGGAL ===\n";
foreach ($uniqueDates as $date) {
    $count = count(array_filter($meetings, function($m) use ($date) {
        return $m['date'] === $date;
    }));
    echo "$date: $count meeting(s)\n";
}

echo "\n=== DETAIL MEETINGS ===\n";
foreach ($meetings as $idx => $m) {
    echo sprintf("%2d. %s | %s | %s | %s\n", 
        $idx + 1, 
        $m['date'], 
        $m['room'], 
        substr($m['topic'], 0, 30), 
        $m['organizer']
    );
}
