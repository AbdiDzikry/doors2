<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'facilities', 'capacity', 'status', 'image_path', 'floor'];

    /**
     * Get the meetings for the room.
     */
    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }
}
