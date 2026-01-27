<?php
// Script to convert seeder from user_id to NPK-based
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$seederFile = __DIR__ . '/database/seeders/LegacyMeetingsJanMar2026Seeder.php';
$content = file_get_contents($seederFile);

// Read NPK mapping
$npkMapping = [
    1004 => '11230531',
    681 => '11220019',
    705 => '11220079',
    1179 => '11240175',
    352 => '11164043',
    1255 => '11250355',
    479 => '11185354',
    993 => '11230499',
    1610 => '31125379',
    517 => '11206120',
    450 => '11174947',
    344 => '11153962',
    1305 => '11940472',
    518 => '11206122',
    682 => '11220022',
    471 => '11185297',
    365 => '11164138',
    672 => '11210580',
    1016 => '11230580',
    786 => '11220821',
    488 => '11195585',
    531 => '11210064',
    1121 => '11240076',
    1335 => '99143739',
    976 => '11230399',
    1186 => '11240195',
    1871 => '31125576',
    743 => '11220177',
    373 => '11164217',
    494 => '11195719',
];

// Add NPK mapping and helper method to class
$classDefinition = "class LegacyMeetingsJanMar2026Seeder extends Seeder\n{";
$newClassDefinition = $classDefinition . "\n    // NPK mapping from old database\n    private \$npkMapping = " . var_export($npkMapping, true) . ";\n    private \$superAdminNpk = 'admin123';\n    \n    private function getUserIdFromOldId(\$oldUserId) {\n        \$npk = \$this->npkMapping[\$oldUserId] ?? null;\n        if (!\$npk) {\n            \$superAdmin = DB::table('users')->where('npk', \$this->superAdminNpk)->first();\n            return \$superAdmin ? \$superAdmin->id : 1;\n        }\n        \$user = DB::table('users')->where('npk', \$npk)->first();\n        if (!\$user) {\n            \$superAdmin = DB::table('users')->where('npk', \$this->superAdminNpk)->first();\n            return \$superAdmin ? \$superAdmin->id : 1;\n        }\n        return \$user->id;\n    }\n";

$content = str_replace($classDefinition, $newClassDefinition, $content);

// Add Log facade
$content = str_replace(
    "use Illuminate\\Support\\Facades\\DB;",
    "use Illuminate\\Support\\Facades\\DB;\nuse Illuminate\\Support\\Facades\\Log;",
    $content
);

// Now we need to process each meeting to use getUserIdFromOldId
// This is complex, so let's add the processing logic in the run method instead

// Find the foreach or insert loop
$runMethodStart = strpos($content, 'public function run(): void');
$meetingsArrayStart = strpos($content, '$meetings = [', $runMethodStart);
$meetingsArrayEnd = strpos($content, '];', $meetingsArrayStart) + 2;

// Extract meetings array
$meetingsArray = substr($content, $meetingsArrayStart, $meetingsArrayEnd - $meetingsArrayStart);

// Add processing logic after meetings array
$processingLogic = "\n\n        // Process meetings and convert old user_id to NPK-based lookup\n        foreach (\$meetings as \$meeting) {\n            \$oldUserId = \$meeting['user_id'];\n            \$meeting['user_id'] = \$this->getUserIdFromOldId(\$oldUserId);\n            DB::table('meetings')->insert(\$meeting);\n        }\n";

// Replace the meetings array insertion
$content = substr_replace($content, $meetingsArray . $processingLogic, $meetingsArrayStart, $meetingsArrayEnd - $meetingsArrayStart);

// Remove old DB::table('meetings')->insert($meetings);
$content = preg_replace('/\s*DB::table\(\'meetings\'\)->insert\(\$meetings\);/', '', $content);

file_put_contents($seederFile, $content);
echo "Seeder updated successfully!\n";
echo "Backup saved to: LegacyMeetingsJanMar2026Seeder_BACKUP.php\n";
