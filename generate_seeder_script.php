<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$className = 'LegacyMeetingsJanMar2026Seeder';
$outputPath = database_path("seeders/{$className}.php");

$startDate = '2026-01-15 00:00:00';
$endDate = '2026-03-31 23:59:59';

$meetings = DB::table('meetings')
    ->whereBetween('start_time', [$startDate, $endDate])
    ->orderBy('start_time')
    ->get();

$data = "[\n";
foreach ($meetings as $meeting) {
    $data .= "            [\n";
    $data .= "                'user_id' => " . ($meeting->user_id ?? 'null') . ",\n";
    $data .= "                'room_id' => " . ($meeting->room_id ?? 'null') . ",\n";
    // Avoid double quoting if topic has quotes
    $topic = str_replace("'", "\'", $meeting->topic);
    $data .= "                'topic' => '" . $topic . "',\n";
    $data .= "                'description' => null,\n"; // Schema doesn't have it but Seeder usually good to have consistent struct, wait schema check fail on description earlier. Let's omit it to be safe or set null?
    // Actually schema check said description is MISSING. So DO NOT INCLUDE description.
    // $data .= "                'description' => null,\n"; 
    $data .= "                'start_time' => '" . $meeting->start_time . "',\n";
    $data .= "                'end_time' => '" . $meeting->end_time . "',\n";
    $data .= "                'status' => '" . $meeting->status . "',\n";
    $data .= "                'created_at' => '" . $meeting->created_at . "',\n";
    $data .= "                'updated_at' => '" . $meeting->updated_at . "',\n";
    $data .= "            ],\n";
}
$data .= "        ]";

$template = "<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class {$className} extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Safety: Delete existing meetings in this range to avoid duplicates
        DB::table('meetings')
            ->whereBetween('start_time', ['{$startDate}', '{$endDate}'])
            ->delete();

        \$meetings = {$data};

        DB::table('meetings')->insert(\$meetings);

        // BACKFILL: Insert Organizer as Participant (Required for Attendance/PDF)
        \$insertedMeetings = DB::table('meetings')
            ->whereBetween('start_time', ['{$startDate}', '{$endDate}'])
            ->get();

        \$participants = [];
        foreach (\$insertedMeetings as \$meeting) {
            \$participants[] = [
                'meeting_id' => \$meeting->id,
                'participant_type' => 'App\Models\User',
                'participant_id' => \$meeting->user_id,
                'status' => \$meeting->status === 'completed' ? 'attended' : 'confirmed',
                'is_pic' => 1, // Set as PIC so they appear in reports
                'checked_in_at' => \$meeting->status === 'completed' ? \$meeting->start_time : null,
                'attended_at' => \$meeting->status === 'completed' ? \$meeting->start_time : null,
                'created_at' => \$meeting->created_at,
                'updated_at' => \$meeting->updated_at,
            ];
        }

        if (!empty(\$participants)) {
            DB::table('meeting_participants')->insert(\$participants);
        }
    }
}
";

File::put($outputPath, $template);

echo "Seeder generated successfully at: {$outputPath}\n";
