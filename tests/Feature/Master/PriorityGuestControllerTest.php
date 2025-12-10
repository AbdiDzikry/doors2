<?php

namespace Tests\Feature\Master;

use App\Models\User;
use App\Models\PriorityGuest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriorityGuestControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed('RolesAndPermissionsSeeder');
    }

    public function test_index_page_is_rendered_properly()
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $this->actingAs($user);

        $response = $this->get(route('master.priority-guests.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_create_a_priority_guest()
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $this->actingAs($user);

        $response = $this->post(route('master.priority-guests.store'), [
            'name' => 'Test Priority Guest',
            'company' => 'Test Company',
            'position' => 'Test Position',
            'level' => 1,
        ]);

        $response->assertRedirect(route('master.priority-guests.index'));
        $this->assertDatabaseHas('priority_guests', [
            'name' => 'Test Priority Guest',
        ]);
    }

    public function test_admin_can_update_a_priority_guest()
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $this->actingAs($user);

        $priorityGuest = PriorityGuest::factory()->create();

        $response = $this->put(route('master.priority-guests.update', $priorityGuest), [
            'name' => 'Updated Priority Guest',
            'company' => 'Updated Company',
            'position' => 'Updated Position',
            'level' => 2,
        ]);

        $response->assertRedirect(route('master.priority-guests.index'));
        $this->assertDatabaseHas('priority_guests', [
            'name' => 'Updated Priority Guest',
        ]);
    }

    public function test_admin_can_delete_a_priority_guest()
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $this->actingAs($user);

        $priorityGuest = PriorityGuest::factory()->create();

        $response = $this->delete(route('master.priority-guests.destroy', $priorityGuest));

        $response->assertRedirect(route('master.priority-guests.index'));
        $this->assertDatabaseMissing('priority_guests', [
            'id' => $priorityGuest->id,
        ]);
    }
}
