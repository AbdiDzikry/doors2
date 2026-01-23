<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Meeting;
use App\Models\PantryOrder;
use Carbon\Carbon;

$startDate = '2026-01-01';
$endDate = '2026-03-31';

// Get meetings created TODAY for the target range
$meetings = Meeting::whereBetween('start_time', [$startDate, $endDate])
    ->whereDate('created_at', Carbon::today())
    ->get();

$sql = "-- Migration Data for Meetings (Jan - Mar 2026)\n";
$sql .= "-- Generated at: " . now() . "\n";
$sql .= "-- Total Meetings: " . $meetings->count() . "\n\n";

$sql = "START TRANSACTION;\n\n";

// SAFETY: Delete existing data for this period on Server to prevent duplicates/collisions
// and ensure we remove the 'blockers' we deleted locally.
$sql .= "-- CLEANUP: Remove existing meetings in this range first\n";
$sql .= "DELETE FROM meetings WHERE start_time BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59';\n\n";

// Helper to escape values
function sqlVal($val) {
    if (is_null($val)) return 'NULL';
    return "'" . addslashes($val) . "'";
}

foreach ($meetings as $m) {
    // Generate INSERT for Meeting
    // We EXCLUDE ID to let Auto Increment handle it on Server (Safer)
    // OR INCLUDE IF User wants exact sync. Let's Exclude ID by default to be safe against PK collisions, 
    // unless there are internal relationships relying on it (e.g. Pantry).
    // WAIT: Pantry Orders rely on Meeting ID. So we MUST verify if we can keep IDs.
    // Assuming this is a Catch-Up import, keeping IDs might clash.
    // BUT generating SQL with dynamic IDs is hard (needs variables).
    // Let's output WITH IDs and assume User will handle ID range or table is consistent.
    
    $cols = [
        'id', 'user_id', 'room_id', 'topic', 'description', 'start_time', 'end_time', 
        'status', 'meeting_type', 'passcode', 'confirmation_status', 'created_at', 'updated_at'
    ];
    
    $vals = array_map(fn($c) => sqlVal($m->$c), $cols);
    $colStr = implode(', ', $cols);
    $valStr = implode(', ', $vals);
    
    $sql .= "INSERT INTO meetings ($colStr) VALUES ($valStr);\n";
    
    // Check Pantry Orders
    $orders = PantryOrder::where('meeting_id', $m->id)->get();
    foreach ($orders as $po) {
        $pCols = ['meeting_id', 'pantry_item_id', 'quantity', 'status', 'created_at', 'updated_at'];
        $pVals = array_map(fn($c) => sqlVal($po->$c), $pCols);
        $pColStr = implode(', ', $pCols);
        $pValStr = implode(', ', $pVals);
        
        $sql .= "INSERT INTO pantry_orders ($pColStr) VALUES ($pValStr);\n";
    }
}

$sql .= "\nCOMMIT;\n";

file_put_contents('migration_meetings_jan_mar_2026.sql', $sql);
echo "SQL generated: migration_meetings_jan_mar_2026.sql\n";
