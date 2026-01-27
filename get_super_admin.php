<?php
// Script to get Super Admin NPK
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Find Super Admin
$superAdmin = DB::table('users')
    ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
    ->where('roles.name', 'Super Admin')
    ->where('model_has_roles.model_type', 'App\\Models\\User')
    ->select('users.id', 'users.name', 'users.npk')
    ->first();

if ($superAdmin) {
    echo "Super Admin found:\n";
    echo "ID: {$superAdmin->id}\n";
    echo "Name: {$superAdmin->name}\n";
    echo "NPK: {$superAdmin->npk}\n";
} else {
    echo "Super Admin not found. Using user ID 1 as fallback.\n";
    $user1 = DB::table('users')->where('id', 1)->first();
    if ($user1) {
        echo "User ID 1:\n";
        echo "Name: {$user1->name}\n";
        echo "NPK: {$user1->npk}\n";
    }
}
