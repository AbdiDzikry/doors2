<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Rap2hpoutre\FastExcel\FastExcel;

$user = User::first();
if ($user) {
    // Generate valid dummy data
    // We update Phone to a NEW value to test update
    $data = [
        [
            'NPK' => $user->npk,
            'Phone' => '08999999999', // New Phone
            'Email' => $user->email   // Same Email
        ]
    ];
    
    (new FastExcel(collect($data)))->export('dummy_users.xlsx');
    echo "Created dummy_users.xlsx regarding NPK: " . $user->npk;
} else {
    echo "No users found.";
}
