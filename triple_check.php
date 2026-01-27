<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== TRIPLE CHECK REPORT ===\n\n";

// 1. Check for Super Admin Assignments (Fallback)
echo "1. CHECKING FOR MISSING USERS (Assigned to Super Admin - ID 1)\n";
$superAdminMeetings = DB::table('meetings')
    ->where('user_id', 1)
    ->whereBetween('start_time', ['2026-01-15 00:00:00', '2026-04-30 23:59:59'])
    ->get(); // Filter by range we touched

if ($superAdminMeetings->count() > 0) {
    echo "⚠️  WARNING: Found {$superAdminMeetings->count()} meetings assigned to Super Admin (ID 1).\n";
    echo "    These might be users not found in database:\n";
    foreach ($superAdminMeetings as $m) {
        echo "    - [{$m->start_time}] {$m->topic} (Room: {$m->room_id})\n";
    }
} else {
    echo "✅ PASSED: No suspicious Super Admin assignments found in this range.\n";
}

// 2. Check for Overlaps / Double Bookings
echo "\n2. CHECKING FOR OVERLAPPING MEETINGS\n";
$meetings = DB::table('meetings')
    ->whereBetween('start_time', ['2026-01-15 00:00:00', '2026-04-30 23:59:59'])
    ->where('status', '!=', 'cancelled')
    ->get();

$overlaps = 0;
foreach ($meetings as $m1) {
    foreach ($meetings as $m2) {
        if ($m1->id >= $m2->id) continue; // Avoid self-compare and duplicates
        if ($m1->room_id != $m2->room_id) continue; // Different rooms are fine
        
        $start1 = Carbon::parse($m1->start_time);
        $end1 = Carbon::parse($m1->end_time);
        $start2 = Carbon::parse($m2->start_time);
        $end2 = Carbon::parse($m2->end_time);
        
        // Overlap logic: (StartA < EndB) and (EndA > StartB)
        if ($start1 < $end2 && $end1 > $start2) {
            $overlaps++;
            $room = DB::table('rooms')->where('id', $m1->room_id)->first();
            $roomName = $room ? $room->name : "ID {$m1->room_id}";
            
            echo "🔴 CRITICAL CONFLICT DETECTED in $roomName:\n";
            echo "   A: [{$m1->id}] {$m1->topic} ({$m1->start_time} - {$m1->end_time})\n";
            echo "   B: [{$m2->id}] {$m2->topic} ({$m2->start_time} - {$m2->end_time})\n";
        }
    }
}

if ($overlaps == 0) {
    echo "✅ PASSED: No data overlapping found.\n";
} else {
    echo "\n❌ FAILED: Found $overlaps conflicts.\n";
}
