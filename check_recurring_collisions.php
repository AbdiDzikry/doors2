<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Meeting;

$logFile = 'storage/logs/import_collisions.log';
$content = file_get_contents($logFile);

// Regex to find "DB (Existing): ID=123"
preg_match_all('/DB \(Existing\): ID=(\d+)/', $content, $matches);
$ids = array_unique($matches[1]);

echo "Analyzing " . count($ids) . " colliding meetings...\n";

foreach ($ids as $id) {
    // We only care about checking if the EXISTING meeting is recurring
    $m = Meeting::find($id);
    if ($m) {
        $isRecurring = $m->meeting_type === 'recurring' ? 'YES (Recurring)' : 'NO (Single)';
        echo "ID: {$m->id} | Topic: {$m->topic} | Type: {$isRecurring} | User: " . ($m->user->name ?? 'Unk') . "\n";
    } else {
        echo "ID: $id | Not Found in DB (Maybe already deleted?)\n";
    }
}
