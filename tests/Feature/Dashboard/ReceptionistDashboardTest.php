<?php

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Models\Meeting;
use App\Models\Room;
use App\Models\PantryItem;
use App\Models\PantryOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceptionistDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed('RolesAndPermissionsSeeder');
    }

    public function test_receptionist_dashboard_is_accessible()
    {
        $receptionist = User::factory()->create();
        $receptionist->assignRole('Resepsionis');
        $this->actingAs($receptionist);

        $response = $this->get(route('dashboard.receptionist'));

        $response->assertStatus(200);
    }

    public function test_receptionist_can_update_pantry_order_status()
    {
        $receptionist = User::factory()->create();
        $receptionist->assignRole('Resepsionis');
        $this->actingAs($receptionist);

        $room = Room::factory()->create();
        $meeting = Meeting::factory()->create(['room_id' => $room->id]);
        $pantryItem = PantryItem::factory()->create();
        $pantryOrder = PantryOrder::factory()->create([
            'meeting_id' => $meeting->id,
            'pantry_item_id' => $pantryItem->id,
            'status' => 'pending',
        ]);

        $response = $this->put(route('dashboard.receptionist.pantry-orders.update', $pantryOrder->id), [
            'status' => 'preparing',
        ]);

        $response->assertRedirect(route('dashboard.receptionist'));
        $this->assertDatabaseHas('pantry_orders', [
            'id' => $pantryOrder->id,
            'status' => 'preparing',
        ]);
    }

    public function test_receptionist_can_update_pantry_order_status_to_delivered()
    {
        $receptionist = User::factory()->create();
        $receptionist->assignRole('Resepsionis');
        $this->actingAs($receptionist);

        $room = Room::factory()->create();
        $meeting = Meeting::factory()->create(['room_id' => $room->id]);
        $pantryItem = PantryItem::factory()->create();
        $pantryOrder = PantryOrder::factory()->create([
            'meeting_id' => $meeting->id,
            'pantry_item_id' => $pantryItem->id,
            'status' => 'preparing',
        ]);

        $response = $this->put(route('dashboard.receptionist.pantry-orders.update', $pantryOrder->id), [
            'status' => 'delivered',
        ]);

        $response->assertRedirect(route('dashboard.receptionist'));
        $this->assertDatabaseHas('pantry_orders', [
            'id' => $pantryOrder->id,
            'status' => 'delivered',
        ]);
    }

}
