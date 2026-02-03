<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class GeneralAffairPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Permissions
        // Parent Permission for Asset Management
        $manageAssets = Permission::firstOrCreate(['name' => 'manage assets']);

        // Parent Permission for Ticket Management (Phase 3)
        // $manageTickets = Permission::firstOrCreate(['name' => 'manage tickets']);

        // 2. Assign to Super Admin (Always gets everything via Gate::before in AuthServiceProvider, but good to be explicit)
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($manageAssets);
        }

        // 3. Assign to Admin (GA Staff acts as Admin for now)
        $admin = Role::where('name', 'Admin')->first();
        if ($admin) {
            $admin->givePermissionTo($manageAssets);
        }

        $this->command->info('General Affair permissions seeded!');
    }
}
