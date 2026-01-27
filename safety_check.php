<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== SAFETY SPOT CHECK ===\n";

$checks = [
    ['time' => '2026-03-11 13:00:00', 'topic' => 'Weekly CR SR', 'desc' => 'Dearsy (Weekly CR)'],
    ['time' => '2026-02-05 09:00:00', 'topic' => 'Konsultan ESG', 'desc' => 'Yuliyati (ESG)'],
    ['time' => '2026-01-29 08:00:00', 'topic' => 'Eksternal', 'desc' => 'Jihan (External)']
];

$allSafe = true;
foreach ($checks as $c) {
    $m = DB::table('meetings')
        ->where('start_time', $c['time'])
        ->where('topic', 'LIKE', "%{$c['topic']}%")
        ->first();
        
    if ($m) {
        $u = DB::table('users')->where('id', $m->user_id)->first();
        echo "✅ FOUND: {$c['desc']} - ID {$m->id} (User: {$u->name})\n";
    } else {
        echo "❌ MISSING: {$c['desc']}\n";
        $allSafe = false;
    }
}

if ($allSafe) echo "\nALL SYSTEMS GO. SAFE TO DEPLOY.\n";
else echo "\nABORT! DATA MISSING.\n";
