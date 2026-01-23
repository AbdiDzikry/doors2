<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Room;
use App\Models\Meeting;
use App\Models\PantryOrder;
use App\Models\PantryItem; // Assuming this model exists
use Carbon\Carbon;

class ImportLegacyMeetings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:legacy-meetings {file=meeting_lists.csv} {--dry-run : Simulate the import without saving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import legacy meeting data (2026 onwards) from CSV file.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        $dryRun = $this->option('dry-run');

        if (!file_exists(base_path($file))) {
            $this->error("File not found: " . base_path($file));
            return 1;
        }

        $this->info("Reading file: $file");
        if ($dryRun) {
            $this->warn("DRY RUN MODE: No changes will be saved to the database.");
        }

        $handle = fopen(base_path($file), 'r');
        $header = fgetcsv($handle); // Skip header

        // Map header columns with normalization
        $colMap = [];
        foreach ($header as $index => $col) {
            // Normalize: "Room Name" -> "room_name", "Start time" -> "start_time"
            $normalized = strtolower(trim($col));
            $normalized = str_replace(' ', '_', $normalized);
            
            $colMap[$normalized] = $index;
            
            // Add Aliases for Indonesian headers
            if ($normalized === 'jenis_meeting') $colMap['kind_meeting'] = $index;
            if ($normalized === 'topik_meeting') $colMap['topic_meeting'] = $index;
        }
        
        $countImported = 0;
        $countSkipped = 0;
        $countFailed = 0;

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                // Helper to get value
                $val = fn($key) => isset($colMap[$key]) ? ($row[$colMap[$key]] ?? null) : null;

                $dateStr = $val('date') ?: $val('start_time'); // Fallback to start_time if date is missing
                // Parse date - CSV seems to use ISO 8601 part or Y-m-d
                // Based on sample: 2026-01-14T05:00:00.000Z or 2026-01-14
                try {
                    $date = Carbon::parse($dateStr);
                } catch (\Exception $e) {
                    $this->warn("Skipping invalid date row: " . implode(',', $row));
                    $countSkipped++;
                    continue;
                }

                // Filter 2026 onwards
                if ($date->year < 2026) {
                    // $this->line("Skipping old date: " . $date->toDateString());
                    $countSkipped++;
                    continue;
                }

                $this->line("Processing: " . $val('name') . " on " . $date->toDateString());

                // 1. Map User (Organizer)
                $npk = $val('npk');
                $createdBy = $val('created_by'); 
                $picName = $val('pic_name'); 

                // Manual Fix: M. Ridwan -> Muhammad Ridwan
                if ($picName === 'M. Ridwan Hadiwinata') {
                    $picName = 'Muhammad Ridwan Hadiwinata';
                }
                
                $user = null;

                // Try finding by NPK first
                if ($npk) {
                    $user = User::where('npk', $npk)->first();
                }
                
                if (!$user && is_numeric($createdBy)) {
                     $user = User::where('npk', $createdBy)->first();
                }

                // Fallback: Find by Name (for Phase 2 data which lacks NPK)
                if (!$user && $picName) {
                    $cleanName = trim($picName);
                    // 1. Try Exact Match (Case Insensitive via LIKE)
                    $user = User::where('name', 'LIKE', $cleanName)->first();
                    
                    // 2. Try First Name Match (Safe Fallback for slightly different spellings)
                    if (!$user) {
                        $firstName = explode(' ', $cleanName)[0];
                        if (strlen($firstName) > 3) {
                             $user = User::where('name', 'LIKE', "{$firstName}%")->first();
                        }
                    }
                }

                if (!$user) {
                    $this->error("User not found for NPK: $npk / Name: $picName. Skipping.");
                    $countFailed++;
                    continue;
                }

                // 2. Map Room
                $roomName = $val('room_name');
                $roomNameClean = trim($roomName);

                // Manual Fix: Arjuna -> Arjuno
                if (stripos($roomNameClean, 'Arjuna') !== false) {
                     $roomNameClean = str_ireplace('Arjuna', 'Arjuno', $roomNameClean);
                }
                
                $room = Room::where('name', 'LIKE', $roomNameClean)->first(); 
                
                if (!$room) {
                    $this->error("Room not found: $roomName. Skipping.");
                    $countFailed++;
                    continue;
                }

                // 3. Prepare Meeting Times
                // CSV has 'start_time' and 'end_time' which look like "2026-01-14T05:00:00.000Z" (UTC)
                try {
                    $startTime = Carbon::parse($val('start_time'))->setTimezone('Asia/Jakarta');
                    $endTime = Carbon::parse($val('end_time'))->setTimezone('Asia/Jakarta');
                } catch(\Exception $e) {
                    $this->error("Invalid time format. Skipping.");
                    $countFailed++;
                    continue;
                }

