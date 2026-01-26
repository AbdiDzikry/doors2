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
    // Expected format: Room | Date | Start | End | Topic | Type | PIC | Status (Optional)
    // Delimiter seems to be TAB or multiple spaces? Assuming TAB based on previous interactions, but let's check.
    // Actually the user pastes raw text which usually is tab separated from Excel/Sheets.
    
    $parts = explode("\t", $line);
    
    // Fallback if not tab-separated (just in case)
    if (count($parts) < 6) {
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
