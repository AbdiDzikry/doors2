<?php

namespace App\Models\GeneralAffair;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\User;

class GaAcTicket extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ga_ac_tickets';

    protected $fillable = [
        'uuid',
        'ga_ac_asset_id',
        'reporter_name',
        'reporter_nik',
        'issue_category',
        'description',
        'status',
        'validated_by',
        'validated_at',
        'technician_id',
        'resolved_at',
        'resolution_notes',
        'repair_cost',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
        'resolved_at' => 'datetime',
        'repair_cost' => 'decimal:2',
    ];

    /**
     * Boot function to handle auto-generation of UUIDs
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    // Relationships

    public function asset()
    {
        return $this->belongsTo(GaAcAsset::class, 'ga_ac_asset_id');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    // Scopes

    public function scopePending($query)
    {
        return $query->where('status', 'pending_validation');
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'assigned', 'in_progress']);
    }
}
