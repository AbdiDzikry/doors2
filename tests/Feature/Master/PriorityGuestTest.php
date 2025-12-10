<?php

namespace Tests\Feature\Master;

use App\Models\PriorityGuest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\Concerns\InteractsWithSession;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PriorityGuestTest extends TestCase
{
    use RefreshDatabase, InteractsWithSession;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->artisan('db:seed --class=RolesAndPermissionsSeeder');

        // Create a Super Admin user
        $superAdminRole = Role::findByName('Super Admin');
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole($superAdminRole);

        // Create an Admin user
        $adminRole = Role::findByName('Admin');
        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);

        // Create a regular user (Karyawan)
        $karyawanRole = Role::findByName('Karyawan');
        $this->karyawan = User::factory()->create();
        $this->karyawan->assignRole($karyawanRole);
    }

    #[test]
    public function super_admin_can_access_priority_guests_index(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('master.priority-guests.index'))
            ->assertOk();
    }

    #[test]
    public function admin_can_access_priority_guests_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('master.priority-guests.index'))
            ->assertOk();
    }

    #[test]
    public function non_admin_cannot_access_priority_guests_index(): void
    {
        $this->actingAs($this->karyawan)
            ->get(route('master.priority-guests.index'))
            ->assertForbidden();
    }

    #[test]
    public function guest_cannot_access_priority_guests_index(): void
    {
        $this->get(route('master.priority-guests.index'))
            ->assertRedirect(route('login'));
    }

    #[test]
    public function super_admin_can_create_priority_guest(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('master.priority-guests.store'), [
                'name' => 'John Doe',
                'level' => 3,
            ])
            ->assertRedirect(route('master.priority-guests.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('priority_guests', [
            'name' => 'John Doe',
            'level' => 3,
        ]);
    }

    #[test]
    public function admin_can_create_priority_guest(): void
    {
        $this->actingAs($this->admin)
            ->post(route('master.priority-guests.store'), [
                'name' => 'Jane Smith',
                'level' => 2,
            ])
            ->assertRedirect(route('master.priority-guests.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('priority_guests', [
            'name' => 'Jane Smith',
            'level' => 2,
        ]);
    }

    #[test]
    public function non_admin_cannot_create_priority_guest(): void
    {
        $this->actingAs($this->karyawan)
            ->post(route('master.priority-guests.store'), [
                'name' => 'Bob Johnson',
                'level' => 1,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('priority_guests', [
            'name' => 'Bob Johnson',
        ]);
    }

    #[test]
    public function super_admin_can_update_priority_guest(): void
    {
        $priorityGuest = PriorityGuest::factory()->create();

        $this->actingAs($this->superAdmin)
            ->put(route('master.priority-guests.update', $priorityGuest), [
                'name' => 'Updated John Doe',
                'level' => 4,
            ])
            ->assertRedirect(route('master.priority-guests.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('priority_guests', [
            'id' => $priorityGuest->id,
            'name' => 'Updated John Doe',
            'level' => 4,
        ]);
    }

    #[test]
    public function admin_can_update_priority_guest(): void
    {
        $priorityGuest = PriorityGuest::factory()->create();

        $this->actingAs($this->admin)
            ->put(route('master.priority-guests.update', $priorityGuest), [
                'name' => 'Updated Jane Smith',
                'level' => 5,
            ])
            ->assertRedirect(route('master.priority-guests.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('priority_guests', [
            'id' => $priorityGuest->id,
            'name' => 'Updated Jane Smith',
            'level' => 5,
        ]);
    }

    #[test]
    public function non_admin_cannot_update_priority_guest(): void
    {
        $priorityGuest = PriorityGuest::factory()->create();

        $this->actingAs($this->karyawan)
            ->put(route('master.priority-guests.update', $priorityGuest), [
                'name' => 'Forbidden Bob Johnson',
                'level' => 1,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('priority_guests', [
            'name' => 'Forbidden Bob Johnson',
        ]);
    }

    #[test]
    public function super_admin_can_delete_priority_guest(): void
    {
        $priorityGuest = PriorityGuest::factory()->create();

        $this->actingAs($this->superAdmin)
            ->delete(route('master.priority-guests.destroy', $priorityGuest))
            ->assertRedirect(route('master.priority-guests.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('priority_guests', [
            'id' => $priorityGuest->id,
        ]);
    }

    #[test]
    public function admin_can_delete_priority_guest(): void
    {
        $priorityGuest = PriorityGuest::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('master.priority-guests.destroy', $priorityGuest))
            ->assertRedirect(route('master.priority-guests.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('priority_guests', [
            'id' => $priorityGuest->id,
        ]);
    }

    #[test]
    public function non_admin_cannot_delete_priority_guest(): void
    {
        $priorityGuest = PriorityGuest::factory()->create();

        $this->actingAs($this->karyawan)
            ->delete(route('master.priority-guests.destroy', $priorityGuest))
            ->assertForbidden();

        $this->assertDatabaseHas('priority_guests', [
            'id' => $priorityGuest->id,
        ]);
    }

    #[test]
    public function create_priority_guest_requires_name_email_organization(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('master.priority-guests.store'), [])
            ->assertSessionHasErrors(['name', 'level']);
    }

    #[test]
    public function update_priority_guest_requires_name_email_organization(): void
    {
        $priorityGuest = PriorityGuest::factory()->create();

        $this->actingAs($this->superAdmin)
            ->put(route('master.priority-guests.update', $priorityGuest), [
                'name' => '',
                'level' => '',
            ])
            ->assertSessionHasErrors(['name', 'level']);
    }
}