<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PantryItem;

class LainnyaPantryItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PantryItem::firstOrCreate(
            ['name' => 'Lainnya'],
            [
                'description' => 'Pilihan untuk memasukkan permintaan item pantry kustom atau tambahan yang tidak ada di daftar.',
                'type' => 'makanan',
                'stock' => 999, // Set a high stock as this is a virtual item
            ]
        );
    }
}