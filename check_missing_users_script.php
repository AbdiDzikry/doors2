<?php

use App\Models\User;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$file = 'meetings_day2.csv';
$handle = fopen($file, 'r');
$header = fgetcsv($handle);
$colMap = array_flip($header);

$missingUsers = [];
$foundUsers = [];

while (($row = fgetcsv($handle)) !== false) {
    // Assuming CSV has 'pic_name' as the last column or we find it by name
    // Based on user file: room_name,topic,kind,start,end,pic_name
    $name = $row[5] ?? null; 
    
    if (!$name) continue;
    
    $cleanName = trim($name);
    
    if (in_array($cleanName, $foundUsers) || in_array($cleanName, $missingUsers)) continue;

    // Try finding logic same as import script
    $user = User::where('name', 'LIKE', $cleanName)->first();
    if (!$user) {
         $firstName = explode(' ', $cleanName)[0];
         if (strlen($firstName) > 3) {
             $user = User::where('name', 'LIKE', "{$firstName}%")->first();
         }
    }

    if ($user) {
        $foundUsers[] = $cleanName;
        echo "Mapped '$cleanName' -> '{$user->name}'\n";
    } else {
        $missingUsers[] = $cleanName;
        echo "MISSING '$cleanName'\n";
    }
}

echo "Found Users: " . count($foundUsers) . "\n";
echo "MISSING Users: " . count($missingUsers) . "\n";
print_r($missingUsers);
