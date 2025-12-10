<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Room;
use App\Models\User;
use App\Models\Meeting;
use App\Models\MeetingParticipant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use App\Events\MeetingStatusUpdated;
use App\Events\ParticipantCheckedIn;

class TabletFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $room;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Artisan::call('optimize:clear'); // Clear caches before each test
        $this->room = Room::factory()->create(['name' => 'Test Room']);
        $this->user = User::factory()->create(['npk' => '12345']);
    }

    /** @test */
    public function it_displays_the_tablet_room_display_view()
    {
        $response = $this->get(route('tablet.room.display', $this->room));
        $response->assertOk();
        $response->assertViewIs('tablet.room-display');
        $response->assertViewHas('room', $this->room);
        $response->assertViewHas('meetings');
    }

    /** @test */
    public function it_gets_todays_meetings_for_a_room()
    {
        $meeting = Meeting::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
            'start_time' => Carbon::now()->setHour(9)->setMinute(0),
            'end_time' => Carbon::now()->setHour(10)->setMinute(0),
        ]);

        $response = $this->getJson(route('api.tablet.room.meetings', $this->room));
        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['id' => $meeting->id]);
    }

    /** @test */
    public function it_gets_room_status_with_current_and_upcoming_meetings()
    {
        // Current meeting
        $currentMeeting = Meeting::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
            'start_time' => Carbon::now()->subMinutes(30),
            'end_time' => Carbon::now()->addMinutes(30),
        ]);
        MeetingParticipant::factory()->create([
            'meeting_id' => $currentMeeting->id,
            'participable_id' => $this->user->id,
            'participable_type' => User::class,
        ]);

        // Upcoming meeting
        $upcomingMeeting = Meeting::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
            'start_time' => Carbon::now()->addHours(1),
            'end_time' => Carbon::now()->addHours(2),
        ]);

        $response = $this->getJson(route('api.tablet.room.status', $this->room));
        $response->assertOk();
        $response->assertJson([
            'is_available' => false,
            'current_meeting' => [
                'id' => $currentMeeting->id,
                'topic' => $currentMeeting->topic,
                'organizer' => $this->user->name,
                'participants' => [
                    [
                        'id' => $currentMeeting->meetingParticipants->first()->id,
                        'name' => $this->user->name,
                        'type' => 'User',
                        'attended_at' => null,
                    ]
                ]
            ],
            'upcoming_meetings' => [
                [
                    'id' => $upcomingMeeting->id,
                    'topic' => $upcomingMeeting->topic,
                ]
            ]
        ]);
    }

    /** @test */
    public function it_can_book_a_room_on_the_spot()
    {
        Event::fake();

        $response = $this->postJson(route('api.tablet.room.book-now', $this->room), [
            'topic' => 'Quick Meeting',
            'duration' => 30,
            'npk' => $this->user->npk,
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'success', 'message' => 'Ruangan berhasil dibooking.']);
        $this->assertDatabaseHas('meetings', [
            'room_id' => $this->room->id,
            'topic' => 'Quick Meeting',
            'user_id' => $this->user->id,
            'status' => 'confirmed',
        ]);
        Event::assertDispatched(MeetingStatusUpdated::class);
    }

    /** @test */
    public function it_prevents_on_the_spot_booking_if_room_is_occupied()
    {
        Meeting::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
            'start_time' => Carbon::now()->subMinutes(10),
            'end_time' => Carbon::now()->addMinutes(20),
        ]);

        $response = $this->postJson(route('api.tablet.room.book-now', $this->room), [
            'topic' => 'Quick Meeting',
            'duration' => 30,
            'npk' => $this->user->npk,
        ]);

        $response->assertStatus(409);
        $response->assertJson(['status' => 'error', 'message' => 'Ruangan sudah tidak tersedia atau ada jadwal berdekatan.']);
    }

    /** @test */
    public function it_can_check_in_a_participant()
    {
        Event::fake();

        $meeting = Meeting::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
            'start_time' => Carbon::now()->subMinutes(10),
            'end_time' => Carbon::now()->addMinutes(50),
        ]);
        MeetingParticipant::factory()->create([
            'meeting_id' => $meeting->id,
            'participable_id' => $this->user->id,
            'participable_type' => User::class,
            'attended_at' => null,
        ]);

        $response = $this->postJson(route('api.meeting.check-in', $meeting), [
            'participant_id' => $participant->id,
            'npk' => $this->user->npk,
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'success', 'message' => 'Absensi berhasil.']);
        $this->assertNotNull($participant->fresh()->attended_at);
        Event::assertDispatched(ParticipantCheckedIn::class);
    }

    /** @test */
    public function it_prevents_check_in_with_invalid_npk()
    {
        $meeting = Meeting::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
            'start_time' => Carbon::now()->subMinutes(10),
            'end_time' => Carbon::now()->addMinutes(50),
        ]);
        MeetingParticipant::factory()->create([
            'meeting_id' => $meeting->id,
            'participable_id' => $this->user->id,
            'participable_type' => User::class,
            'attended_at' => null,
        ]);

        $response = $this->postJson(route('api.meeting.check-in', $meeting), [
            'participant_id' => $participant->id,
            'npk' => 'invalid_npk',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['status' => 'error', 'message' => 'NPK verifikator tidak valid.']);
    }

    /** @test */
    public function it_can_extend_a_meeting()
    {
        Event::fake();

        $meeting = Meeting::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
            'start_time' => Carbon::now()->subMinutes(30),
            'end_time' => Carbon::now()->addMinutes(30),
        ]);
        $originalEndTime = $meeting->end_time;

        $response = $this->postJson(route('api.tablet.meeting.extend', $meeting), [
            'duration' => 30,
            'npk' => $this->user->npk,
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'success', 'message' => 'Meeting berhasil diperpanjang.']);
        $this->assertEquals(Carbon::parse($originalEndTime)->addMinutes(30), $meeting->fresh()->end_time);
        Event::assertDispatched(MeetingStatusUpdated::class);
    }

    /** @test */
    public function it_prevents_meeting_extension_if_not_organizer()
    {
        $otherUser = User::factory()->create(['npk' => '54321']);
        $meeting = Meeting::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
            'start_time' => Carbon::now()->subMinutes(30),
            'end_time' => Carbon::now()->addMinutes(30),
        ]);

        $response = $this->postJson(route('api.tablet.meeting.extend', $meeting), [
            'duration' => 30,
            'npk' => $otherUser->npk,
        ]);

        $response->assertStatus(403);
        $response->assertJson(['status' => 'error', 'message' => 'Hanya pemesan ruangan yang bisa memperpanjang meeting.']);
    }
}
