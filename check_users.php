<?php

use App\Models\User;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$namesToCheck = [
    'ALFONSINE', 
    'Archadius', 
    'Esi Lacosta',
    'Imam Khanafi', 
    'Jihan', 
    'RANGGA', 
    'SESILIA'
];

echo "Checking Users...\n";
foreach ($namesToCheck as $name) {
    // Try Case Insensitive LIKE
    $user = User::where('name', 'LIKE', "%{$name}%")->first();
    if ($user) {
        echo "[FOUND] {$name} -> ID: {$user->id}, Name: {$user->name}, NPK: {$user->npk}\n";
    } else {
        echo "[MISSING] {$name}\n";
    }
}
