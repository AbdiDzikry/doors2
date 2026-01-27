<?php
// Script to create a NEW seeder with NPK logic
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$sourceFile = __DIR__ . '/database/seeders/LegacyMeetingsJanMar2026Seeder.php';
$targetFile = __DIR__ . '/database/seeders/FixedMeetingsSeeder.php';

$content = file_get_contents($sourceFile);

// 1. Change Class Name
$content = str_replace('class LegacyMeetingsJanMar2026Seeder', 'class FixedMeetingsSeeder', $content);

// 2. Add NPK Mapping Property and Helper Method
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

$logicCode = "
    // NPK mapping from old database
    private \$npkMapping = " . var_export($npkMapping, true) . ";
    private \$superAdminNpk = 'admin123';
    
    private function getUserIdFromOldId(\$oldUserId) {
        \$npk = \$this->npkMapping[\$oldUserId] ?? null;
        
        // If no NPK mapped, use Super Admin
        if (!\$npk) {
            \$superAdmin = DB::table('users')->where('npk', \$this->superAdminNpk)->first();
            return \$superAdmin ? \$superAdmin->id : 1;
        }
        
        // Find user by NPK in SSO database
        \$user = DB::table('users')->where('npk', \$npk)->first();
        
        if (!\$user) {
            // User ID exists in old DB but not currently synced in new DB
            // Fallback to Super Admin so meeting is not lost
            \$superAdmin = DB::table('users')->where('npk', \$this->superAdminNpk)->first();
            return \$superAdmin ? \$superAdmin->id : 1;
        }
        
        return \$user->id;
    }
";

// Insert logic inside class
$content = preg_replace('/class FixedMeetingsSeeder extends Seeder\s*\{/', "class FixedMeetingsSeeder extends Seeder\n{\n$logicCode", $content);

// 3. Modifying main run loop
// Find where $meetings array is defined and used
// We want to replace the `DB::table('meetings')->insert($meetings);` with our custom loop

$loopLogic = "
        // Process meetings and convert old user_id to NPK-based lookup
        foreach (\$meetings as \$meeting) {
            \$oldUserId = \$meeting['user_id'];
            \$meeting['user_id'] = \$this->getUserIdFromOldId(\$oldUserId);
            // Ensure timestamps are valid
            if (!isset(\$meeting['created_at'])) \$meeting['created_at'] = now();
            if (!isset(\$meeting['updated_at'])) \$meeting['updated_at'] = now();
            
            DB::table('meetings')->insert(\$meeting);
        }
";

// Replace the insert call
$content = str_replace("DB::table('meetings')->insert(\$meetings);", $loopLogic, $content);

// 4. Add Log facade usage if not present
if (!str_contains($content, 'use Illuminate\Support\Facades\Log;')) {
    $content = str_replace('use Illuminate\Support\Facades\DB;', "use Illuminate\Support\Facades\DB;\nuse Illuminate\Support\Facades\Log;", $content);
}

file_put_contents($targetFile, $content);
echo "Created FixedMeetingsSeeder.php successfully!\n";
