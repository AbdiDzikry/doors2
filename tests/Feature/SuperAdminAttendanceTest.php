<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Meeting;
use App\Models\Room;
use App\Models\MeetingParticipant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

class SuperAdminAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'Super Admin']);
        Role::create(['name' => 'User']);
    }

    /** @test */
    public function super_admin_can_mark_attendance_before_meeting_starts()
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');
        
        $room = Room::factory()->create();
        $meeting = Meeting::factory()->create([
            'room_id' => $room->id,
            'start_time' => now()->addHours(2), // Meeting in future
            'end_time' => now()->addHours(3),
        ]);
        
        $participant = MeetingParticipant::create([
            'meeting_id' => $meeting->id,
            'participant_type' => User::class,
            'participant_id' => $admin->id,
            'status' => 'scheduled',
        ]);

        // Act
        $response = $this->actingAs($admin)
            ->post(route('meeting.meetings.attendance.store', $meeting), [
                'participant_ids' => [$participant->id]
            ]);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertNotNull($participant->fresh()->attended_at);
    }

    /** @test */
    public function super_admin_can_mark_attendance_after_window_closed()
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');
        
        $room = Room::factory()->create();
        $meeting = Meeting::factory()->create([
            'room_id' => $room->id,
            'start_time' => now()->subHours(3), // Meeting ended 3 hours ago
            'end_time' => now()->subHours(2),
        ]);
        
        $participant = MeetingParticipant::create([
            'meeting_id' => $meeting->id,
            'participant_type' => User::class,
            'participant_id' => $admin->id,
            'status' => 'scheduled',
        ]);

        // Act
        $response = $this->actingAs($admin)
            ->post(route('meeting.meetings.attendance.store', $meeting), [
                'participant_ids' => [$participant->id]
            ]);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertNotNull($participant->fresh()->attended_at);
    }

    /** @test */
    public function regular_user_cannot_mark_attendance_before_meeting_starts()
    {
        // Arrange
        $user = User::factory()->create();
        $user->assignRole('User');
        
        $room = Room::factory()->create();
        $meeting = Meeting::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user->id, // User is organizer
            'start_time' => now()->addHours(1), // Meeting in future
            'end_time' => now()->addHours(2),
        ]);
        
        $participant = MeetingParticipant::create([
            'meeting_id' => $meeting->id,
            'participant_type' => User::class,
            'participant_id' => $user->id,
            'status' => 'scheduled',
        ]);

        // Act
        $response = $this->actingAs($user)
            ->post(route('meeting.meetings.attendance.store', $meeting), [
                'participant_ids' => [$participant->id]
            ]);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Attendance cannot be recorded before the meeting starts.');
        $this->assertNull($participant->fresh()->attended_at);
    }

    /** @test */
    public function regular_user_cannot_mark_attendance_after_window_closed()
    {
        // Arrange
        $user = User::factory()->create();
        $user->assignRole('User');
        
        $room = Room::factory()->create();
        $meeting = Meeting::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'start_time' => now()->subHours(2),
            'end_time' => now()->subHours(1), // Window closed 30+ mins ago
        ]);
        
        $participant = MeetingParticipant::create([
            'meeting_id' => $meeting->id,
            'participant_type' => User::class,
            'participant_id' => $user->id,
            'status' => 'scheduled',
        ]);

        // Act
        $response = $this->actingAs($user)
            ->post(route('meeting.meetings.attendance.store', $meeting), [
                'participant_ids' => [$participant->id]
            ]);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Attendance window closed (30 minutes after meeting ended).');
        $this->assertNull($participant->fresh()->attended_at);
    }
}
