<?php

namespace Tests\Feature\Master;

use App\Models\ExternalParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExternalParticipantTest extends TestCase
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

    #[test]
    public function super_admin_can_access_external_participants_index(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('master.external-participants.index'))
            ->assertOk();
    }

    #[test]
    public function admin_can_access_external_participants_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('master.external-participants.index'))
            ->assertOk();
    }

    #[test]
    public function non_admin_cannot_access_external_participants_index(): void
    {
        $this->actingAs($this->karyawan)
            ->get(route('master.external-participants.index'))
            ->assertForbidden();
    }

    #[test]
    public function guest_cannot_access_external_participants_index(): void
    {
        $this->get(route('master.external-participants.index'))
            ->assertRedirect(route('login'));
    }

    #[test]
    public function super_admin_can_create_external_participant(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('master.external-participants.store'), [
                'name' => 'Test Participant',
                'email' => 'test@example.com',
                'company' => 'Test Company',
                'type' => 'external',
            ])
            ->assertRedirect(route('master.external-participants.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('external_participants', [
            'name' => 'Test Participant',
            'email' => 'test@example.com',
            'company' => 'Test Company',
        ]);
    }

    #[test]
    public function admin_can_create_external_participant(): void
    {
        $this->actingAs($this->admin)
            ->post(route('master.external-participants.store'), [
                'name' => 'Another Participant',
                'email' => 'another@example.com',
                'company' => 'Another Company',
                'type' => 'external',
            ])
            ->assertRedirect(route('master.external-participants.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('external_participants', [
            'name' => 'Another Participant',
            'email' => 'another@example.com',
            'company' => 'Another Company',
        ]);
    }

    #[test]
    public function non_admin_cannot_create_external_participant(): void
    {
        $this->actingAs($this->karyawan)
            ->post(route('master.external-participants.store'), [
                'name' => 'Forbidden Participant',
                'email' => 'forbidden@example.com',
                'company' => 'Forbidden Company',
                'type' => 'external',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('external_participants', [
            'name' => 'Forbidden Participant',
        ]);
    }

    #[test]
    public function super_admin_can_update_external_participant(): void
    {
        $participant = ExternalParticipant::factory()->create();

        $this->actingAs($this->superAdmin)
            ->put(route('master.external-participants.update', $participant), [
                'name' => 'Updated Participant',
                'email' => 'updated@example.com',
                'company' => 'Updated Company',
                'type' => 'external',
            ])
            ->assertRedirect(route('master.external-participants.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('external_participants', [
            'id' => $participant->id,
            'name' => 'Updated Participant',
            'email' => 'updated@example.com',
            'company' => 'Updated Company',
        ]);
    }

    #[test]
    public function admin_can_update_external_participant(): void
    {
        $participant = ExternalParticipant::factory()->create();

        $this->actingAs($this->admin)
            ->put(route('master.external-participants.update', $participant), [
                'name' => 'Admin Updated Participant',
                'email' => 'admin.updated@example.com',
                'company' => 'Admin Updated Company',
                'type' => 'external',
            ])
            ->assertRedirect(route('master.external-participants.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('external_participants', [
            'id' => $participant->id,
            'name' => 'Admin Updated Participant',
            'email' => 'admin.updated@example.com',
            'company' => 'Admin Updated Company',
        ]);
    }

    #[test]
    public function non_admin_cannot_update_external_participant(): void
    {
        $participant = ExternalParticipant::factory()->create();

        $this->actingAs($this->karyawan)
            ->put(route('master.external-participants.update', $participant), [
                'name' => 'Forbidden Update',
                'email' => 'forbidden.update@example.com',
                'company' => 'Forbidden Update Company',
                'type' => 'external',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('external_participants', [
            'name' => 'Forbidden Update',
        ]);
    }

    #[test]
    public function super_admin_can_delete_external_participant(): void
    {
        $participant = ExternalParticipant::factory()->create();

        $this->actingAs($this->superAdmin)
            ->delete(route('master.external-participants.destroy', $participant))
            ->assertRedirect(route('master.external-participants.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('external_participants', [
            'id' => $participant->id,
        ]);
    }

    #[test]
    public function admin_can_delete_external_participant(): void
    {
        $participant = ExternalParticipant::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('master.external-participants.destroy', $participant))
            ->assertRedirect(route('master.external-participants.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('external_participants', [
            'id' => $participant->id,
        ]);
    }

    #[test]
    public function non_admin_cannot_delete_external_participant(): void
    {
        $participant = ExternalParticipant::factory()->create();

        $this->actingAs($this->karyawan)
            ->delete(route('master.external-participants.destroy', $participant))
            ->assertForbidden();

        $this->assertDatabaseHas('external_participants', [
            'id' => $participant->id,
        ]);
    }

    #[test]
    public function create_external_participant_requires_name_email_company(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('master.external-participants.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'type']);
    }

    #[test]
    public function update_external_participant_requires_name_email_company(): void
    {
        $participant = ExternalParticipant::factory()->create();

        $this->actingAs($this->superAdmin)
            ->put(route('master.external-participants.update', $participant), [
                'name' => '',
                'email' => '',
                'company' => '',
                'type' => '',
            ])
            ->assertSessionHasErrors(['name', 'email', 'type']);
    }
}