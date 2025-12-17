<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Configuration;

class ConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Configuration::firstOrCreate(['key' => 'app_name'], ['value' => 'Doors App']);
        Configuration::firstOrCreate(['key' => 'default_meeting_duration'], ['value' => '60']);
        Configuration::firstOrCreate(['key' => 'office_start_hour'], ['value' => '7']);
        Configuration::firstOrCreate(['key' => 'office_end_hour'], ['value' => '18']);
    }
}
