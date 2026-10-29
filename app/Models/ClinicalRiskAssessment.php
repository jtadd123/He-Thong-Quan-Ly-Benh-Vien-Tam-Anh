<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicalRiskAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_profile_id',
        'assessment_type',
        'score',
        'risk_category',
        'details',
        'preventive_actions',
        'assessed_by',
        'assessed_at',
    ];

    protected $casts = [
        'details' => 'array',
        'preventive_actions' => 'array',
        'assessed_at' => 'datetime',
    ];

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }
}
