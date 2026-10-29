<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PainAssessment extends Model
{
    protected $table = 'pain_assessments';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'scale_type',
        'pain_score',
        'pain_location',
        'pain_quality',
        'pain_at_rest',
        'pain_on_movement',
        'aggravating_factors',
        'relieving_factors',
        'analgesic_ladder',
        'analgesics_given',
        'pain_score_after',
        'reassessment_minutes',
        'pain_controlled',
        'pain_severity',
        'nursing_notes',
    ];

    protected $casts = [
        'pain_score' => 'decimal:1',
        'pain_score_after' => 'decimal:1',
        'pain_at_rest' => 'boolean',
        'pain_on_movement' => 'boolean',
        'pain_controlled' => 'boolean',
        'analgesics_given' => 'array',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public static function classifyPainSeverity(float $score): string
    {
        if ($score == 0) return 'none';
        if ($score <= 3) return 'mild';
        if ($score <= 6) return 'moderate';
        if ($score <= 9) return 'severe';
        return 'very_severe';
    }

    public static function recommendAnalgesicLadder(float $painScore): string
    {
        if ($painScore <= 3) return 'step1_nonopioid';
        if ($painScore <= 6) return 'step2_weak_opioid';
        return 'step3_strong_opioid';
    }

    public static function isPainControlled(float $scoreBefore, float $scoreAfter): bool
    {
        return $scoreAfter <= 3 || ($scoreBefore - $scoreAfter) >= ($scoreBefore * 0.3);
    }

    public static function whoAnalgesicLadderGuide(string $ladder): array
    {
        return match($ladder) {
            'step1_nonopioid' => [
                'drugs' => ['Paracetamol 500-1000mg', 'Ibuprofen 400-600mg', 'Diclofenac 50-75mg'],
                'adjuvants' => ['Amitriptyline', 'Gabapentin']
            ],
            'step2_weak_opioid' => [
                'drugs' => ['Tramadol 50-100mg', 'Codeine 30-60mg', 'Tapentadol 50-75mg'],
                'adjuvants' => ['NSAIDs', 'Corticosteroids']
            ],
            'step3_strong_opioid' => [
                'drugs' => ['Morphine 10-30mg IR', 'Oxycodone 5-15mg', 'Fentanyl patch 25mcg/h'],
                'adjuvants' => ['Anticonvulsants', 'Antidepressants', 'Nerve blocks']
            ],
            default => ['drugs' => [], 'adjuvants' => []]
        };
    }
}
