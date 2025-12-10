<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PantryItem;

class PantryItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PantryItem::firstOrCreate(['name' => 'Coffee'], ['stock' => 100, 'type' => 'minuman']);
        PantryItem::firstOrCreate(['name' => 'Tea'], ['stock' => 100, 'type' => 'minuman']);
        PantryItem::firstOrCreate(['name' => 'Water'], ['stock' => 200, 'type' => 'minuman']);
        PantryItem::firstOrCreate(['name' => 'Biscuits'], ['stock' => 50, 'type' => 'makanan']);
    }
}