                // CHECK FOR DUPLICATES / COLLISIONS
                $existingMeeting = Meeting::where('room_id', $room->id)
                    ->where(function ($query) use ($startTime, $endTime) {
                        $query->whereBetween('start_time', [$startTime, $endTime])
                              ->orWhereBetween('end_time', [$startTime, $endTime])
                              ->orWhere(function ($q) use ($startTime, $endTime) {
                                  $q->where('start_time', '<', $startTime)
                                    ->where('end_time', '>', $endTime);
                              });
                    })
                    ->first();

                if ($existingMeeting) {
                    // Check if it's an exact duplicate (safely repeatable)
                    if ($existingMeeting->start_time->eq($startTime) && $existingMeeting->end_time->eq($endTime)) {
                         $this->line("  - Duplicate found (Skipping): " . $val('name'));
                         $countSkipped++; 
                    } else {
                         // Real Collision
                         $this->error("  ! COLLISION detected with existing meeting ID {$existingMeeting->id} in {$room->name}. Skipping.");
                         
                         $logMessage = sprintf(
                            "[%s] COLLISION DETECTED\n" .
                            "   CSV (New): Topic='%s', User='%s' (%s), Time=%s - %s\n" .
                            "   DB (Existing): ID=%d, Topic='%s', User='%s', Time=%s - %s\n" .
                            "   Room: %s\n" .
                            "--------------------------------------------------\n",
                            now(),
                            $val('topic_meeting') ?: $val('kind_meeting'),
                            $user->name, $npk,
                            $startTime->toDateTimeString(), $endTime->toDateTimeString(),
                            $existingMeeting->id,
                            $existingMeeting->topic,
                            $existingMeeting->user->name ?? 'Unknown',
                            $existingMeeting->start_time->toDateTimeString(),
                            $existingMeeting->end_time->toDateTimeString(),
                            $room->name
                         );
                         
                         // Append to a specific log file for easy reading
                         file_put_contents(storage_path('logs/import_collisions.log'), $logMessage, FILE_APPEND);
                         
                         $countFailed++;
                    }
                    continue; // Skip insertion
                }

                // 4. Create Meeting
                if (!$dryRun) {
                    $meeting = Meeting::create([
                        'user_id' => $user->id,
                        'room_id' => $room->id,
                        'topic' => $val('topic_meeting') ?: ($val('kind_meeting') ?: 'Meeting Import'), 
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'description' => $val('topic_meeting'), 
                        'status' => $endTime->isPast() ? 'completed' : 'scheduled', // Auto-complete past meetings to prevent auto-cancellation
                        'meeting_type' => 'non-recurring', 
                        'confirmation_status' => 'confirmed', 
                    ]);

                    // 5. Map Pantry Orders
                    // Column 'pantries' seems empty in samples, but 'pantry_ids' or just JSON string in a column?
                    // Sample: "[{""id"":""6189fbb9ff1aef0eee4201a6"",""name"":""Air Mineral"",""qty"":""7"",""description"":""air mineral"",""status"":0}]"
                    // It seems the column 'pantries' contains the JSON execution details.
                    
                    $pantryJson = $val('pantries');
                    if (!empty($pantryJson) && $pantryJson !== '[]') {
                        $pantryData = json_decode($pantryJson, true);
                        if (is_array($pantryData)) {
                            foreach ($pantryData as $itemData) {
                                // Find Pantry Item by Name
                                $itemName = $itemData['name'] ?? '';
                                $qty = $itemData['qty'] ?? 0;
                                
                                if (!$itemName || $qty <= 0) continue;

                                $pantryItem = PantryItem::where('name', 'LIKE', $itemName)->first();
                                
                                if ($pantryItem) {
                                    PantryOrder::create([
                                        'meeting_id' => $meeting->id,
                                        'pantry_item_id' => $pantryItem->id,
                                        'quantity' => $qty,
                                        'status' => 'pending', // Default to pending so receptionist sees them
                                        'custom_items' => $itemData['description'] ?? null
                                    ]);
                                    $this->info("  + Added Pantry: $itemName x$qty");
                                } else {
                                    $this->warn("  - Pantry item not found: $itemName");
                                }
                            }
                        }
                    }
                }
                
                $countImported++;
            }

            if ($dryRun) {
                DB::rollBack();
                $this->info("Dry run complete. Rolled back changes.");
            } else {
                DB::commit();
                $this->info("Import complete. Changes saved.");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Fatal error: " . $e->getMessage());
            return 1;
        }

        $this->table(
            ['Metric', 'Count'],
            [
                ['Imported (New)', $countImported],
                ['Skipped (Date < 2026 or Duplicate)', $countSkipped],
                ['Failed (Mapping/Collision)', $countFailed],
            ]
        );

        if ($countFailed > 0) {
            $this->warn("\nCheck storage/logs/import_collisions.log for detailed collision reports.");
        }

        return 0;
    }
}
