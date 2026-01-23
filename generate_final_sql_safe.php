<?php

use App\Models\Meeting;
use Illuminate\Support\Facades\File;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$startDate = '2026-01-15 00:00:00';
$endDate = '2026-03-31 23:59:59';

$meetings = Meeting::whereBetween('start_time', [$startDate, $endDate])
    ->orderBy('start_time')
    ->get();

$sql = "START TRANSACTION;\n\n";
$sql .= "-- CLEANUP: Remove existing meetings in this range first\n";
$sql .= "DELETE FROM meetings WHERE start_time BETWEEN '2026-01-01 00:00:00' AND '2026-03-31 23:59:59';\n\n";

foreach ($meetings as $meeting) {
    // Exclude 'description' and 'created_by' as they don't exist in the verified schema
    // Explicitly listing valid columns based on check_schema.php output
    /*
    [0] => id
    [1] => user_id
    [2] => room_id
    [3] => topic
    [4] => start_time
    [5] => end_time
    [6] => status
    [7] => meeting_type .. etc
    */
    
    $cols = [
        'id', 'user_id', 'room_id', 'topic', 'start_time', 'end_time', 
        'status', 'meeting_type', 'passcode', 'confirmation_status', 
        'created_at', 'updated_at'
    ];
    
    $vals = [];
    foreach ($cols as $col) {
        $val = $meeting->$col;
        if ($val === null) {
            $vals[] = "NULL";
        } else {
            $val = addslashes($val);
            $vals[] = "'$val'";
        }
    }
    
    $colString = implode(', ', $cols);
    $valString = implode(', ', $vals);
    
    $sql .= "INSERT INTO meetings ($colString) VALUES ($valString);\n";
}

$sql .= "\nCOMMIT;\n";

File::put('migration_meetings_jan_mar_2026.sql', $sql);
echo "Safe SQL script generated successfully.";
