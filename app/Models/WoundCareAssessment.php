<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WoundCareAssessment extends Model
{
    protected $table = 'wound_care_assessments';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'wound_type',
        'wound_location',
        'pressure_injury_stage',
        'wound_length_cm',
        'wound_width_cm',
        'wound_depth_cm',
        'wound_area_cm2',
        'push_score',
        'braden_score',
        'wound_bed',
        'exudate_amount',
        'exudate_type',
        'wound_infection',
        'dressing_type',
        'dressing_frequency_days',
        'topical_treatment',
        'photos',
        'assessment_notes',
        'next_assessment_date',
        'healing_trend',
    ];

    protected $casts = [
        'wound_infection' => 'boolean',
        'photos' => 'array',
        'next_assessment_date' => 'date',
        'wound_length_cm' => 'decimal:1',
        'wound_width_cm' => 'decimal:1',
        'wound_depth_cm' => 'decimal:1',
        'wound_area_cm2' => 'decimal:2',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public static function calculateWoundArea(float $lengthCm, float $widthCm): float
    {
        return round($lengthCm * $widthCm, 2);
    }

    public static function classifyBradenRisk(int $bradenScore): string
    {
        if ($bradenScore <= 9) return 'very_high';
        if ($bradenScore <= 12) return 'high';
        if ($bradenScore <= 14) return 'moderate';
        if ($bradenScore <= 18) return 'mild';
        return 'no_risk';
    }

    public static function classifyPressureInjury(int $stage): string
    {
        return match($stage) {
            1 => 'Giai đoạn 1: Da nguyên vẹn, ban đỏ không mất màu',
            2 => 'Giai đoạn 2: Mất lớp da một phần, trầy xước/phỏng',
            3 => 'Giai đoạn 3: Mất da toàn lớp, mô dưới da lộ',
            4 => 'Giai đoạn 4: Mất mô toàn lớp, lộ xương/gân/cơ',
            default => 'Không phân loại được'
        };
    }

    public static function recommendDressing(string $woundBed, string $exudateAmount): string
    {
        if ($woundBed === 'black') return 'Debridement enzyme + Alginate';
        if ($woundBed === 'yellow') return 'Hydrogel hoặc Hydrofiber để làm sạch slough';
        if ($woundBed === 'red' && in_array($exudateAmount, ['large', 'moderate'])) return 'Foam dressing hút dịch';
        if ($woundBed === 'red') return 'Hydrocolloid để bảo vệ tổ chức hạt';
        return 'Đánh giá thêm';
    }
}
