<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class SurveyResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'q1', 'q2', 'q3', 'q4', 'q5',
        'q6', 'q7', 'q8', 'q9', 'q10',
        'sus_score',
        'comments',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
