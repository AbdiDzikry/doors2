<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'participant_id',
        'participant_type',
        'name',
        'email',
        'type',
        'participant_code',
        'attend_status',
        'check_in_time',
        'check_out_time',
        'attended_at',
        'is_pic',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function participant()
    {
        return $this->morphTo();
    }
}
