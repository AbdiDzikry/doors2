<?php

namespace Tests\Feature\Master;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class RoomTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_super_admin_can_access_rooms_index(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('master.rooms.index'))
            ->assertOk();
    }

    public function test_admin_can_access_rooms_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('master.rooms.index'))
            ->assertOk();
    }

    public function test_non_admin_cannot_access_rooms_index(): void
    {
        $this->actingAs($this->karyawan)
            ->get(route('master.rooms.index'))
            ->assertForbidden();
    }

    public function test_guest_cannot_access_rooms_index(): void
    {
        $this->get(route('master.rooms.index'))
            ->assertRedirect(route('login'));
    }

    public function test_super_admin_can_create_room(): void
    {
        Storage::fake('public');
        $image = UploadedFile::fake()->image('room.jpg');

        $this->actingAs($this->superAdmin)
            ->post(route('master.rooms.store'), [
                'name' => 'Meeting Room A',
                'description' => 'A large meeting room with projector.',
                'facilities' => 'Projector, Whiteboard, AC',
                'capacity' => 10,
                'status' => 'available',
                'image' => $image,
            ])
            ->assertRedirect(route('master.rooms.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('rooms', [
            'name' => 'Meeting Room A',
            'description' => 'A large meeting room with projector.',
            'facilities' => 'Projector, Whiteboard, AC',
            'status' => 'available',
        ]);

        Storage::disk('public')->assertExists('rooms/' . $image->hashName());
    }

    public function test_admin_can_create_room(): void
    {
        Storage::fake('public');
        $image = UploadedFile::fake()->image('room2.jpg');

        $this->actingAs($this->admin)
            ->post(route('master.rooms.store'), [
                'name' => 'Meeting Room B',
                'description' => 'A small meeting room with TV screen.',
                'facilities' => 'TV Screen, Whiteboard',
                'capacity' => 5,
                'status' => 'under_maintenance',
                'image' => $image,
            ])
            ->assertRedirect(route('master.rooms.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('rooms', [
            'name' => 'Meeting Room B',
            'description' => 'A small meeting room with TV screen.',
            'facilities' => 'TV Screen, Whiteboard',
            'status' => 'under_maintenance',
        ]);

        Storage::disk('public')->assertExists('rooms/' . $image->hashName());
    }

    public function test_non_admin_cannot_create_room(): void
    {
        Storage::fake('public');
        $image = UploadedFile::fake()->image('room3.jpg');

        $this->actingAs($this->karyawan)
            ->post(route('master.rooms.store'), [
                'name' => 'Meeting Room C',
                'description' => 'A medium meeting room.',
                'facilities' => 'Whiteboard',
                'capacity' => 8,
                'status' => 'available',
                'image' => $image,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('rooms', [
            'name' => 'Meeting Room C',
        ]);

        Storage::disk('public')->assertMissing('rooms/' . $image->hashName());
    }

    public function test_super_admin_can_update_room(): void
    {
        Storage::fake('public');
        $room = Room::factory()->create();
        $newImage = UploadedFile::fake()->image('new_room.jpg');

        $this->actingAs($this->superAdmin)
            ->put(route('master.rooms.update', $room), [
                'name' => 'Updated Meeting Room A',
                'description' => 'Updated description for Meeting Room A.',
                'facilities' => 'Projector, Whiteboard, AC, Video Conference',
                'capacity' => 12,
                'status' => 'under_maintenance',
                'image' => $newImage,
            ])
            ->assertRedirect(route('master.rooms.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'name' => 'Updated Meeting Room A',
            'description' => 'Updated description for Meeting Room A.',
            'facilities' => 'Projector, Whiteboard, AC, Video Conference',
            'status' => 'under_maintenance',
        ]);

        Storage::disk('public')->assertExists('rooms/' . $newImage->hashName());
    }

    public function test_admin_can_update_room(): void
    {
        Storage::fake('public');
        $room = Room::factory()->create();
        $newImage = UploadedFile::fake()->image('new_room2.jpg');

        $this->actingAs($this->admin)
            ->put(route('master.rooms.update', $room), [
                'name' => 'Updated Meeting Room B',
                'description' => 'Updated description for Meeting Room B.',
                'facilities' => 'TV Screen, Whiteboard, Sound System',
                'capacity' => 6,
                'status' => 'available',
                'image' => $newImage,
            ])
            ->assertRedirect(route('master.rooms.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'name' => 'Updated Meeting Room B',
            'description' => 'Updated description for Meeting Room B.',
            'facilities' => 'TV Screen, Whiteboard, Sound System',
            'status' => 'available',
        ]);

        Storage::disk('public')->assertExists('rooms/' . $newImage->hashName());
    }

    public function test_non_admin_cannot_update_room(): void
    {
        Storage::fake('public');
        $room = Room::factory()->create();
        $newImage = UploadedFile::fake()->image('new_room3.jpg');

        $this->actingAs($this->karyawan)
            ->put(route('master.rooms.update', $room), [
                'name' => 'Forbidden Meeting Room C',
                'description' => 'Forbidden description.',
                'facilities' => 'Forbidden facilities.',
                'capacity' => 7,
                'status' => 'maintenance',
                'image' => $newImage,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('rooms', [
            'name' => 'Forbidden Meeting Room C',
        ]);

        Storage::disk('public')->assertMissing('rooms/' . $newImage->hashName());
    }

    public function test_super_admin_can_delete_room(): void
    {
        Storage::fake('public');
        $room = Room::factory()->create();

        $this->actingAs($this->superAdmin)
            ->delete(route('master.rooms.destroy', $room))
            ->assertRedirect(route('master.rooms.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('rooms', [
            'id' => $room->id,
        ]);
    }

    public function test_admin_can_delete_room(): void
    {
        Storage::fake('public');
        $room = Room::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('master.rooms.destroy', $room))
            ->assertRedirect(route('master.rooms.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('rooms', [
            'id' => $room->id,
        ]);
    }

    public function test_non_admin_cannot_delete_room(): void
    {
        Storage::fake('public');
        $room = Room::factory()->create();

        $this->actingAs($this->karyawan)
            ->delete(route('master.rooms.destroy', $room))
            ->assertForbidden();

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
        ]);
    }

    public function test_create_room_requires_name_capacity_status_image(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('master.rooms.store'), [])
            ->assertSessionHasErrors(['name', 'description', 'facilities', 'status']);
    }

    public function test_update_room_requires_name_capacity_status_image(): void
    {
        $room = Room::factory()->create();

        $this->actingAs($this->superAdmin)
            ->put(route('master.rooms.update', $room), [
                'name' => '',
                'capacity' => '',
                'status' => '',
                'image' => '',
            ])
            ->assertSessionHasErrors(['name', 'description', 'facilities', 'status']);
    }
}