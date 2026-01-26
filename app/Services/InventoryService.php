<?php

namespace App\Services;

use App\Models\Meeting;
use App\Models\PantryItem;
use App\Models\PantryOrder;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Deduct stock for a list of items.
     * 
     * @param array $items Array of ['pantry_item_id' => id, 'quantity' => qty]
     * @return void
     * @throws \Exception
     */
    public function deductStock(array $items)
    {
        foreach ($items as $item) {
            if (!empty($item['pantry_item_id']) && !empty($item['quantity'])) {
                $pantryItem = PantryItem::find($item['pantry_item_id']);
                if ($pantryItem) {
                    if ($pantryItem->stock < $item['quantity']) {
                         throw new \Exception("Insufficient stock for {$pantryItem->name}.");
                    }
                    $pantryItem->decrement('stock', $item['quantity']);
                }
            }
        }
    }

    /**
     * Refund stock for a specific meeting's orders.
     * 
     * @param Meeting $meeting
     * @return void
     */
    public function refundStockForMeeting(Meeting $meeting)
    {
        foreach ($meeting->pantryOrders as $order) {
            $this->restoreStock($order->pantry_item_id, $order->quantity);
        }
    }

    /**
     * Restore stock for a specific item.
     * 
     * @param int $pantryItemId
     * @param int $quantity
     * @return void
     */
    public function restoreStock($pantryItemId, $quantity)
    {
        if ($pantryItemId && $quantity > 0) {
            PantryItem::where('id', $pantryItemId)->increment('stock', $quantity);
        }
    }
}
