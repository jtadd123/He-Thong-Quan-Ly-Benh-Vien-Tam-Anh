<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PacsAiAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'analysis_code',
        'patient_id',
        'modality',
        'body_part',
        'finding_detected',
        'confidence_score',
        'triage_urgency',
        'is_prioritized',
        'ai_model_version',
    ];

    protected $casts = [
        'confidence_score' => 'decimal:2',
        'is_prioritized' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }
}
