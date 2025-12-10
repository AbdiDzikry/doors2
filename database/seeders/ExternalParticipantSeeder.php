<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ExternalParticipant;

class ExternalParticipantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ExternalParticipant::firstOrCreate(
            ['email' => 'john.doe@example.com'],
            ['name' => 'John Doe', 'type' => 'external']
        );
        ExternalParticipant::firstOrCreate(
            ['email' => 'jane.smith@example.com'],
            ['name' => 'Jane Smith', 'type' => 'external']
        );
    }
}
