<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RolePermissionControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function setUp(): void
    {
        parent::setUp();
        config()->set('permission.cache.duration', 0); // Disable cache for tests
    }



    public function test_super_admin_can_access_role_permissions_index_page()
    {
        $superAdmin = User::factory()->create();
        Role::factory()->create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->assignRole('Super Admin');
        $this->actingAs($superAdmin);

        $response = $this->get(route('settings.role-permissions.index'));

        $response->assertStatus(200);
    }

    public function test_super_admin_can_create_a_role()
    {
        $superAdmin = User::factory()->create();
        Role::factory()->create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->assignRole('Super Admin');
        $this->actingAs($superAdmin);

        $permission = Permission::factory()->create();

        $response = $this->post(route('settings.role-permissions.store'), [
            'name' => 'New Role',
            'permissions' => [$permission->id],
        ]);

        $response->assertRedirect(route('settings.role-permissions.index'));
        $this->assertDatabaseHas('roles', ['name' => 'New Role']);
    }

    public function test_super_admin_can_update_a_role()
    {
        $superAdmin = User::factory()->create();
        Role::factory()->create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->assignRole('Super Admin');
        $this->actingAs($superAdmin);

        $role = Role::factory()->create(['name' => 'Test Role']);
        $permission = Permission::factory()->create();

        $response = $this->put(route('settings.role-permissions.update', $role->id), [
            'name' => 'Updated Role',
            'permissions' => [$permission->id],
        ]);

        $response->assertRedirect(route('settings.role-permissions.index'));
        $this->assertDatabaseHas('roles', ['name' => 'Updated Role']);
    }

    public function test_super_admin_can_delete_a_role()
    {
        $superAdmin = User::factory()->create();
        Role::factory()->create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->assignRole('Super Admin');
        $this->actingAs($superAdmin);

        $role = Role::factory()->create(['name' => 'Test Role']);

        $response = $this->delete(route('settings.role-permissions.destroy', $role->id));

        $response->assertRedirect(route('settings.role-permissions.index'));
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }
}
