<?php

$file = 'database/seeders/LegacyMeetingsJanMar2026Seeder.php';
$lines = file($file);
$newLines = [];

foreach ($lines as $line) {
    if (strpos($line, "'created_by' =>") === false) {
        $newLines[] = $line;
    }
}

file_put_contents($file, implode("", $newLines));
echo "Fixed seeder file by removing created_by lines.\n";
