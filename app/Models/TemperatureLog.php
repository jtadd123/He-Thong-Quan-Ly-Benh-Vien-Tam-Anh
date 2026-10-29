<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemperatureLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'storage_id',
        'temperature',
        'humidity',
        'alert_dispatched',
        'status_tag',
        'recorded_at',
    ];

    protected $casts = [
        'temperature' => 'decimal:1',
        'humidity' => 'decimal:1',
        'alert_dispatched' => 'boolean',
        'recorded_at' => 'datetime',
    ];

    public function storage(): BelongsTo
    {
        return $this->belongsTo(ColdChainStorage::class, 'storage_id');
    }
}
