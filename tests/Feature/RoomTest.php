<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Room;
use Spatie\Permission\Models\Role;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class RoomTest extends TestCase
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

        Storage::fake('public');
    }

    /** @test */
    public function admin_can_view_rooms_index()
    {
        $this->actingAs($this->admin)
             ->get(route('master.rooms.index'))
             ->assertStatus(200)
             ->assertViewIs('master.rooms.index');
    }

    /** @test */
    public function admin_can_view_create_room_page()
    {
        $this->actingAs($this->admin)
             ->get(route('master.rooms.create'))
             ->assertStatus(200)
             ->assertViewIs('master.rooms.create');
    }

    /** @test */
    public function admin_can_store_a_new_room()
    {
        $this->actingAs($this->admin)
             ->post(route('master.rooms.store'), [
                 'name' => 'Meeting Room A',
                 'description' => 'A spacious meeting room',
                 'facilities' => 'Projector, Whiteboard',
                 'capacity' => 10,
                 'status' => 'available',
                 'image' => UploadedFile::fake()->image('room.jpg'),
             ])
             ->assertRedirect(route('master.rooms.index'))
             ->assertSessionHas('success', 'Room created successfully.');

        $this->assertDatabaseHas('rooms', [
            'name' => 'Meeting Room A',
            'description' => 'A spacious meeting room',
            'facilities' => 'Projector, Whiteboard',
            'capacity' => 10,
            'status' => 'available',
        ]);

        $room = Room::where('name', 'Meeting Room A')->first();
        Storage::disk('public')->assertExists($room->image_path);
    }

    /** @test */
    public function admin_can_view_edit_room_page()
    {
        $room = Room::factory()->create();

        $this->actingAs($this->admin)
             ->get(route('master.rooms.edit', $room))
             ->assertStatus(200)
             ->assertViewIs('master.rooms.edit')
             ->assertViewHas('room', $room);
    }

    /** @test */
    public function admin_can_update_a_room()
    {
        $room = Room::factory()->create([
            'name' => 'Old Room Name',
            'image_path' => UploadedFile::fake()->image('old_room.jpg')->store('rooms', 'public'),
        ]);

        $this->actingAs($this->admin)
             ->put(route('master.rooms.update', $room), [
                 'name' => 'Updated Room Name',
                 'description' => 'Updated description',
                 'facilities' => 'Updated facilities',
                 'capacity' => 15,
                 'status' => 'under_maintenance',
                 'image' => UploadedFile::fake()->image('new_room.png'),
             ])
             ->assertRedirect(route('master.rooms.index'))
             ->assertSessionHas('success', 'Room updated successfully');

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'name' => 'Updated Room Name',
            'description' => 'Updated description',
            'facilities' => 'Updated facilities',
            'capacity' => 15,
            'status' => 'under_maintenance',
        ]);

        $updatedRoom = Room::find($room->id);
        Storage::disk('public')->assertExists($updatedRoom->image_path);
        Storage::disk('public')->assertMissing($room->getOriginal('image_path')); // Old image should be deleted
    }

    /** @test */
    public function admin_can_delete_a_room()
    {
        $room = Room::factory()->create([
            'image_path' => UploadedFile::fake()->image('room_to_delete.jpg')->store('rooms', 'public'),
        ]);

        $this->actingAs($this->admin)
             ->delete(route('master.rooms.destroy', $room))
             ->assertRedirect(route('master.rooms.index'))
             ->assertSessionHas('success', 'Room deleted successfully');

        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
        Storage::disk('public')->assertMissing($room->image_path);
    }

    /** @test */
    public function room_creation_requires_validation()
    {
        $this->actingAs($this->admin)
             ->post(route('master.rooms.store'), [])
             ->assertSessionHasErrors(['name', 'description', 'facilities', 'capacity', 'status']);
    }

    /** @test */
    public function room_update_requires_validation()
    {
        $room = Room::factory()->create();

        $this->actingAs($this->admin)
             ->put(route('master.rooms.update', $room), [
                 'name' => '',
                 'description' => '',
                 'facilities' => '',
                 'capacity' => 'invalid',
                 'status' => 'invalid_status',
             ])
             ->assertSessionHasErrors(['name', 'description', 'facilities', 'capacity', 'status']);
    }
}
