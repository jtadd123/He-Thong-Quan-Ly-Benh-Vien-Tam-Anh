<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HospitalBed extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'bed_code',
        'status',
        'current_patient_id',
        'last_disinfected_at',
    ];

    protected $casts = [
        'last_disinfected_at' => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(HospitalRoom::class, 'room_id');
    }

    public function currentPatient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'current_patient_id');
    }

    public function cleaningLogs(): HasMany
    {
        return $this->hasMany(BedCleaningLog::class, 'bed_id');
    }
}
