<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use App\Models\Configuration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfigurationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed('RolesAndPermissionsSeeder');
    }

    public function test_super_admin_can_access_configurations_index_page()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');
        $this->actingAs($superAdmin);

        $response = $this->get(route('settings.configurations.index'));

        $response->assertStatus(200);
    }

    public function test_super_admin_can_update_a_configuration()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');
        $this->actingAs($superAdmin);

        $configuration = Configuration::factory()->create();

        $response = $this->put(route('settings.configurations.update', $configuration), [
            'value' => 'New Value',
        ]);

        $response->assertRedirect(route('settings.configurations.index'));
        $this->assertDatabaseHas('configurations', [
            'id' => $configuration->id,
            'value' => 'New Value',
        ]);
    }
}
