<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentalHealthAssessment extends Model
{
    protected $table = 'mental_health_assessments';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'assessment_type',
        'responses',
        'total_score',
        'severity_level',
        'interpretation',
        'suicide_risk',
        'safety_plan_made',
        'safety_plan_details',
        'referred_to',
        'medication_started',
        'medication_name',
        'psychotherapy_recommended',
        'follow_up_date',
        'clinical_impression',
    ];

    protected $casts = [
        'responses' => 'array',
        'total_score' => 'decimal:1',
        'safety_plan_made' => 'boolean',
        'medication_started' => 'boolean',
        'psychotherapy_recommended' => 'boolean',
        'follow_up_date' => 'date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public static function scorePHQ9(array $responses): array
    {
        $score = array_sum($responses);
        $severity = match(true) {
            $score <= 4 => 'minimal',
            $score <= 9 => 'mild',
            $score <= 14 => 'moderate',
            $score <= 19 => 'moderately_severe',
            default => 'severe'
        };

        $recommendation = match(true) {
            $score <= 4 => 'Theo dõi, tái đánh giá sau 6 tháng',
            $score <= 9 => 'Tư vấn, can thiệp tâm lý ngắn hạn',
            $score <= 14 => 'Kết hợp tâm lý trị liệu và xem xét dùng thuốc',
            default => 'Cần điều trị chuyên sâu, hội chẩn chuyên khoa tâm thần'
        };

        $suicideRisk = ($responses[8] ?? 0) >= 1 ? 'needs_assessment' : 'none';
        return [
            'score' => $score,
            'severity' => $severity,
            'recommendation' => $recommendation,
            'suicide_item' => $suicideRisk
        ];
    }

    public static function scoreGAD7(array $responses): array
    {
        $score = array_sum($responses);
        $severity = match(true) {
            $score <= 4 => 'minimal',
            $score <= 9 => 'mild',
            $score <= 14 => 'moderate',
            default => 'severe'
        };

        return [
            'score' => $score,
            'severity' => $severity,
            'recommendation' => $score >= 10 ? 'Cần can thiệp chuyên khoa lo âu' : 'Theo dõi và hỗ trợ tâm lý'
        ];
    }

    public static function classifySuicideRisk(string $riskLevel): array
    {
        return match($riskLevel) {
            'none' => ['action' => 'Tiếp tục theo dõi thường quy', 'frequency' => '6 tháng/lần'],
            'low' => ['action' => 'Đánh giá kỹ hơn, tăng tần suất theo dõi', 'frequency' => '1 tháng/lần'],
            'moderate' => ['action' => 'Lập kế hoạch an toàn, hỗ trợ khủng hoảng 24/7', 'frequency' => '1-2 tuần/lần'],
            'high' => ['action' => 'Nhập viện tâm thần, theo dõi sát sao', 'frequency' => 'Hàng ngày'],
            'imminent' => ['action' => 'Nhập viện khẩn cấp, không để một mình', 'frequency' => 'Liên tục'],
            default => ['action' => 'Đánh giá thêm', 'frequency' => 'N/A']
        };
    }
}
