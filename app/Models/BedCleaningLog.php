<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BedCleaningLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'bed_id',
        'cleaner_name',
        'disinfectant_chemical',
        'started_at',
        'completed_at',
        'verified_by',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function bed(): BelongsTo
    {
        return $this->belongsTo(HospitalBed::class, 'bed_id');
    }
}
