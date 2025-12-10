<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Room;
use App\Models\User;
use App\Models\ExternalParticipant;
use App\Models\PantryItem;
use Carbon\Carbon;

class Meeting extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'room_id',
        'topic',
        'start_time',
        'end_time',
        'meeting_type',
        'priority_guest_id',
        'status',
        'recurring_meeting_id', // This is the actual column used
        'confirmation_status',
    ];

    protected $appends = ['calculated_status'];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        // 'recurring_end_date' => 'datetime', // This column no longer exists on Meeting
    ];

    public function getCalculatedStatusAttribute()
    {
        if ($this->status === 'cancelled') {
            return 'cancelled';
        }

        $now = now(); // Use Laravel's helper for the current time

        if ($this->start_time && $this->end_time) {
            // The 'start_time' and 'end_time' attributes are already Carbon objects due to the $casts property.
            // No need to parse them again.
            if ($now->between($this->start_time, $this->end_time)) {
                return 'ongoing';
            }

            if ($now->isAfter($this->end_time)) {
                return 'completed';
            }
        }

        // If the start time is in the future, it's considered scheduled.
        return 'scheduled';
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function meetingParticipants()
    {
        return $this->hasMany(MeetingParticipant::class);
    }

    public function externalParticipants()
    {
        return $this->morphToMany(ExternalParticipant::class, 'participant', 'meeting_participants');
    }

    public function pantryOrders()
    {
        return $this->hasMany(PantryOrder::class);
    }

    public function organizer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Add recurringMeeting relationship
    public function recurringMeeting()
    {
        return $this->belongsTo(RecurringMeeting::class);
    }

    public function scopeFilterByDate($query, $filter, $startDateInput = null, $endDateInput = null)
    {
        $carbonStartDate = $startDateInput ? \Carbon\Carbon::parse($startDateInput) : today();
        $carbonEndDate = $endDateInput ? \Carbon\Carbon::parse($endDateInput) : today();

        if ($filter === 'day') {
            $effectiveStartDate = $carbonStartDate->startOfDay();
            $effectiveEndDate = $carbonEndDate->endOfDay();
        } elseif ($filter === 'week') {
            $effectiveStartDate = $carbonStartDate->copy()->startOfWeek();
            $effectiveEndDate = $carbonEndDate->copy()->endOfWeek();
        } elseif ($filter === 'month') {
            $effectiveStartDate = $carbonStartDate->copy()->startOfMonth();
            $effectiveEndDate = $carbonEndDate->copy()->endOfMonth();
        } elseif ($filter === 'year') {
            $effectiveStartDate = $carbonStartDate->copy()->startOfYear();
            $effectiveEndDate = $carbonEndDate->copy()->endOfYear();
        } else {
            // Default or 'custom'
            $effectiveStartDate = $carbonStartDate->startOfDay();
            $effectiveEndDate = $carbonEndDate->endOfDay();
        }

        return $query->whereBetween('start_time', [$effectiveStartDate, $effectiveEndDate]);
    }

    // Remove children/parent relationships as they are not used for the current recurring meeting structure
    // public function children()
    // {
    //     return $this->hasMany(Meeting::class, 'parent_meeting_id');
    // }

    // public function parent()
    // {
    //     return $this->belongsTo(Meeting::class, 'parent_meeting_id');
    // }
}
