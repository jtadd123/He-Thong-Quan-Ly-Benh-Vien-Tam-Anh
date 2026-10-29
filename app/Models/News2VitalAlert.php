<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class News2VitalAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_profile_id',
        'respiratory_rate',
        'oxygen_saturation',
        'supplemental_oxygen',
        'systolic_bp',
        'pulse_rate',
        'consciousness_level',
        'temperature',
        'news2_score',
        'risk_level',
        'rrt_triggered',
        'clinician_notes',
        'recorded_by',
    ];

    protected $casts = [
        'supplemental_oxygen' => 'boolean',
        'rrt_triggered' => 'boolean',
        'temperature' => 'decimal:1',
    ];

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public function rrtActivation(): HasOne
    {
        return $this->hasOne(RrtActivation::class, 'news2_alert_id');
    }

    /**
     * Thuật toán tính điểm NEWS2 chuẩn Royal College of Physicians (RCP)
     */
    public static function calculateScore(
        int $rr,
        int $spo2,
        bool $o2,
        int $sbp,
        int $pulse,
        string $consciousness,
        float $temp
    ): array {
        $score = 0;

        // 1. Nhịp thở (Respiratory Rate)
        if ($rr <= 8 || $rr >= 25) {
            $score += 3;
        } elseif ($rr >= 21) {
            $score += 2;
        } elseif ($rr >= 9 && $rr <= 11) {
            $score += 1;
        }

        // 2. Độ bão hòa oxy SpO2
        if ($spo2 <= 91) {
            $score += 3;
        } elseif ($spo2 <= 93) {
            $score += 2;
        } elseif ($spo2 <= 95) {
            $score += 1;
        }

        // 3. Thở oxy hỗ trợ
        if ($o2) {
            $score += 2;
        }

        // 4. Huyết áp tâm thu
        if ($sbp <= 90 || $sbp >= 220) {
            $score += 3;
        } elseif ($sbp <= 100) {
            $score += 2;
        } elseif ($sbp <= 110) {
            $score += 1;
        }

        // 5. Nhịp tim
        if ($pulse <= 40 || $pulse >= 131) {
            $score += 3;
        } elseif ($pulse >= 111) {
            $score += 2;
        } elseif ($pulse <= 50 || $pulse >= 91) {
            $score += 1;
        }

        // 6. Tri giác ACVPU
        if (strtolower($consciousness) !== 'alert') {
            $score += 3;
        }

        // 7. Thân nhiệt
        if ($temp <= 35.0) {
            $score += 3;
        } elseif ($temp >= 39.1) {
            $score += 2;
        } elseif ($temp <= 36.0 || $temp >= 38.1) {
            $score += 1;
        }

        // Xác định cấp độ nguy cơ
        if ($score >= 7) {
            $risk = 'high_critical';
        } elseif ($score >= 5) {
            $risk = 'medium';
        } else {
            $risk = 'low';
        }

        return [
            'score' => $score,
            'risk_level' => $risk,
            'trigger_rrt' => ($risk === 'high_critical'),
        ];
    }
}
