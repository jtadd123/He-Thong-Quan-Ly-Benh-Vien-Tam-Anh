<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OccupationalExposure extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_code',
        'employee_name',
        'employee_role',
        'department',
        'exposure_type',
        'source_patient_id',
        'source_status',
        'pep_started',
        'pep_start_hours_elapsed',
        'followup_timeline',
        'status',
        'incident_description',
    ];

    protected $casts = [
        'pep_started' => 'boolean',
        'followup_timeline' => 'array',
    ];

    public function sourcePatient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'source_patient_id');
    }

    public static function generateCode(): string
    {
        return 'PEP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
    }
}
