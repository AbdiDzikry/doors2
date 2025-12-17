<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Disable foreign key checks
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();

        $this->call(RolesAndPermissionsSeeder::class);

        // Create Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@doors.com'],
            ['name' => 'Super Admin', 'password' => bcrypt('password'), 'npk' => 'SA001', 'position' => 'Super Admin', 'division' => 'Board', 'department' => 'Executive', 'phone' => '081200000000']
        );
        if (!$superAdmin->hasRole('Super Admin')) {
            $superAdmin->assignRole('Super Admin');
        }

        // Create Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@doors.com'],
            ['name' => 'Admin', 'password' => bcrypt('password'), 'npk' => 'ADM01', 'position' => 'Administrator', 'division' => 'IT', 'department' => 'Infrastructure', 'phone' => '081200000001']
        );
        if (!$admin->hasRole('Admin')) {
            $admin->assignRole('Admin');
        }

        // Create Manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@doors.com'],
            ['name' => 'Manager', 'password' => bcrypt('password'), 'npk' => 'MGR01', 'position' => 'General Manager', 'division' => 'Operations', 'department' => 'Facility Management', 'phone' => '081200000002']
        );
        if (!$manager->hasRole('Manager')) {
            $manager->assignRole('Manager');
        }

        // Create Resepsionis
        $resepsionis = User::firstOrCreate(
            ['email' => 'resepsionis@doors.com'],
            ['name' => 'Resepsionis', 'password' => bcrypt('password'), 'npk' => 'REC01', 'position' => 'Receptionist', 'division' => 'GA', 'department' => 'Front Office', 'phone' => '081200000003']
        );
        if (!$resepsionis->hasRole('Resepsionis')) {
            $resepsionis->assignRole('Resepsionis');
        }

        // Create Karyawan
        $karyawan = User::firstOrCreate(
            ['email' => 'karyawan@doors.com'],
            ['name' => 'Karyawan', 'password' => bcrypt('password'), 'npk' => 'EMP01', 'position' => 'Staff', 'division' => 'IT', 'department' => 'Development', 'phone' => '081200000004']
        );
        if (!$karyawan->hasRole('Karyawan')) {
            $karyawan->assignRole('Karyawan');
        }

        $this->call(RoomSeeder::class);
        $this->call(PantryItemSeeder::class);
        $this->call(LainnyaPantryItemSeeder::class);
        $this->call(ExternalParticipantSeeder::class);
        $this->call(PriorityGuestSeeder::class);
        $this->call(ConfigurationSeeder::class);
        $this->call(LoginApiKeySeeder::class); // Add this line // Ensure all required data is present before meetings

        if (!app()->isProduction()) {
            $this->call(MeetingSeeder::class);
            $this->call(PantryOrderSeeder::class);
        }

        // Enable foreign key checks
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
    }
}
