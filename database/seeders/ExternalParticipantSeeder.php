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
        // Tamu dari Toyota
        ExternalParticipant::firstOrCreate(
            ['email' => 'aditya.pratama@toyota.co.id'],
            ['name' => 'Aditya Pratama', 'company_name' => 'PT Toyota Motor Manufacturing Indonesia', 'type' => 'Tamu Korporat']
        );

        // Tamu dari Astra
        ExternalParticipant::firstOrCreate(
            ['email' => 'budi.santoso@astra.co.id'],
            ['name' => 'Budi Santoso', 'company_name' => 'PT Astra International Tbk', 'type' => 'Tamu Korporat']
        );

        // Tamu dari Vendor IT
        ExternalParticipant::firstOrCreate(
            ['email' => 'support@netindo.com'],
            ['name' => 'Citra Lestari', 'company_name' => 'PT Netindo Solusi Digital', 'type' => 'Vendor']
        );
            
        // Tamu dari Honda
        ExternalParticipant::firstOrCreate(
            ['email' => 'dimas.wibowo@hpm.co.id'],
            ['name' => 'Dimas Wibowo', 'company_name' => 'PT Honda Prospect Motor', 'type' => 'Tamu Korporat']
        );
    }
}
