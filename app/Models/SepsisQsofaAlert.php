<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SepsisQsofaAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'respiratory_rate',
        'glasgow_coma_scale',
        'systolic_bp',
        'qsofa_score',
        'risk_level',
        'icu_notified',
        'assessed_by',
    ];

    protected $casts = [
        'icu_notified' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    /**
     * Tính toán điểm qSOFA theo tiêu chuẩn Quốc tế:
     * 1. Nhịp thở >= 22 bpm -> 1 điểm
     * 2. Thang điểm Glasgow < 15 -> 1 điểm
     * 3. Huyết áp tâm thu <= 100 mmHg -> 1 điểm
     */
    public static function calculateScore(int $rr, int $gcs, int $sbp): int
    {
        $score = 0;
        if ($rr >= 22) $score++;
        if ($gcs < 15) $score++;
        if ($sbp <= 100) $score++;
        return $score;
    }
}
