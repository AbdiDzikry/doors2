<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PantryOrder;
use App\Models\Meeting;
use App\Models\PantryItem;

class PantryOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $meetings = Meeting::all();
        $pantryItems = PantryItem::all();

        if ($meetings->isEmpty() || $pantryItems->isEmpty()) {
            $this->command->info('Skipping PantryOrderSeeder: No meetings or pantry items found. Please run MeetingSeeder and PantryItemSeeder first.');
            return;
        }

        // Clear existing pantry orders to avoid duplicates on re-seed
        PantryOrder::truncate();

        // Create some dummy pantry orders for existing meetings
        foreach ($meetings as $meeting) {
            // Each meeting gets 1-3 random pantry items
            $randomPantryItems = $pantryItems->random(rand(1, min(3, $pantryItems->count())));

            foreach ($randomPantryItems as $item) {
                PantryOrder::create([
                    'meeting_id' => $meeting->id,
                    'pantry_item_id' => $item->id,
                    'quantity' => rand(1, 5), // Random quantity between 1 and 5
                    'status' => $meeting->status === 'confirmed' ? 'pending' : 'delivered', // Example status logic
                ]);
            }
        }

        $this->command->info('PantryOrderSeeder: Dummy pantry orders created.');
    }
}