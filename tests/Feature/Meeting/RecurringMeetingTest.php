<?php

namespace Tests\Feature\Meeting;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Room;
use App\Models\Meeting;
use App\Models\RecurringMeeting;
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

        // Disable permission cache for tests
        config()->set('permission.cache.duration', 0);

        // Clear application cache to ensure routes are reloaded
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    }

    public function test_karyawan_can_create_a_daily_recurring_meeting()
    {
        $karyawanRole = Role::findByName('Karyawan');
        $karyawan = User::factory()->create();
        $karyawan->assignRole($karyawanRole);

        $this->actingAs($karyawan);

        $room = Room::factory()->create();
        $startTime = Carbon::now()->addDay()->setHour(9)->setMinute(0)->setSecond(0);
        $endTime = Carbon::now()->addDay()->setHour(10)->setMinute(0)->setSecond(0);
        $endsAt = Carbon::now()->addDay()->addDays(2);

        $response = $this->post(route('meeting.bookings.store'), [
            'room_id' => $room->id,
            'topic' => 'Daily Recurring Meeting',
            'start_time' => $startTime->toDateTimeString(),
            'end_time' => $endTime->toDateTimeString(),
            'recurring' => true,
            'frequency' => 'daily',
            'ends_at' => $endsAt->toDateString(),
        ]);

        $response->assertRedirect(route('meeting.meeting-lists.index'));

        $this->assertDatabaseCount('meetings', 3);
    }

    public function test_karyawan_can_create_a_weekly_recurring_meeting()
    {
        $karyawanRole = Role::findByName('Karyawan');
        $karyawan = User::factory()->create();
        $karyawan->assignRole($karyawanRole);

        $this->actingAs($karyawan);

        $room = Room::factory()->create();
        $startTime = Carbon::now()->addDay()->setHour(9)->setMinute(0)->setSecond(0);
        $endTime = Carbon::now()->addDay()->setHour(10)->setMinute(0)->setSecond(0);
        $endsAt = Carbon::now()->addDay()->addWeeks(2);

        $response = $this->post(route('meeting.bookings.store'), [
            'room_id' => $room->id,
            'topic' => 'Weekly Recurring Meeting',
            'start_time' => $startTime->toDateTimeString(),
            'end_time' => $endTime->toDateTimeString(),
            'recurring' => true,
            'frequency' => 'weekly',
            'ends_at' => $endsAt->toDateString(),
        ]);

        $response->assertRedirect(route('meeting.meeting-lists.index'));

        $this->assertDatabaseCount('meetings', 3);
    }

    public function test_karyawan_can_create_a_monthly_recurring_meeting()
    {
        $karyawanRole = Role::findByName('Karyawan');
        $karyawan = User::factory()->create();
        $karyawan->assignRole($karyawanRole);

        $this->actingAs($karyawan);

        $room = Room::factory()->create();
        $startTime = Carbon::now()->addDay()->setHour(9)->setMinute(0)->setSecond(0);
        $endTime = Carbon::now()->addDay()->setHour(10)->setMinute(0)->setSecond(0);
        $endsAt = Carbon::now()->addDay()->addMonths(2);

        $response = $this->post(route('meeting.bookings.store'), [
            'room_id' => $room->id,
            'topic' => 'Monthly Recurring Meeting',
            'start_time' => $startTime->toDateTimeString(),
            'end_time' => $endTime->toDateTimeString(),
            'recurring' => true,
            'frequency' => 'monthly',
            'ends_at' => $endsAt->toDateString(),
        ]);

        $response->assertRedirect(route('meeting.meeting-lists.index'));

        $this->assertDatabaseCount('meetings', 3);
    }

    public function test_super_admin_can_access_recurring_meetings_index()
    {
        $superAdminRole = Role::findByName('Super Admin');
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($superAdminRole);

        $response = $this->get(route('master.recurring-meetings.index'));

        $response->assertOk();
        $response->assertViewIs('meetings.recurring-meetings.index');
    }

    public function test_admin_can_access_recurring_meetings_index()
    {
        $adminRole = Role::findByName('Admin');
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $response = $this->get(route('master.recurring-meetings.index'));

        $response->assertOk();
        $response->assertViewIs('meetings.recurring-meetings.index');
    }

    public function test_karyawan_cannot_access_recurring_meetings_index()
    {
        $karyawanRole = Role::findByName('Karyawan');
        $karyawan = User::factory()->create();
        $karyawan->assignRole($karyawanRole);

        $response = $this->get(route('master.recurring-meetings.index'));

        $response->assertForbidden();
    }

    public function test_guest_cannot_access_recurring_meetings_index()
    {
        $response = $this->get(route('master.recurring-meetings.index'));

        $response->assertRedirect('/login');
    }

    public function test_super_admin_can_view_recurring_meeting()
    {
        $superAdminRole = Role::findByName('Super Admin');
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($superAdminRole);

        $this->actingAs($superAdmin);

        $recurringMeeting = RecurringMeeting::factory()->create();

        $response = $this->get(route('master.recurring-meetings.show', $recurringMeeting->id));

        $response->assertOk();
        $response->assertViewIs('meetings.recurring-meetings.show');
        $response->assertViewHas('recurringMeeting', $recurringMeeting);
    }

    public function test_admin_can_view_recurring_meeting()
    {
        $adminRole = Role::findByName('Admin');
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $this->actingAs($admin);

        $recurringMeeting = RecurringMeeting::factory()->create();

        $response = $this->get(route('master.recurring-meetings.show', $recurringMeeting->id));

        $response->assertOk();
        $response->assertViewIs('meetings.recurring-meetings.show');
        $response->assertViewHas('recurringMeeting', $recurringMeeting);
    }

    public function test_super_admin_can_edit_recurring_meeting()
    {
        $superAdminRole = Role::findByName('Super Admin');
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($superAdminRole);

        $this->actingAs($superAdmin);

        $recurringMeeting = RecurringMeeting::factory()->create();

        $response = $this->get(route('master.recurring-meetings.edit', $recurringMeeting->id));

        $response->assertOk();
        $response->assertViewIs('meetings.recurring-meetings.edit');
        $response->assertViewHas('recurringMeeting', $recurringMeeting);
    }

    public function test_admin_can_edit_recurring_meeting()
    {
        $adminRole = Role::findByName('Admin');
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $this->actingAs($admin);

        $recurringMeeting = RecurringMeeting::factory()->create();

        $response = $this->get(route('master.recurring-meetings.edit', $recurringMeeting->id));

        $response->assertOk();
        $response->assertViewIs('meetings.recurring-meetings.edit');
        $response->assertViewHas('recurringMeeting', $recurringMeeting);
    }

    public function test_super_admin_can_update_recurring_meeting()
    {
        $superAdminRole = Role::findByName('Super Admin');
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($superAdminRole);

        $this->actingAs($superAdmin);

        $recurringMeeting = RecurringMeeting::factory()->create();

        $updatedData = [
            'frequency' => 'weekly',
            'ends_at' => Carbon::now()->addMonth()->toDateString(),
        ];

        $response = $this->put(route('master.recurring-meetings.update', $recurringMeeting->id), $updatedData);

        $response->assertRedirect(route('master.recurring-meetings.index'));
        $this->assertDatabaseHas('recurring_meetings', array_merge(['id' => $recurringMeeting->id], $updatedData));
    }

    public function test_admin_can_update_recurring_meeting()
    {
        $adminRole = Role::findByName('Admin');
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $this->actingAs($admin);

        $recurringMeeting = RecurringMeeting::factory()->create();

        $updatedData = [
            'frequency' => 'monthly',
            'ends_at' => Carbon::now()->addMonths(2)->toDateString(),
        ];

        $response = $this->put(route('master.recurring-meetings.update', $recurringMeeting->id), $updatedData);

        $response->assertRedirect(route('master.recurring-meetings.index'));
        $this->assertDatabaseHas('recurring_meetings', array_merge(['id' => $recurringMeeting->id], $updatedData));
    }

    public function test_super_admin_can_delete_recurring_meeting()
    {
        $superAdminRole = Role::findByName('Super Admin');
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($superAdminRole);

        $this->actingAs($superAdmin);

        $recurringMeeting = RecurringMeeting::factory()->create();

        $response = $this->delete(route('master.recurring-meetings.destroy', $recurringMeeting->id));

        $response->assertRedirect(route('master.recurring-meetings.index'));
        $this->assertDatabaseMissing('recurring_meetings', ['id' => $recurringMeeting->id]);
    }

    public function test_admin_can_delete_recurring_meeting()
    {
        $adminRole = Role::findByName('Admin');
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $this->actingAs($admin);

        $recurringMeeting = RecurringMeeting::factory()->create();

        $response = $this->delete(route('master.recurring-meetings.destroy', $recurringMeeting->id));

        $response->assertRedirect(route('master.recurring-meetings.index'));
        $this->assertDatabaseMissing('recurring_meetings', ['id' => $recurringMeeting->id]);
    }
}