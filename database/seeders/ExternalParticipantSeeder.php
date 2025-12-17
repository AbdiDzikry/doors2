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
            ['name' => 'Aditya Pratama', 'company' => 'PT Toyota Motor Manufacturing Indonesia', 'type' => 'external']
        );

        // Tamu dari Astra
        ExternalParticipant::firstOrCreate(
            ['email' => 'budi.santoso@astra.co.id'],
            ['name' => 'Budi Santoso', 'company' => 'PT Astra International Tbk', 'type' => 'external']
        );

        // Tamu dari Vendor IT
        ExternalParticipant::firstOrCreate(
            ['email' => 'support@netindo.com'],
            ['name' => 'Citra Lestari', 'company' => 'PT Netindo Solusi Digital', 'type' => 'external']
        );
            
        // Tamu dari Honda
        ExternalParticipant::firstOrCreate(
            ['email' => 'dimas.wibowo@hpm.co.id'],
            ['name' => 'Dimas Wibowo', 'company' => 'PT Honda Prospect Motor', 'type' => 'external']
        );
    }
}
