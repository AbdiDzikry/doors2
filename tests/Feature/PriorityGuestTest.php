<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\PriorityGuest;
use Spatie\Permission\Models\Role;

class PriorityGuestTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'Super Admin']);
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Karyawan']);
        Role::create(['name' => 'Resepsionis']);
        Role::create(['name' => 'Manager']);

        // Create an admin user
        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');
    }

    /** @test */
    public function admin_can_view_priority_guests_index()
    {
        $this->actingAs($this->admin)
             ->get(route('master.priority-guests.index'))
             ->assertStatus(200)
             ->assertViewIs('master.priority.index');
    }

    /** @test */
    public function admin_can_view_create_priority_guest_page()
    {
        $this->actingAs($this->admin)
             ->get(route('master.priority-guests.create'))
             ->assertStatus(200)
             ->assertViewIs('master.priority.create');
    }

    /** @test */
    public function admin_can_store_a_new_priority_guest()
    {
        $this->actingAs($this->admin)
             ->post(route('master.priority-guests.store'), [
                 'name' => 'VIP Guest',
                 'level' => 1,
             ])
             ->assertRedirect(route('master.priority-guests.index'))
             ->assertSessionHas('success', 'Priority Guest created successfully.');

        $this->assertDatabaseHas('priority_guests', [
            'name' => 'VIP Guest',
            'level' => 1,
        ]);
    }

    /** @test */
    public function admin_can_view_edit_priority_guest_page()
    {
        $priorityGuest = PriorityGuest::factory()->create();

        $this->actingAs($this->admin)
             ->get(route('master.priority-guests.edit', $priorityGuest))
             ->assertStatus(200)
             ->assertViewIs('master.priority.edit')
             ->assertViewHas('guest', $priorityGuest);
    }

    /** @test */
    public function admin_can_update_a_priority_guest()
    {
        $priorityGuest = PriorityGuest::factory()->create([
            'name' => 'Old Guest',
            'level' => 2,
        ]);

        $this->actingAs($this->admin)
             ->put(route('master.priority-guests.update', $priorityGuest), [
                 'name' => 'Updated Guest',
                 'level' => 3,
             ])
             ->assertRedirect(route('master.priority-guests.index'))
             ->assertSessionHas('success', 'Priority Guest updated successfully');

        $this->assertDatabaseHas('priority_guests', [
            'id' => $priorityGuest->id,
            'name' => 'Updated Guest',
            'level' => 3,
        ]);
    }

    /** @test */
    public function admin_can_delete_a_priority_guest()
    {
        $priorityGuest = PriorityGuest::factory()->create();

        $this->actingAs($this->admin)
             ->delete(route('master.priority-guests.destroy', $priorityGuest))
             ->assertRedirect(route('master.priority-guests.index'))
             ->assertSessionHas('success', 'Priority Guest deleted successfully');

        $this->assertDatabaseMissing('priority_guests', ['id' => $priorityGuest->id]);
    }

    /** @test */
    public function priority_guest_creation_requires_validation()
    {
        $this->actingAs($this->admin)
             ->post(route('master.priority-guests.store'), [])
             ->assertSessionHasErrors(['name', 'level']);
    }

    /** @test */
    public function priority_guest_update_requires_validation()
    {
        $priorityGuest = PriorityGuest::factory()->create();

        $this->actingAs($this->admin)
             ->put(route('master.priority-guests.update', $priorityGuest), [
                 'name' => '',
                 'level' => 'invalid',
             ])
             ->assertSessionHasErrors(['name', 'level']);
    }
}
