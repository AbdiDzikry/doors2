<?php
// Script to sanitize and fix logpemesan_corrected.txt
$inputFile = __DIR__ . '/logpemesan_corrected.txt';
$outputFile = __DIR__ . '/logpemesan_final.txt';

$content = file_get_contents($inputFile);

// Normalize line endings
$content = str_replace(["\r\n", "\r"], "\n", $content);
$lines = explode("\n", $content);

$validLines = [];
$corrections = [
    'Ruang Arjuna' => 'Ruang Arjuno',
    'Fajar Faqih' => 'Fajar Khun Faqih',
    'Muhammad Alpin Dwizahra' => 'Muhamad Alpin Dwizahra',
    'Metting project' => 'Meeting project', // Fix topic typo
    'Meeting project ' => 'Meeting project',
];

foreach ($lines as $line) {
    if (empty(trim($line))) continue;
    
    // Check if line looks valid (has tabs)
    if (strpos($line, "\t") === false) {
        // Try to recover merged lines? 
        // Or maybe just spaces used instead of tabs?
        // Let's regex match basic structure: Room Name followed by Date
        if (preg_match('/^(Ruang .+?)\s+(\d{4}-\d{2}-\d{2})/', $line, $msg)) {
             // Found a date, proceed
        } else {
             continue; // Skip noise
        }
    }
    
    // Replace corrections
    foreach ($corrections as $bad => $good) {
        $line = str_replace($bad, $good, $line);
    }
    
    // Validate mapping specifically for "Ruang Kencana"
    if (strpos($line, 'Ruang Kencana') !== false) {
        // Ensure it doesn't accidentally become Raung? No, string replace is safe.
    }

    $validLines[] = trim($line);
}

file_put_contents($outputFile, implode("\n", $validLines));
echo "Sanitized " . count($validLines) . " lines. Saved to logpemesan_final.txt\n";
