<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Room;
use App\Models\Meeting;

$u = User::where('name', 'LIKE', '%Ridwan Hadiwinata%')->first();
$r = Room::where('name', 'Ruang Merbabu')->first();

if ($u && $r) {
    Meeting::create([
        'user_id' => $u->id,
        'room_id' => $r->id,
        'topic' => 'Meeting KIDP',
        'start_time' => '2026-01-19 17:00:00',
        'end_time' => '2026-01-19 18:00:00',
        'status' => 'completed',
        'confirmation_status' => 'confirmed'
    ]);
    echo "Created Meeting KIDP for {$u->name}\n";
} else {
    echo "User or Room not found\n";
}
