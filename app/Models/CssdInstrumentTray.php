<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CssdInstrumentTray extends Model
{
    use HasFactory;

    protected $fillable = [
        'tray_code',
        'tray_name',
        'department',
        'total_instruments_count',
        'instruments_manifest',
        'status',
        'last_cycle_id',
        'expiry_date',
    ];

    protected $casts = [
        'instruments_manifest' => 'array',
        'expiry_date' => 'date',
    ];

    public function lastCycle(): BelongsTo
    {
        return $this->belongsTo(SterilizationCycle::class, 'last_cycle_id');
    }
}
