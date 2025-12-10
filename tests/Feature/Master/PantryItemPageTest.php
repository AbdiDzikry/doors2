<?php

namespace Tests\Feature\Master;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;

class PantryItemPageTest extends TestCase
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
    public function test_pantry_items_index_page_is_accessible_by_admin()
    {
        $adminRole = Role::findByName('Admin');
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $this->actingAs($admin);

        $response = $this->get(route('master.pantry-items.index'));

        $response->assertStatus(200);
    }
}