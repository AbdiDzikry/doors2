<?php

namespace Tests\Feature\Master;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Room;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class RoomControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(); // Seed the database with roles

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');
        $this->admin->assignRole('Karyawan'); // Assign Karyawan role for accessing meeting routes
    }

    /**
     * A comprehensive test for creating and updating a room with an image.
     *
     * @return void
     */
    public function test_admin_can_create_and_update_room_with_image()
    {
        Storage::fake('public');

        // 1. === CREATE ROOM ===
        $imageFile = UploadedFile::fake()->image('room1.jpg', 800, 600);
        $roomData = [
            'name' => 'Test Room Alpha',
            'description' => 'A test description.',
            'facilities' => 'Whiteboard, Projector',
            'capacity' => 15,
            'floor' => '10th Floor',
            'status' => 'available',
            'image' => $imageFile,
        ];

        $response = $this->actingAs($this->admin)->post(route('master.rooms.store'), $roomData);

        // Assertions for creation
        $response->assertRedirect(route('master.rooms.index'));
        $response->assertSessionHas('message', 'Room created successfully.');
        
        $this->assertDatabaseHas('rooms', [
            'name' => 'Test Room Alpha',
            'floor' => '10th Floor',
        ]);

        $room = Room::where('name', 'Test Room Alpha')->first();
        $this->assertNotNull($room->image_path, 'Image path should not be null after creation.');
        Storage::disk('public')->assertExists($room->image_path);
        
        // 2. === UPDATE ROOM ===
        $newImageFile = UploadedFile::fake()->image('room_new.png', 800, 600);
        $updatedRoomData = [
            'name' => 'Test Room Alpha Updated',
            'description' => 'An updated description.',
            'capacity' => 20,
            'floor' => '11th Floor',
            'status' => 'under_maintenance',
            'image' => $newImageFile,
        ];

        $oldImagePath = $room->image_path; // Capture old image path before update

        $response = $this->actingAs($this->admin)->put(route('master.rooms.update', $room->id), $updatedRoomData);
        
        // Assertions for update
        $response->assertRedirect(route('master.rooms.edit', $room->id));
        $response->assertSessionHas('message', 'Room updated successfully.');

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'name' => 'Test Room Alpha Updated',
            'capacity' => 20,
            'floor' => '11th Floor',
        ]);

        $room->refresh();
        $this->assertNotNull($room->image_path, 'Image path should not be null after update.');
        Storage::disk('public')->assertExists($room->image_path);
        Storage::disk('public')->assertMissing($oldImagePath); // Assert old image is deleted

        // 3. === VERIFY IMAGE ON RESERVATION PAGE ===
        $response = $this->actingAs($this->admin)->get(route('meeting.room-reservations.index'));

        $response->assertStatus(200);
        $response->assertSee(e($room->name));
        $response->assertSee(Storage::url($room->image_path));
    }
}