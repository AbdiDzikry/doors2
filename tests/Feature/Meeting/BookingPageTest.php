<?php

namespace Tests\Feature\Meeting;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;

class BookingPageTest extends TestCase
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
    public function test_bookings_create_page_is_accessible_by_karyawan()
    {
        $karyawanRole = Role::findByName('Karyawan');
        $karyawan = User::factory()->create();
        $karyawan->assignRole($karyawanRole);

        $this->actingAs($karyawan);

        $response = $this->get(route('meeting.bookings.create'));

        $response->assertStatus(200);
    }
}