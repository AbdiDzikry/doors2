<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Configuration; // Don't forget to use the Configuration model

class LoginApiKeySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Configuration::firstOrCreate(
            ['key' => 'LOGIN_API_KEY'],
            [
                'value' => 'your_api_key_here',
                'description' => 'API key used for external login service integration. Replace with your actual key.',
            ]
        );
    }
}