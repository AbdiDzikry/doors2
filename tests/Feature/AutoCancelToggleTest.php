<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Meeting;
use App\Models\Room;
use App\Models\Configuration;
use App\Models\MeetingParticipant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

class AutoCancelToggleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create Super Admin role
        Role::create(['name' => 'Super Admin']);
    }

    /** @test */
    public function auto_cancel_command_skips_when_disabled()
    {
        // Arrange
        Configuration::create([
            'key' => 'auto_cancel_unattended_meetings',
            'value' => '0', // DISABLED
            'description' => 'Auto-cancel meetings'
        ]);

        $room = Room::factory()->create();
        $meeting = Meeting::factory()->create([
            'room_id' => $room->id,
            'start_time' => now()->subMinutes(35), // 35 mins ago
            'end_time' => now()->addMinutes(25),
            'status' => 'scheduled',
        ]);

        // No participants attended

        // Act
        $this->artisan('meetings:cancel-unattended')
            ->expectsOutput('Auto-cancel is disabled. Skipping...')
            ->assertExitCode(0);

        // Assert
        $this->assertEquals('scheduled', $meeting->fresh()->status);
    }

    /** @test */
    public function auto_cancel_command_runs_when_enabled()
    {
        // Arrange
        Configuration::create([
            'key' => 'auto_cancel_unattended_meetings',
            'value' => '1', // ENABLED
            'description' => 'Auto-cancel meetings'
        ]);

        $room = Room::factory()->create();
        $user = User::factory()->create();
        
        $meeting = Meeting::factory()->create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'start_time' => now()->subMinutes(35), // 35 mins ago
            'end_time' => now()->addMinutes(25),
            'status' => 'scheduled',
        ]);

        // Add participant but NO attendance
        MeetingParticipant::create([
            'meeting_id' => $meeting->id,
            'participant_type' => User::class,
            'participant_id' => $user->id,
            'status' => 'scheduled',
            'attended_at' => null, // NOT attended
        ]);

        // Act
        $this->artisan('meetings:cancel-unattended')
            ->assertExitCode(0);

        // Assert
        $this->assertEquals('cancelled', $meeting->fresh()->status);
    }

    /** @test */
    public function meeting_not_cancelled_if_someone_attended()
    {
        // Arrange
        Configuration::create([
            'key' => 'auto_cancel_unattended_meetings',
            'value' => '1', // ENABLED
        ]);

        $room = Room::factory()->create();
        $user = User::factory()->create();
        
        $meeting = Meeting::factory()->create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'start_time' => now()->subMinutes(35),
            'end_time' => now()->addMinutes(25),
            'status' => 'scheduled',
        ]);

        // Add participant WITH attendance
        MeetingParticipant::create([
            'meeting_id' => $meeting->id,
            'participant_type' => User::class,
            'participant_id' => $user->id,
            'status' => 'attended',
            'attended_at' => now(), // ATTENDED
        ]);

        // Act
        $this->artisan('meetings:cancel-unattended')
            ->assertExitCode(0);

        // Assert - Should NOT be cancelled
        $this->assertNotEquals('cancelled', $meeting->fresh()->status);
    }

    /** @test */
    public function admin_can_toggle_auto_cancel_setting()
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');
        
        Configuration::create([
            'key' => 'auto_cancel_unattended_meetings',
            'value' => '0',
        ]);

        // Act - Enable
        $response = $this->actingAs($admin)
            ->put(route('settings.configurations.update-bulk'), [
                'configurations' => [
                    'auto_cancel_unattended_meetings' => '1'
                ]
            ]);

        // Assert
        $response->assertRedirect(route('settings.configurations.index'));
        $response->assertSessionHas('success');
        
        $config = Configuration::where('key', 'auto_cancel_unattended_meetings')->first();
        $this->assertEquals('1', $config->value);
    }

    /** @test */
    public function unchecked_toggle_sets_value_to_zero()
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');
        
        Configuration::create([
            'key' => 'auto_cancel_unattended_meetings',
            'value' => '1', // Currently enabled
        ]);

        // Act - Disable (don't send the key in request)
        $response = $this->actingAs($admin)
            ->put(route('settings.configurations.update-bulk'), [
                'configurations' => [] // Empty = all toggles OFF
            ]);

        // Assert
        $config = Configuration::where('key', 'auto_cancel_unattended_meetings')->first();
        $this->assertEquals('0', $config->value);
    }

    /** @test */
    public function settings_page_displays_toggle_correctly()
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');
        
        Configuration::create([
            'key' => 'auto_cancel_unattended_meetings',
            'value' => '1',
        ]);

        // Act
        $response = $this->actingAs($admin)
            ->get(route('settings.configurations.index'));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Auto-cancel unattended meetings');
        $response->assertSee('30 minutes');
        $response->assertSee('checked'); // Toggle should be ON
    }
}
