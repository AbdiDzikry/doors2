<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$startDate = '2026-01-15 00:00:00';
$endDate = '2026-03-31 23:59:59';

$meetings = DB::table('meetings')
    ->whereBetween('start_time', [$startDate, $endDate])
    ->orderBy('start_time')
    ->get();

$sql = "START TRANSACTION;\n\n";
$sql .= "-- CLEANUP: Remove existing meetings in this range first\n";
$sql .= "DELETE FROM meetings WHERE start_time BETWEEN '$startDate' AND '$endDate';\n\n";

foreach ($meetings as $meeting) {
    $vals = [];
    $cols = [];
    foreach ($meeting as $key => $value) {
        $cols[] = $key;
        if (is_null($value)) {
            $vals[] = "NULL";
        } elseif (is_numeric($value) && $key !== 'topic') { 
            $vals[] = "'$value'"; 
        } else {
             $safeVal = str_replace("'", "\'", $value); 
             $vals[] = "'$safeVal'";
        }
    }
    
    $colString = implode(', ', $cols);
    $valString = implode(', ', $vals);
    
    $sql .= "INSERT INTO meetings ($colString) VALUES ($valString);\n";

    // Participant (Organizer)
    $partCols = ['meeting_id', 'participant_type', 'participant_id', 'status', 'is_pic', 'checked_in_at', 'attended_at', 'created_at', 'updated_at'];
    $partVals = [
        $meeting->id,
        "'App\\\Models\\\User'", // Escape backslashes for SQL
        $meeting->user_id,
        $meeting->status === 'completed' ? "'attended'" : "'confirmed'",
        1,
        $meeting->status === 'completed' ? "'{$meeting->start_time}'" : "NULL",
        $meeting->status === 'completed' ? "'{$meeting->start_time}'" : "NULL",
        "'{$meeting->created_at}'",
        "'{$meeting->updated_at}'"
    ];
    $partColString = implode(', ', $partCols);
    $partValString = implode(', ', $partVals);

    $sql .= "INSERT INTO meeting_participants ($partColString) VALUES ($partValString);\n";
}

$sql .= "\nCOMMIT;\n";

file_put_contents('migration_meetings_jan_mar_2026.sql', $sql);
echo "SQL script generated: migration_meetings_jan_mar_2026.sql\n";
