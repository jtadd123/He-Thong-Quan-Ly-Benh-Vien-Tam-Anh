<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientDigitalTwin extends Model
{
    protected $table = 'patient_digital_twins';

    protected $fillable = [
        'patient_id',
        'chronic_conditions',
        'allergy_profile',
        'medication_history',
        'vaccination_record',
        'family_history',
        'surgical_history',
        'lab_trends',
        'vital_trends',
        'wearable_data',
        'health_score',
        'risk_stratification',
        'predicted_risks',
        'care_gaps',
        'last_updated_at',
    ];

    protected $casts = [
        'chronic_conditions' => 'array',
        'allergy_profile' => 'array',
        'medication_history' => 'array',
        'vaccination_record' => 'array',
        'family_history' => 'array',
        'surgical_history' => 'array',
        'lab_trends' => 'array',
        'vital_trends' => 'array',
        'wearable_data' => 'array',
        'predicted_risks' => 'array',
        'care_gaps' => 'array',
        'last_updated_at' => 'datetime',
        'health_score' => 'decimal:1',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    public static function calculateHealthScore(array $metrics): float
    {
        $score = 100.0;
        $deductions = [
            'chronic_conditions_count' => fn($v) => min($v * 5, 30),
            'er_visits' => fn($v) => min($v * 8, 24),
            'hospitalizations' => fn($v) => min($v * 10, 30),
            'medication_adherence_pct' => fn($v) => max(0, (100 - $v) * 0.2),
        ];

        foreach ($deductions as $key => $fn) {
            if (isset($metrics[$key])) {
                $score -= $fn($metrics[$key]);
            }
        }

        return (float)max(0.0, round($score, 1));
    }

    public static function classifyRisk(float $healthScore): string
    {
        if ($healthScore >= 80) return 'low';
        if ($healthScore >= 60) return 'moderate';
        if ($healthScore >= 40) return 'high';
        return 'very_high';
    }

    public static function identifyCareGaps(array $chronicConditions, array $vaccinations, array $labs): array
    {
        $gaps = [];
        if (in_array('diabetes_t2', $chronicConditions) && !isset($labs['hba1c_last_90d'])) {
            $gaps[] = 'HbA1c chưa kiểm tra trong 90 ngày';
        }
        if (in_array('hypertension', $chronicConditions) && !isset($labs['ecg_last_year'])) {
            $gaps[] = 'ECG chưa kiểm tra trong 1 năm';
        }
        if (!in_array('covid19', $vaccinations)) {
            $gaps[] = 'Chưa tiêm vắc-xin COVID-19';
        }
        return $gaps;
    }
}
