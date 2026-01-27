<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;



require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$csvFile = __DIR__ . '/users.csv';

if (!file_exists($csvFile)) {
    die("Users CSV file not found at: $csvFile\n");
}

echo "Reading users.csv...\n";

// Use simple file reading if League/Csv not installed or to verify headers
$handle = fopen($csvFile, 'r');
$headers = fgetcsv($handle);
// map headers: _id -> id
$headerMap = array_flip($headers);

$batch = [];
$count = 0;

// Disable foreign keys
Schema::disableForeignKeyConstraints();

// Optional: Truncate current users to avoid conflicts if they want a full restore
// DB::table('users')->truncate(); 

while (($data = fgetcsv($handle)) !== false) {
    // Map CSV columns to default specific user table columns
    // We need to be careful about column names.
    
    // Helper to get value
    $val = function($col) use ($data, $headerMap) {
        return isset($headerMap[$col]) ? ($data[$headerMap[$col]] ?: null) : null;
    };

    $user = [
        'id' => $val('_id'),
        'name' => $val('full_name') ?? $val('username'),
        'email' => $val('email'),
        'password' => $val('password') ?: bcrypt('password'), // fallback
        'npk' => $val('npk'),
        'phone' => $val('phone'),
        'position' => $val('position'),
        'division' => $val('division'),
        'department' => $val('department'),
        'created_at' => $val('created_at'),
        'updated_at' => $val('updated_at'),
        // Add other necessary defaults
        'email_verified_at' => now(),
    ];
    
    // Remove nulls if keys don't exist in DB schema? 
    // Safer to just upsert.
    
    $batch[] = $user;
    
    if (count($batch) >= 100) {
        DB::table('users')->upsert($batch, ['id'], ['name', 'email', 'npk', 'phone', 'position', 'division', 'department', 'password', 'created_at', 'updated_at']);
        $batch = [];
        echo ".";
    }
    $count++;
}

if (!empty($batch)) {
    DB::table('users')->upsert($batch, ['id'], ['name', 'email', 'npk', 'phone', 'position', 'division', 'department', 'password', 'created_at', 'updated_at']);
}

Schema::enableForeignKeyConstraints();

echo "\nRestored $count users successfully.\n";
