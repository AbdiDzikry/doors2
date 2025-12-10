<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalParticipant extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'email', 'phone', 'company', 'department', 'address', 'type'];

    public function meetingParticipants()
    {
        return $this->morphMany(MeetingParticipant::class, 'participant');
    }
}
