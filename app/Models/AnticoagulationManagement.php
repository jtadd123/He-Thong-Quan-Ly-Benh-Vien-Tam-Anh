<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnticoagulationManagement extends Model
{
    protected $table = 'anticoagulation_managements';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'indication',
        'anticoagulant',
        'target_inr_min',
        'target_inr_max',
        'current_inr',
        'current_aptt',
        'target_aptt_min',
        'target_aptt_max',
        'current_dose',
        'dose_unit',
        'dose_adjustment',
        'has_bled_score',
        'bleeding_risk',
        'contraindications',
        'next_monitoring_date',
        'status',
        'clinical_notes',
    ];

    protected $casts = [
        'target_inr_min' => 'decimal:1',
        'target_inr_max' => 'decimal:1',
        'current_inr' => 'decimal:2',
        'current_aptt' => 'decimal:1',
        'current_dose' => 'decimal:2',
        'has_bled_score' => 'decimal:1',
        'next_monitoring_date' => 'date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public static function assessInrStatus(float $inr, float $targetMin, float $targetMax): string
    {
        if ($inr < $targetMin) return 'sub_therapeutic';
        if ($inr > $targetMax) return 'supra_therapeutic';
        return 'therapeutic';
    }

    public static function calculateHasBled(array $params): float
    {
        $score = 0;
        if (!empty($params['hypertension'])) $score += 1;
        if (!empty($params['renal_dysfunction'])) $score += 1;
        if (!empty($params['liver_dysfunction'])) $score += 1;
        if (!empty($params['stroke_history'])) $score += 1;
        if (!empty($params['bleeding_history'])) $score += 1;
        if (!empty($params['labile_inr'])) $score += 1;
        if (!empty($params['elderly_gt65'])) $score += 1;
        if (!empty($params['drugs_alcohol'])) $score += 1;
        return (float)$score;
    }

    public static function assessBleedingRisk(float $hasBledScore): string
    {
        if ($hasBledScore >= 3) return 'high';
        if ($hasBledScore >= 2) return 'moderate';
        return 'low';
    }

    public static function suggestWarfarinAdjustment(float $currentInr, float $targetMin, float $targetMax, float $currentDoseMg): string
    {
        $status = self::assessInrStatus($currentInr, $targetMin, $targetMax);
        if ($status === 'therapeutic') {
            return "Giữ nguyên liều {$currentDoseMg} mg/ngày";
        }
        if ($status === 'sub_therapeutic') {
            $newDose = round($currentDoseMg * 1.1, 2);
            return "Tăng liều 10%: {$newDose} mg/ngày. Kiểm tra lại INR sau 7 ngày";
        }
        if ($currentInr > 5.0) {
            return "Tạm ngưng Warfarin, theo dõi xuất huyết sát. INR > 5.0";
        }
        $newDose = round($currentDoseMg * 0.9, 2);
        return "Giảm liều 10%: {$newDose} mg/ngày. Kiểm tra lại INR sau 7 ngày";
    }
}
