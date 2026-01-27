<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== DEEP VERIFICATION AUDIT ===\n";
echo "Comparing logpemesan_corrected.txt vs Database\n\n";

$logFile = __DIR__ . '/logpemesan_corrected.txt';
$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$metrics = [
    'total_source' => 0,
    'perfect_match' => 0,
    'missing' => 0,
    'organizer_mismatch' => 0,
    'fallback_warning' => 0 // Suspicious Super Admin assignments
];

foreach ($lines as $lineNum => $line) {
    // Skip likely headers or empty lines logic from previous scripts
    // Simple heuristic: must have tabs
    if (strpos($line, "\t") === false) continue;
    
    $parts = explode("\t", $line);
    if (count($parts) < 7) continue;

    $metrics['total_source']++;
    
    $roomName = trim($parts[0]);
    $date = trim($parts[1]);
    $startTime = trim($parts[2]);
    $topic = trim($parts[4]);
    $organizerName = trim($parts[6]);
    
    // Find in DB specifically by Topic and Start Time (strong identifiers)
    // Note: Topic might have been sanitized/escaped, checking broad match
    $meeting = DB::table('meetings')
        ->where('start_time', $startTime)
        ->where('topic', $topic) // Exact match first
        ->first();
        
    if (!$meeting) {
        $metrics['missing']++;
        echo "❌ MISSING: [$date] $topic ($organizerName)\n";
        continue;
    }
    
    // Check Organizer
    $dbUser = DB::table('users')->where('id', $meeting->user_id)->first();
    $dbUserName = $dbUser ? $dbUser->name : 'Unknown';
    $dbUserNpk = $dbUser ? $dbUser->npk : 'N/A';
    
    // Fuzzy matching for name
    $isNameMatch = (stripos($dbUserName, $organizerName) !== false) || (stripos($organizerName, $dbUserName) !== false);
    
    // Special check for Angelly
    if (stripos($organizerName, 'Angelly') !== false && $meeting->user_id == 1125) $isNameMatch = true; 
    
    // Special check for fallback to Super Admin (ID 1)
    if ($meeting->user_id == 1) {
        // Acceptable fallback?
        // Maybe "Internal" organizre? Or specific admin names.
        // User provided log usually has REAL names.
        if ($dbUserName == 'Super Admin') {
             $metrics['fallback_warning']++;
             echo "⚠️  FALLBACK WARNING: [$date] $topic\n";
             echo "    Expected: $organizerName\n";
             echo "    Actual: Super Admin (ID 1)\n\n";
             continue;
        }
    }
    
    if (!$isNameMatch) {
         $metrics['organizer_mismatch']++;
         echo "❌ ORGANIZER MISMATCH: [$date] $topic\n";
         echo "    Expected: $organizerName\n";
         echo "    Actual: $dbUserName (NPK: $dbUserNpk)\n\n";
    } else {
        $metrics['perfect_match']++;
    }
}

echo "\n=== SUMMARY ===\n";
echo "Total Source Records: {$metrics['total_source']}\n";
echo "✅ Perfect Matches:   {$metrics['perfect_match']}\n";
echo "❌ Missing in DB:     {$metrics['missing']}\n";
echo "❌ Org Mismatch:      {$metrics['organizer_mismatch']}\n";
echo "⚠️  Super Admin FB:    {$metrics['fallback_warning']}\n";

if ($metrics['missing'] == 0 && $metrics['organizer_mismatch'] == 0 && $metrics['fallback_warning'] == 0) {
    echo "\n🏆 VERIFICATION PASSED: All data is consistent and accurate.\n";
} else {
    echo "\n⚠️ VERIFICATION FAILED: See details above.\n";
}
