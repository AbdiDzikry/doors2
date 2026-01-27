<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$users = DB::table('users')->where('name', 'LIKE', '%Ignatius%')->get();

foreach ($users as $user) {
    echo "Found: {$user->name} (NPK: {$user->npk}, ID: {$user->id})\n";
}

if ($users->isEmpty()) {
    echo "No user found with name like Ignatius.\n";
}
