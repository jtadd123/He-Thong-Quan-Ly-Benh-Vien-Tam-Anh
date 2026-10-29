<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicalDecisionRule extends Model
{
    protected $table = 'clinical_decision_rules';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'rule_type',
        'input_parameters',
        'calculated_score',
        'score_interpretation',
        'recommendation',
        'next_action',
        'is_emergency',
        'calculated_at',
    ];

    protected $casts = [
        'input_parameters' => 'array',
        'calculated_score' => 'decimal:2',
        'is_emergency' => 'boolean',
        'calculated_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public static function calculateWellsPe(array $params): array
    {
        $score = 0;
        if (!empty($params['clinical_signs_dvt'])) $score += 3;
        if (!empty($params['pe_most_likely'])) $score += 3;
        if (!empty($params['heart_rate_gt100'])) $score += 1.5;
        if (!empty($params['immobilization'])) $score += 1.5;
        if (!empty($params['previous_dvt_pe'])) $score += 1.5;
        if (!empty($params['hemoptysis'])) $score += 1;
        if (!empty($params['malignancy'])) $score += 1;

        $risk = $score <= 1 ? 'low' : ($score <= 6 ? 'moderate' : 'high');
        $recommendation = match($risk) {
            'low' => 'D-dimer xét nghiệm, nếu âm tính không cần CT',
            'moderate' => 'D-dimer và CT Pulmonary Angiography',
            'high' => 'CT Pulmonary Angiography ngay, bắt đầu điều trị chống đông',
            default => 'Đánh giá thêm'
        };

        return ['score' => $score, 'risk' => $risk, 'recommendation' => $recommendation];
    }

    public static function calculateHeartScore(array $params): array
    {
        $score = 0;
        $score += min(2, (int)($params['history_suspicion'] ?? 0));
        $score += min(2, (int)($params['ecg_finding'] ?? 0));
        $score += min(2, (int)($params['age_score'] ?? 0));
        $score += min(2, (int)($params['risk_factors'] ?? 0));
        $score += min(2, (int)($params['troponin_score'] ?? 0));

        $risk = $score <= 3 ? 'low' : ($score <= 6 ? 'moderate' : 'high');
        $recommendation = match($risk) {
            'low' => 'Xuất viện an toàn, tái khám sau 2 tuần',
            'moderate' => 'Theo dõi quan sát, làm troponin nối tiếp',
            'high' => 'Nhập viện cấp cứu can thiệp sớm',
            default => 'Đánh giá thêm'
        };

        return ['score' => $score, 'risk' => $risk, 'recommendation' => $recommendation];
    }

    public static function interpretScore(string $ruleType, float $score): string
    {
        return match($ruleType) {
            'wells_pe' => $score <= 1 ? 'Nguy cơ thấp' : ($score <= 6 ? 'Nguy cơ trung bình' : 'Nguy cơ cao'),
            'wells_dvt' => $score < 2 ? 'Nguy cơ thấp' : ($score <= 6 ? 'Nguy cơ trung bình' : 'Nguy cơ cao'),
            'heart_score' => $score <= 3 ? 'Nguy cơ thấp - MACE 0.9-1.7%' : ($score <= 6 ? 'Nguy cơ trung bình - MACE 12-16.6%' : 'Nguy cơ cao - MACE 50-65%'),
            'chads2_vasc' => $score == 0 ? 'Không cần chống đông' : ($score == 1 ? 'Xem xét chống đông' : 'Nên dùng chống đông OAC'),
            'curb65' => $score <= 1 ? 'Nhẹ - Điều trị ngoại trú' : ($score <= 2 ? 'Trung bình - Nhập viện' : 'Nặng - ICU'),
            default => "Điểm: $score"
        };
    }
}
