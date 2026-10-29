<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NicuMonitoringRecord extends Model
{
    protected $table = 'nicu_monitoring_records';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'gestational_age_weeks',
        'birth_weight_grams',
        'apgar_1min',
        'apgar_5min',
        'current_weight_grams',
        'temperature',
        'spo2_percent',
        'heart_rate_bpm',
        'respiratory_rate',
        'blood_glucose',
        'bilirubin_total',
        'bilirubin_direct',
        'phototherapy_active',
        'oxygen_support',
        'respiratory_support',
        'iv_access',
        'umbilical_line',
        'total_fluid_ml_per_kg',
        'caloric_intake_kcal_per_kg',
        'breast_feeding',
        'feeding_type',
        'ballard_score',
        'discharge_readiness',
        'nursing_notes',
    ];

    protected $casts = [
        'phototherapy_active' => 'boolean',
        'oxygen_support' => 'boolean',
        'iv_access' => 'boolean',
        'umbilical_line' => 'boolean',
        'breast_feeding' => 'boolean',
        'birth_weight_grams' => 'decimal:1',
        'current_weight_grams' => 'decimal:1',
        'temperature' => 'decimal:1',
        'spo2_percent' => 'decimal:1',
        'bilirubin_total' => 'decimal:2',
        'bilirubin_direct' => 'decimal:2',
        'blood_glucose' => 'decimal:1',
        'total_fluid_ml_per_kg' => 'decimal:1',
        'caloric_intake_kcal_per_kg' => 'decimal:1',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public static function classifyBirthWeight(float $weightGrams): string
    {
        if ($weightGrams < 1000) return 'extremely_low_birth_weight';
        if ($weightGrams < 1500) return 'very_low_birth_weight';
        if ($weightGrams < 2500) return 'low_birth_weight';
        if ($weightGrams < 4000) return 'normal';
        return 'macrosomia';
    }

    public static function classifyGestationalAge(int $weeks): string
    {
        if ($weeks < 28) return 'extremely_preterm';
        if ($weeks < 32) return 'very_preterm';
        if ($weeks < 37) return 'preterm';
        if ($weeks < 42) return 'term';
        return 'post_term';
    }

    public static function needsPhototherapy(float $bilirubinMgdl, int $gestationalAgeWeeks, int $ageHours): bool
    {
        $threshold = match(true) {
            $gestationalAgeWeeks >= 38 && $ageHours >= 72 => 15.0,
            $gestationalAgeWeeks >= 35 && $ageHours >= 48 => 13.0,
            $gestationalAgeWeeks >= 35 => 12.0,
            default => 10.0
        };
        return $bilirubinMgdl >= $threshold;
    }

    public static function calculateFluidNeeds(float $weightKg, int $agedays): float
    {
        $base = $agedays <= 1 ? 60 : ($agedays <= 2 ? 80 : ($agedays <= 3 ? 100 : ($agedays <= 7 ? 120 : 150)));
        return round($base * $weightKg, 1);
    }

    public static function classifyApgar(int $score): string
    {
        if ($score >= 7) return 'Bình thường';
        if ($score >= 4) return 'Nhẹ-trung bình: Cần hỗ trợ hô hấp';
        return 'Nặng: Hồi sức cấp cứu ngay';
    }
}
