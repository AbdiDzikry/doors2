<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PriorityGuest;

class PriorityGuestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PriorityGuest::firstOrCreate(['name' => 'CEO']);
        PriorityGuest::firstOrCreate(['name' => 'VIP Client']);
    }
}
