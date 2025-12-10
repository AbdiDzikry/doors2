<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;

class ExternalParticipantTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'Super Admin']);
        Role::create(['name' => 'Admin']);

        // Create an admin user
        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');

        $this->withoutExceptionHandling();
    }

    /** @test */
    public function admin_can_export_external_participants()
    {
        $response = $this->actingAs($this->admin)
             ->get(route('master.external-participants.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('Content-Disposition', 'attachment; filename=external_participants.xlsx');
    }
}
