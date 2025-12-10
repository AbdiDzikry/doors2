<?php

namespace Tests\Feature\Master;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed('RolesAndPermissionsSeeder');
    }

    public function test_admin_can_create_a_user()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $this->actingAs($admin);

        $response = $this->post(route('master.users.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'npk' => '123456',
            'department' => 'Test Department',
            'roles' => ['Karyawan'],
        ]);

        $response->assertRedirect(route('master.users.index'));
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    public function test_admin_can_update_a_user()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $this->actingAs($admin);

        $user = User::factory()->create();

        $response = $this->put(route('master.users.update', $user), [
            'name' => 'Updated User',
            'email' => $user->email,
            'npk' => '654321',
            'department' => 'Updated Department',
            'roles' => ['Manager'],
            'password' => null,
            'password_confirmation' => null,
        ]);

        $response->assertRedirect(route('master.users.index'));
        $this->assertDatabaseHas('users', [
            'name' => 'Updated User',
            'email' => $user->email,
        ]);
    }

    public function test_admin_can_delete_a_user()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $this->actingAs($admin);

        $user = User::factory()->create();

        $response = $this->delete(route('master.users.destroy', $user));

        $response->assertRedirect(route('master.users.index'));
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_admin_can_export_users()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $this->actingAs($admin);

        // Create a few users to export, plus the admin user = 6 total
        User::factory()->count(5)->create();

        Excel::fake();

        $this->get(route('master.users.export'));

        Excel::assertDownloaded('users.xlsx', function ($export) {
            // Assert that the collection count is 6 (5 created + 1 admin)
            return $export->collection()->count() === 6;
        });
    }
}
