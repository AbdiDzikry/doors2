<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Room;
use App\Models\Meeting;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;

class RecurringMeetingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->artisan('db:seed --class=RolesAndPermissionsSeeder');
    }

    /**
     * A basic feature test example.
     */
    public function test_recurring_meeting_can_be_booked()
    {
        $user = User::factory()->create();
        $karyawanRole = Role::findByName('Karyawan');
        $user->assignRole($karyawanRole);
        $room = Room::factory()->create();

        $this->actingAs($user);

        $startDate = Carbon::now()->addDays(1)->format('Y-m-d H:i:s');
        $endDate = Carbon::now()->addDays(1)->addHour()->format('Y-m-d H:i:s');
        $endsAt = Carbon::now()->addDays(7)->format('Y-m-d');

        $response = $this->post(route('meeting.bookings.store'), [
            'room_id' => $room->id,
            'topic' => 'Test Recurring Meeting',
            'start_time' => $startDate,
            'end_time' => $endDate,
            'recurring' => true,
            'frequency' => 'daily',
            'ends_at' => $endsAt,
        ]);

        $response->assertRedirect(route('meeting.meeting-lists.index'));
        $response->assertSessionHas('success', 'Meeting(s) booked successfully.');

        $this->assertDatabaseCount('meetings', 7); // 7 days of daily meetings

        // Verify meeting details
        $firstMeeting = Meeting::where('start_time', $startDate)->first();
        $this->assertNotNull($firstMeeting);
        $this->assertEquals('Test Recurring Meeting', $firstMeeting->topic);
        $this->assertEquals($room->id, $firstMeeting->room_id);
        $this->assertEquals($user->id, $firstMeeting->user_id);
        $this->assertEquals('pending', $firstMeeting->status);

        $lastMeetingDate = Carbon::parse($endsAt)->startOfDay();
        $lastMeeting = Meeting::whereDate('start_time', $lastMeetingDate)->orderBy('start_time', 'desc')->first();
        $this->assertNotNull($lastMeeting);
        $this->assertEquals('Test Recurring Meeting', $lastMeeting->topic);
    }
}
