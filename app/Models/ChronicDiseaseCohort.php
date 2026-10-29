<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChronicDiseaseCohort extends Model
{
    protected $table = 'chronic_disease_cohorts';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'disease_type',
        'icd10_code',
        'diagnosis_date',
        'disease_stage',
        'comorbidities',
        'target_metrics',
        'current_metrics',
        'metrics_on_target',
        'care_manager',
        'last_review_date',
        'next_review_date',
        'annual_visit_count',
        'er_visits_12mo',
        'hospitalizations_12mo',
        'enrolled_in_program',
        'engagement_level',
        'care_plan_notes',
    ];

    protected $casts = [
        'diagnosis_date' => 'date',
        'last_review_date' => 'date',
        'next_review_date' => 'date',
        'comorbidities' => 'array',
        'target_metrics' => 'array',
        'current_metrics' => 'array',
        'metrics_on_target' => 'boolean',
        'enrolled_in_program' => 'boolean',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public static function getTargetMetrics(string $diseaseType): array
    {
        return match($diseaseType) {
            'diabetes_t2' => ['hba1c' => '<7%', 'sbp' => '<130', 'ldl' => '<2.6 mmol/L', 'bmi' => '18.5-25'],
            'hypertension' => ['sbp' => '<130', 'dbp' => '<80', 'pulse' => '<80 bpm'],
            'chf' => ['ef_pct' => '>50%', 'bnp' => '<100 pg/mL', 'weight_gain_kg' => '<2kg/3ngày'],
            'ckd' => ['gfr' => '>60 mL/min/1.73m²', 'acr' => '<30 mg/g', 'bp' => '<130/80'],
            'copd' => ['fev1_pct' => '>80%', 'exacerbations_per_year' => '<2', 'mrc_dyspnea' => '<3'],
            default => ['follow_guidelines' => true]
        };
    }

    public static function assessMetricsOnTarget(array $targetMetrics, array $currentMetrics): bool
    {
        if (empty($targetMetrics) || empty($currentMetrics)) return false;
        return count(array_intersect_key($targetMetrics, $currentMetrics)) >= ceil(count($targetMetrics) * 0.7);
    }

    public static function calculatePopulationRisk(array $cohortData): array
    {
        $total = count($cohortData);
        if ($total === 0) return ['high_risk_pct' => 0.0, 'on_target_pct' => 0.0];
        $highRisk = count(array_filter($cohortData, fn($p) => ($p['er_visits_12mo'] ?? 0) >= 2 || ($p['hospitalizations_12mo'] ?? 0) >= 1));
        $onTarget = count(array_filter($cohortData, fn($p) => !empty($p['metrics_on_target'])));
        return [
            'high_risk_pct' => round(($highRisk / $total) * 100, 1),
            'on_target_pct' => round(($onTarget / $total) * 100, 1),
        ];
    }
}
