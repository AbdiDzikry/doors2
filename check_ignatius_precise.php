<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$user = DB::table('users')->where('name', 'Ignatius Benny Saputro')->first();
if ($user) {
    echo "Strict Match: {$user->name} => {$user->npk}\n";
} else {
    $users = DB::table('users')->where('name', 'LIKE', '%Ignatius%')->get();
    foreach($users as $u) {
        echo "Like Match: {$u->name} => {$u->npk}\n";
    }
}
