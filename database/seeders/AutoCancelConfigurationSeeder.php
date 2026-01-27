<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Configuration;

class AutoCancelConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Configuration::updateOrCreate(
            ['key' => 'auto_cancel_unattended_meetings'],
            [
                'value' => '1', // Enabled by default
                'description' => 'Automatically cancel meetings 30 minutes after start time if no one checks in. This frees up the room for other bookings.'
            ]
        );
    }
}
