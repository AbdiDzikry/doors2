<?php
// Script to find correct NPK for expected user names
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Expected names from logerror.txt
$expectedNames = [
    'IGNATIUS BENNY SAPUTRO',
    'Eka Wulandari',
    'Fajar Faqih',
    'ISMA SEPTIANI',
    'Jihan',
    'Muhammad Alpin Dwizahra',
    'Ricky',
    'Tri Witanto Aldian',
    'Andreas Calvin Gotandra',
    'Yosa Ramdani',
    'Lukman Hawari Pratama',
    'Fatkhul Anam Ma\'Arif'
];

echo "Finding correct NPK for expected user names...\n\n";
echo "Name => NPK => user_id\n";
echo str_repeat("-", 80) . "\n";

$correctMapping = [];
foreach ($expectedNames as $name) {
    $user = DB::table('users')->where('name', 'LIKE', "%{$name}%")->first();
    if ($user) {
        echo sprintf("%-35s => %-15s => %d\n", $user->name, $user->npk, $user->id);
        $correctMapping[$name] = [
            'npk' => $user->npk,
            'user_id' => $user->id,
            'actual_name' => $user->name
        ];
    } else {
        echo sprintf("%-35s => NOT FOUND\n", $name);
    }
}

echo "\n" . str_repeat("-", 80) . "\n";
echo "Total users found: " . count($correctMapping) . "\n";

// Save correct mapping
file_put_contents('correct_npk_mapping.json', json_encode($correctMapping, JSON_PRETTY_PRINT));
echo "\nCorrect mapping saved to correct_npk_mapping.json\n";
