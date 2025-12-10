<?php

namespace Tests\Feature\Master;

use App\Models\PantryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PantryItemTest extends TestCase
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
    public function super_admin_can_access_pantry_items_index(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('master.pantry-items.index'))
            ->assertOk();
    }

    #[test]
    public function admin_can_access_pantry_items_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('master.pantry-items.index'))
            ->assertOk();
    }

    #[test]
    public function non_admin_cannot_access_pantry_items_index(): void
    {
        $this->actingAs($this->karyawan)
            ->get(route('master.pantry-items.index'))
            ->assertForbidden();
    }

    #[test]
    public function guest_cannot_access_pantry_items_index(): void
    {
        $this->get(route('master.pantry-items.index'))
            ->assertRedirect(route('login'));
    }

    #[test]
    public function super_admin_can_create_pantry_item(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('master.pantry-items.store'), [
                'name' => 'Coffee',
                'stock' => 100,
                'type' => 'minuman',
            ])
            ->assertRedirect(route('master.pantry-items.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('pantry_items', [
            'name' => 'Coffee',
            'stock' => 100,
            'type' => 'minuman',
        ]);
    }

    #[test]
    public function admin_can_create_pantry_item(): void
    {
        $this->actingAs($this->admin)
            ->post(route('master.pantry-items.store'), [
                'name' => 'Tea',
                'stock' => 50,
                'type' => 'minuman',
            ])
            ->assertRedirect(route('master.pantry-items.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('pantry_items', [
            'name' => 'Tea',
            'stock' => 50,
            'type' => 'minuman',
        ]);
    }

    #[test]
    public function non_admin_cannot_create_pantry_item(): void
    {
        $this->actingAs($this->karyawan)
            ->post(route('master.pantry-items.store'), [
                'name' => 'Milk',
                'stock' => 10,
                'unit' => 'liters',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('pantry_items', [
            'name' => 'Milk',
        ]);
    }

    #[test]
    public function super_admin_can_update_pantry_item(): void
    {
        $pantryItem = PantryItem::factory()->create();

        $this->actingAs($this->superAdmin)
            ->put(route('master.pantry-items.update', $pantryItem), [
                'name' => 'Updated Coffee',
                'stock' => 120,
                'type' => 'minuman',
            ])
            ->assertRedirect(route('master.pantry-items.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('pantry_items', [
            'id' => $pantryItem->id,
            'name' => 'Updated Coffee',
            'stock' => 120,
            'type' => 'minuman',
        ]);
    }

    #[test]
    public function admin_can_update_pantry_item(): void
    {
        $pantryItem = PantryItem::factory()->create();

        $this->actingAs($this->admin)
            ->put(route('master.pantry-items.update', $pantryItem), [
                'name' => 'Updated Tea',
                'stock' => 60,
                'type' => 'minuman',
            ])
            ->assertRedirect(route('master.pantry-items.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('pantry_items', [
            'id' => $pantryItem->id,
            'name' => 'Updated Tea',
            'stock' => 60,
            'type' => 'minuman',
        ]);
    }

    #[test]
    public function non_admin_cannot_update_pantry_item(): void
    {
        $pantryItem = PantryItem::factory()->create();

        $this->actingAs($this->karyawan)
            ->put(route('master.pantry-items.update', $pantryItem), [
                'name' => 'Forbidden Milk',
                'stock' => 15,
                'type' => 'makanan',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('pantry_items', [
            'name' => 'Forbidden Milk',
        ]);
    }

    #[test]
    public function super_admin_can_delete_pantry_item(): void
    {
        $pantryItem = PantryItem::factory()->create();

        $this->actingAs($this->superAdmin)
            ->delete(route('master.pantry-items.destroy', $pantryItem))
            ->assertRedirect(route('master.pantry-items.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('pantry_items', [
            'id' => $pantryItem->id,
        ]);
    }

    #[test]
    public function admin_can_delete_pantry_item(): void
    {
        $pantryItem = PantryItem::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('master.pantry-items.destroy', $pantryItem))
            ->assertRedirect(route('master.pantry-items.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('pantry_items', [
            'id' => $pantryItem->id,
        ]);
    }

    #[test]
    public function non_admin_cannot_delete_pantry_item(): void
    {
        $pantryItem = PantryItem::factory()->create();

        $this->actingAs($this->karyawan)
            ->delete(route('master.pantry-items.destroy', $pantryItem))
            ->assertForbidden();

        $this->assertDatabaseHas('pantry_items', [
            'id' => $pantryItem->id,
        ]);
    }

    #[test]
    public function create_pantry_item_requires_name_stock_unit(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('master.pantry-items.store'), [])
            ->assertSessionHasErrors(['name', 'type', 'stock']);
    }

    #[test]
    public function update_pantry_item_requires_name_stock_unit(): void
    {
        $pantryItem = PantryItem::factory()->create();

        $this->actingAs($this->superAdmin)
            ->put(route('master.pantry-items.update', $pantryItem), [
                'name' => '',
                'stock' => '',
                'unit' => '',
            ])
            ->assertSessionHasErrors(['name', 'type', 'stock']);
    }
}