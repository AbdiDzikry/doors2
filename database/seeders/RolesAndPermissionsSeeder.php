<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // create permissions
        Permission::firstOrCreate(['name' => 'manage settings']);

        Permission::firstOrCreate(['name' => 'manage master data']);
        Permission::firstOrCreate(['name' => 'manage users']);
        Permission::firstOrCreate(['name' => 'manage rooms']);
        Permission::firstOrCreate(['name' => 'manage pantry']);
        Permission::firstOrCreate(['name' => 'manage external participants']);
        Permission::firstOrCreate(['name' => 'manage priority guests']);

        Permission::firstOrCreate(['name' => 'access meeting room']);
        Permission::firstOrCreate(['name' => 'book rooms']);
        Permission::firstOrCreate(['name' => 'view analytics']);

        Permission::firstOrCreate(['name' => 'manage configurations']);
        Permission::firstOrCreate(['name' => 'manage roles and permissions']);

        Permission::firstOrCreate(['name' => 'access pantry dashboard']);
        Permission::firstOrCreate(['name' => 'access tablet mode']);

        // create roles and assign created permissions

        $role = Role::firstOrCreate(['name' => 'Super Admin']);
        $role->givePermissionTo(Permission::all());

        $role = Role::firstOrCreate(['name' => 'Tablet']);
        $role->givePermissionTo('access tablet mode');

        $role = Role::firstOrCreate(['name' => 'Admin']);
        $role->givePermissionTo('manage master data');
        $role->givePermissionTo('access tablet mode');
        $role->givePermissionTo('manage users');
        $role->givePermissionTo('manage rooms');
        $role->givePermissionTo('manage pantry');
        $role->givePermissionTo('manage external participants');
        $role->givePermissionTo('manage priority guests');
        $role->givePermissionTo('manage configurations');
        $role->givePermissionTo('manage roles and permissions');

        $role = Role::firstOrCreate(['name' => 'Karyawan']);
        $role->givePermissionTo('access meeting room');
        $role->givePermissionTo('book rooms');
        $role->givePermissionTo('view analytics');

        $role = Role::firstOrCreate(['name' => 'Resepsionis']);
        $role->givePermissionTo('access pantry dashboard');

        $role = Role::firstOrCreate(['name' => 'Manager']);
        $role->givePermissionTo('manage rooms');
        $role->givePermissionTo('manage pantry');
        $role->givePermissionTo('view analytics');

        $role = Role::firstOrCreate(['name' => 'Section Head']);
        $role->givePermissionTo('manage settings');
        $role->givePermissionTo('manage master data');
        $role->givePermissionTo('manage rooms');
        $role->givePermissionTo('manage pantry');
        $role->givePermissionTo('manage priority guests');
        $role->givePermissionTo('access meeting room');
    }
}
