<?php
// Script to extract NPK mapping from production database
// Run with: php extract_npk_mapping.php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Get all unique user_ids from the seeder
$userIds = [
    1004, 681, 705, 1179, 352, 1255, 479, 993, 1610, 517, 450, 344, 1305, 518, 682, 471, 365, 672, 1016, 786, 488, 531, 1121, 1335, 976, 1186, 1871, 743, 373, 494
];

echo "Extracting NPK mapping from production database...\n\n";
echo "user_id => NPK => Name\n";
echo str_repeat("-", 80) . "\n";

$mapping = [];
foreach ($userIds as $userId) {
    $user = DB::table('users')->where('id', $userId)->first();
    if ($user) {
        echo sprintf("%4d => %-15s => %s\n", $userId, $user->npk, $user->name);
        $mapping[$userId] = [
            'npk' => $user->npk,
            'name' => $user->name
        ];
    } else {
        echo sprintf("%4d => NOT FOUND\n", $userId);
    }
}

echo "\n" . str_repeat("-", 80) . "\n";
echo "Total users found: " . count($mapping) . "\n";

// Save mapping to JSON file for reference
file_put_contents('npk_mapping.json', json_encode($mapping, JSON_PRETTY_PRINT));
echo "\nMapping saved to npk_mapping.json\n";
