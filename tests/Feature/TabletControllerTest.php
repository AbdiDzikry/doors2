<?php

namespace Tests\Feature;

use App\Events\MeetingStatusUpdated;
use App\Models\ExternalParticipant;
use App\Models\Meeting;
use App\Models\MeetingParticipant;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TabletControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a user for authentication in API tests
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function test_show_room_displays_correct_view_and_data()
    {
        $room = Room::factory()->create();

        $response = $this->get(route('tablet.room.show', $room));

        $response->assertOk();
        $response->assertViewIs('tablet.room-display');
        $response->assertViewHas('room', $room);
        $response->assertViewHas('currentMeeting', null);
    }

    /** @test */
    public function test_show_room_displays_current_meeting()
    {
        $room = Room::factory()->create();
        $user = User::factory()->create();
        $meeting = Meeting::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'start_time' => Carbon::now()->subMinutes(30),
            'end_time' => Carbon::now()->addMinutes(30),
        ]);

        $response = $this->get(route('tablet.room.show', $room));

        $response->assertOk();
        $response->assertViewIs('tablet.room-display');
        $response->assertViewHas('room', $room);
        $response->assertViewHas('currentMeeting');
        $this->assertEquals($meeting->id, $response->viewData('currentMeeting')->id);
    }

    /** @test */
    public function test_book_now_creates_a_meeting()
    {
        Event::fake();
        $room = Room::factory()->create();
        $user = User::factory()->create();

        $response = $this->postJson(route('api.tablet.room.book-now', $room), [
            'topic' => 'Quick Meeting',
            'duration' => 15,
        ]);

        $response->assertCreated();
        $response->assertJson(['message' => 'Meeting booked successfully!']);
        $this->assertDatabaseHas('meetings', [
            'room_id' => $room->id,
            'user_id' => $this->user->id, // Use the ID of the authenticated user
            'topic' => 'Quick Meeting',
            'meeting_type' => 'on-the-spot',
        ]);
        Event::assertDispatched(MeetingStatusUpdated::class);
    }

    /** @test */
    public function test_book_now_prevents_overlapping_meetings()
    {
        Event::fake();
        $room = Room::factory()->create();
        $user = User::factory()->create();
        Meeting::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'start_time' => Carbon::now()->subMinutes(5),
            'end_time' => Carbon::now()->addMinutes(20),
        ]);

        $response = $this->postJson(route('api.tablet.room.book-now', $room), [
            'topic' => 'Overlapping Meeting',
            'duration' => 30,
        ]);

        $response->assertStatus(409);
        $response->assertJson(['message' => 'Room is already booked for this time slot.']);
        $this->assertDatabaseMissing('meetings', [
            'topic' => 'Overlapping Meeting',
        ]);
        Event::assertNotDispatched(MeetingStatusUpdated::class);
    }

    /** @test */
    public function test_check_in_participant_updates_status()
    {
        $user = User::factory()->create();
        $meeting = Meeting::factory()->create();
        $meetingParticipant = MeetingParticipant::factory()->create([
            'meeting_id' => $meeting->id,
            'participant_id' => $user->id,
            'participant_type' => User::class,
            'status' => 'pending',
        ]);

        $response = $this->postJson(route('api.meeting.check-in', $meeting), [
            'participant_id' => $user->id,
            'participant_type' => User::class,
        ]);

        $response->assertOk();
        $response->assertJson(['message' => 'Participant checked in successfully!']);
        $this->assertDatabaseHas('meeting_participants', [
            'id' => $meetingParticipant->id,
            'status' => 'attended',
        ]);
    }

    /** @test */
    public function test_check_in_external_participant_updates_status()
    {
        $externalParticipant = ExternalParticipant::factory()->create();
        $meeting = Meeting::factory()->create();
        $meetingParticipant = MeetingParticipant::factory()->create([
            'meeting_id' => $meeting->id,
            'participant_id' => $externalParticipant->id,
            'participant_type' => ExternalParticipant::class,
            'status' => 'pending',
        ]);

        $response = $this->postJson(route('api.meeting.check-in', $meeting), [
            'participant_id' => $externalParticipant->id,
            'participant_type' => ExternalParticipant::class,
        ]);

        $response->assertOk();
        $response->assertJson(['message' => 'Participant checked in successfully!']);
        $this->assertDatabaseHas('meeting_participants', [
            'id' => $meetingParticipant->id,
            'status' => 'attended',
        ]);
    }

    /** @test */
    public function test_check_in_participant_not_found()
    {
        $meeting = Meeting::factory()->create();

        $response = $this->postJson(route('api.meeting.check-in', $meeting), [
            'participant_id' => 999, // Non-existent ID
            'participant_type' => User::class,
        ]);

        $response->assertNotFound();
        $response->assertJson(['message' => 'Participant not found for this meeting.']);
    }
}
