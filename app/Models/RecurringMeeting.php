<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class RecurringMeeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'frequency',
        'ends_at',
    ];

    protected $casts = [
        'ends_at' => 'date',
    ];

    public function meetings()
    {
        return $this->hasMany(Meeting::class);
    }
}
