<?php

// Input and Output Files
$inputFile = 'logtanya.txt';
$outputFile = 'meetings_day2.csv';

// Read raw file
$rawContent = file_get_contents($inputFile);
$lines = explode("\n", $rawContent);

$csvData = [];
// Standard Headers expected by ImportLegacyMeetings
$headers = ['room_name', 'topic_meeting', 'kind_meeting', 'start_time', 'end_time', 'pic_name'];

$fp = fopen($outputFile, 'w');
fputcsv($fp, $headers);

foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line)) continue;
    
    // Split by tab or multiple spaces
    // Based on user sample: Room Name [TAB] Detail ...
    $parts = preg_split('/\t+/', $line);
    
    // Skip header lines or empty lines
    if (strpos($line, 'Room Name') !== false || strpos($line, 'Date') !== false) continue;
    
    // Expected format based on user file:
    // 0: Room Name (e.g. Ruang Auditorium)
    // 1: Date (2026-01-15) - We ignore this, rely on Start Time which has date
    // 2: Start Time (2026-01-15 17:30:00)
    // 3: End Time (2026-01-15 20:00:00)
    // 4: Jenis Meeting (Mat Pilates)
    // 5: Topik Meeting (Mat Pilates)
    // 6: PIC Name (ALFONSINE...)
    // 7: Status (1) - Ignored
    
    if (count($parts) >= 7) {
        $data = [
            'room_name' => trim($parts[0]),
            'topic_meeting' => trim($parts[5]), // Index 5 is Topik
            'kind_meeting' => trim($parts[4]), // Index 4 is Jenis
            'start_time' => trim($parts[2]),
            'end_time' => trim($parts[3]),
            'pic_name' => trim($parts[6])
        ];
        
        fputcsv($fp, $data);
    }
}

fclose($fp);

echo "Conversion complete. standard CSV created at: $outputFile\n";
