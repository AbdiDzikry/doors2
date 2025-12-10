<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PantryOrder extends Model
{
    use HasFactory;
    protected $fillable = [
        'meeting_id',
        'pantry_item_id',
        'quantity',
        'status',
        'custom_items',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function pantryItem()
    {
        return $this->belongsTo(PantryItem::class);
    }
}
