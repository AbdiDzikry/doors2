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
            if ($order->pantryItem) {
                // Determine if we should refund? 
                // Generally if status is pending, prepared, delivered... 
                // If it's already consumed/done, maybe not? 
                // For now, let's assume if the meeting is cancelled, we refund everything 
                // UNLESS the order status implies it's too late? 
                // Usually "cancelled meeting" means items are returned.
                
                // Only increment if we have the item record
                PantryItem::where('id', $order->pantry_item_id)->increment('stock', $order->quantity);
            }
        }
    }
}
