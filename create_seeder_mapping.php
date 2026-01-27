<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// 1. Parse Seeder for (Topic -> OldUserId)
$seederPath = __DIR__ . '/database/seeders/LegacyMeetingsJanMar2026Seeder.php';
$seederContent = file_get_contents($seederPath);

// Regex to capture user_id and topic in the array
// [
//    'user_id' => 1004,
//    ...
//    'topic' => 'SRME LCA Strategy',
// ]
// We assume they are strictly ordered? No, maybe not.
// We can match the whole block?
// Simpler: Extract all meetings as blocks, then parse inside.

preg_match_all('/\[\s*\'user_id\'\s*=>\s*(\d+),.*?\'topic\'\s*=>\s*\'(.*?)\',/ms', $seederContent, $matches, PREG_SET_ORDER);

$topicToOldId = [];
foreach ($matches as $match) {
    $id = $match[1];
    $topic = $match[2];
    $topicToOldId[trim($topic)] = $id;
}

echo "Found " . count($topicToOldId) . " topics in Seeder.\n";

// 2. Parse LogError for (Topic -> Name)
$logPath = __DIR__ . '/logerror.txt';
$logLines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$topicToName = [];
foreach ($logLines as $line) {
    $parts = explode("\t", $line);
    if (count($parts) < 7) continue;
    
    // Check if this is the "Error" section (lines > 13 usually)
    // The user said lines 1-10 are "Seharusnya".
    // We want the Correct mapping.
    // The "Wrong" mapping (lines 14+) has the Wrong names?
    // User said: "Seharusnya... Booked by IGNATIUS... tapi malah Faizal".
    // So the FIRST section is correct.
    
    // We can detect section change? or just take the first occurrence?
    // Let's take the first occurrence for each topic.
    
    $topic = trim($parts[4]);
    $name = trim($parts[6]);
    
    if (!isset($topicToName[$topic])) {
        $topicToName[$topic] = $name;
    }
}

echo "Found " . count($topicToName) . " topics in Log.\n";

// 3. Map OldId -> Name -> NPK
$mapping = [];
$missingUsers = [];

foreach ($topicToOldId as $topic => $oldId) {
    if (isset($topicToName[$topic])) {
        $name = $topicToName[$topic];
        
        // Find user by name
        $user = DB::table('users')->where('name', $name)->first();
        if (!$user) {
            // Try LIKE
            $user = DB::table('users')->where('name', 'LIKE', "%$name%")->first();
        }
        
        if ($user) {
            $mapping[$oldId] = $user->npk; // Store NPK
        } else {
            $missingUsers[$oldId] = $name;
        }
    }
}

// manually add overrides if needed?
// e.g. 531 -> Ignatius
if (!isset($mapping[531])) {
    // Try to find Ignatius
    $ignatius = DB::table('users')->where('name', 'LIKE', '%Ignatius%')->first();
    if ($ignatius) {
        $mapping[531] = $ignatius->npk;
    }
}

// 4. Output
echo "Mapping found for " . count($mapping) . " IDs.\n";
echo "Missing mapping for " . count($missingUsers) . " IDs.\n";

$export = "<?php\n\nreturn [\n";
foreach ($mapping as $id => $npk) {
    $export .= "    $id => '$npk',\n";
}
$export .= "];\n";

file_put_contents(__DIR__ . '/real_npk_mapping.php', $export);
echo "Saved to real_npk_mapping.php\n";

foreach ($missingUsers as $id => $name) {
    echo "Missing: ID $id ($name)\n";
}
