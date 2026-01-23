<?php

$file = 'database/seeders/LegacyMeetingsJanMar2026Seeder.php';
$lines = file($file);
$newLines = [];

foreach ($lines as $line) {
    // Remove lines assigning problematic columns
    if (strpos($line, "'created_by' =>") !== false) {
        continue;
    }
    // Only keep description if the column exists (check schema output manually first if unsure, but let's assume valid from schema check output which didn't show it in field list? Wait, I didn't see description in the schema execution output index)
    // The output was: [0]=>id ... [16]=>confirmation_status. 'description' IS MISSING from the array output!
    if (strpos($line, "'description' =>") !== false) {
        continue;
    }
    
    $newLines[] = $line;
}

file_put_contents($file, implode("", $newLines));
echo "Fixed seeder file by removing invalid columns.\n";
