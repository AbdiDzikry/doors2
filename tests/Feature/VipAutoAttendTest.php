<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Meeting;
use App\Models\Room;
use App\Models\PriorityGuest;
use App\Models\MeetingParticipant;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VipAutoAttendTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function participants_auto_marked_attended_when_priority_guest_set_on_creation()
    {
        // Arrange
        $priorityGuest = PriorityGuest::create([
            'name' => 'CEO',
            'level' => 1
        ]);
        
        $room = Room::factory()->create();
        $organizer = User::factory()->create();
        $participant1 = User::factory()->create();
        $participant2 = User::factory()->create();

        // Act
        $meeting = Meeting::create([
            'user_id' => $organizer->id,
            'room_id' => $room->id,
            'topic' => 'VIP Board Meeting',
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(2),
            'priority_guest_id' => $priorityGuest->id,
            'status' => 'scheduled',
        ]);

        // Add participants
        MeetingParticipant::create([
            'meeting_id' => $meeting->id,
            'participant_type' => User::class,
            'participant_id' => $participant1->id,
            'status' => 'scheduled',
        ]);

        MeetingParticipant::create([
            'meeting_id' => $meeting->id,
            'participant_type' => User::class,
            'participant_id' => $participant2->id,
            'status' => 'scheduled',
        ]);

        // Trigger observer by refreshing
        $meeting->refresh();

        // Assert
        $participants = $meeting->meetingParticipants;
        foreach ($participants as $participant) {
            $this->assertNotNull(
                $participant->attended_at,
                "Participant {$participant->id} should be auto-marked as attended"
            );
            $this->assertEquals('attended', $participant->status);
        }
    }

    /** @test */
    public function participants_auto_marked_attended_when_priority_guest_added_later()
    {
        // Arrange
        $priorityGuest = PriorityGuest::create([
            'name' => 'VIP Client',
            'level' => 2
        ]);
        
        $room = Room::factory()->create();
        $organizer = User::factory()->create();
        
        // Create meeting WITHOUT priority guest
        $meeting = Meeting::create([
            'user_id' => $organizer->id,
            'room_id' => $room->id,
            'topic' => 'Regular Meeting',
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(2),
            'status' => 'scheduled',
        ]);

        $participant = MeetingParticipant::create([
            'meeting_id' => $meeting->id,
            'participant_type' => User::class,
            'participant_id' => $organizer->id,
            'status' => 'scheduled',
        ]);

        // Verify NOT attended initially
        $this->assertNull($participant->attended_at);

        // Act - Add priority guest
        $meeting->update(['priority_guest_id' => $priorityGuest->id]);

        // Assert
        $this->assertNotNull(
            $participant->fresh()->attended_at,
            'Participant should be auto-marked when priority guest added'
        );
    }

    /** @test */
    public function meeting_without_priority_guest_does_not_auto_attend()
    {
        // Arrange
        $room = Room::factory()->create();
        $organizer = User::factory()->create();
        
        $meeting = Meeting::create([
            'user_id' => $organizer->id,
            'room_id' => $room->id,
            'topic' => 'Regular Meeting',
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(2),
            'status' => 'scheduled',
        ]);

        $participant = MeetingParticipant::create([
            'meeting_id' => $meeting->id,
            'participant_type' => User::class,
            'participant_id' => $organizer->id,
            'status' => 'scheduled',
        ]);

        // Assert
        $this->assertNull(
            $participant->fresh()->attended_at,
            'Regular meeting should NOT auto-mark attendance'
        );
    }

    /** @test */
    public function priority_guest_badge_displays_on_meeting_details()
    {
        // Arrange
        $priorityGuest = PriorityGuest::create([
            'name' => 'Executive',
            'level' => 3
        ]);
        
        $room = Room::factory()->create();
        $user = User::factory()->create();
        
        $meeting = Meeting::create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'topic' => 'Executive Meeting',
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(2),
            'priority_guest_id' => $priorityGuest->id,
            'status' => 'scheduled',
        ]);

        // Act
        $response = $this->actingAs($user)
            ->get(route('meeting.meeting-lists.show', $meeting));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Priority Guest');
        $response->assertSee('Executive');
        $response->assertSee('All participants auto-marked as attended');
    }
}
