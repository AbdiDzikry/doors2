<?php

namespace App\Models\GeneralAffair;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class GaAcAsset extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ga_ac_assets';

    protected $fillable = [
        'uuid',
        'sku',
        'name',
        'brand',
        'model',
        'pk',
        'location',
        'purchase_date',
        'warranty_expiry_date',
        'status',
        'notes',
        'qr_path',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry_date' => 'date',
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
}
