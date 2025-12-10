<?php

namespace App\Livewire\Meeting;

use Livewire\Component;
use App\Models\PantryItem;

class SelectPantryItems extends Component
{
    public $allItems;
    public $orders = []; // New structure: [['pantry_item_id' => ..., 'quantity' => ..., 'custom_items' => '']]
    public $itemToAdd = null;
    public $customItemName = 'Lainnya'; // This item must exist in the pantry_items table

    public function mount($initialPantryItems = [])
    {
        $this->allItems = PantryItem::all();
        // Adapt the initial data to the new structure
        if (!empty($initialPantryItems)) {
            foreach ($initialPantryItems as $item) {
                 $this->orders[] = [
                    'pantry_item_id' => $item['pantry_item_id'],
                    'quantity' => $item['quantity'],
                    'custom_items' => $item['custom_items'] ?? '',
                 ];
            }
        }
    }

    public function addPantryItem()
    {
        if (!$this->itemToAdd) {
            return;
        }

        // Prevent adding duplicates
        foreach ($this->orders as $order) {
            if ($order['pantry_item_id'] == $this->itemToAdd) {
                $this->itemToAdd = null; // Reset dropdown
                return;
            }
        }

        $this->orders[] = [
            'pantry_item_id' => $this->itemToAdd,
            'quantity' => 1,
            'custom_items' => '',
        ];
        $this->itemToAdd = null; // Reset dropdown
        $this->dispatchOrders();
    }

    public function removePantryItem($index)
    {
        unset($this->orders[$index]);
        $this->orders = array_values($this->orders); // Re-index array
        $this->dispatchOrders();
    }

    // Livewire's magic 'updated' hook will catch any changes to the $orders array.
    public function updatedOrders()
    {
        // This is a good place to add validation or cleanup logic if needed.
        // For example, filter out items with 0 quantity.
        $this->orders = array_values(array_filter($this->orders, function ($order) {
            return $order['quantity'] > 0;
        }));

        $this->dispatchOrders();
    }
    
    private function dispatchOrders()
    {
        $this->dispatch('pantryOrdersUpdated', $this->orders);
    }

    public function render()
    {
        // We need to pass the item details to the view for display
        $selectedIds = array_column($this->orders, 'pantry_item_id');
        $selectedItemsData = PantryItem::whereIn('id', $selectedIds)->get()->keyBy('id');

        return view('livewire.meeting.select-pantry-items', [
            'selectedItemsData' => $selectedItemsData,
        ]);
    }
}
