<?php

require __DIR__ . '/vendor/autoload.php';

use Carbon\Carbon;

$inputFile = 'logtanya.txt';
$outputFile = 'meetings_day2.csv';

if (!file_exists($inputFile)) {
    die("Error: Input file '$inputFile' not found.\n");
}

$lines = file($inputFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$csvData = [];

// Header
$csvData[] = ['room_name', 'topic_meeting', 'kind_meeting', 'start_time', 'end_time', 'pic_name'];

$roomMap = [
    'Ruang Raung' => 'Ruang Raung',
    'Ruang Kencana' => 'Ruang Kencana',
    'Ruang Arjuna' => 'Ruang Arjuno', // Normalized
    'Ruang Arjuno' => 'Ruang Arjuno',
    'Ruang Semeru' => 'Ruang Semeru',
    'Ruang Merbabu' => 'Ruang Merbabu',
    'Ruang Kerinci' => 'Ruang Kerinci',
    'Ruang Rinjani' => 'Ruang Rinjani',
    'Ruang Auditorium' => 'Auditorium',
    // Add others if needed
];

foreach ($lines as $line) {
    // Debug
    echo "Processing format check: " . substr($line, 0, 50) . "...\n";
    
    // Expected format: Room | Date | Start | End | Topic | Type | PIC | Status (Optional)
    // Try TAB first
    $parts = explode("\t", $line);
    
    // If not enough parts, try splitting by 4+ spaces (common in copy-paste)
    if (count($parts) < 6) {
        $parts = preg_split('/\s{2,}/', $line);
    }
    
    if (count($parts) < 6) {
        // Last resort: try single space? No, that breaks names. 
        // Skip invalid lines
        continue; 
    }

    $roomRaw = trim($parts[0]);
    $dateRaw = trim($parts[1]);
    $startRaw = trim($parts[2]); // Full datetime or just time? previous sample showed full datetime
    $endRaw = trim($parts[3]);
    $topic = trim($parts[4]);
    $type = trim($parts[5]);
    $pic = trim($parts[6]);
    
    // Map Room
    $room = $roomMap[$roomRaw] ?? $roomRaw;
    
    // Status Logic skipped for CSV (handled by importer)
    
    $csvData[] = [$room, $topic, $type, $startRaw, $endRaw, $pic];
}

$fp = fopen($outputFile, 'w');
foreach ($csvData as $row) {
    fputcsv($fp, $row);
}
fclose($fp);

echo "Converted " . count($csvData) . " lines to $outputFile\n";
