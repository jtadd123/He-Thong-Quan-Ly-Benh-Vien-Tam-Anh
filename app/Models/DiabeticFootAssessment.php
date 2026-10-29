<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiabeticFootAssessment extends Model
{
    protected $table = 'diabetic_foot_assessments';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'foot_side',
        'wagner_grade',
        'wagner_description',
        'abi_left',
        'abi_right',
        'abi_interpretation',
        'tbpi',
        'neuropathy_present',
        'monofilament_10g',
        'vibration_sensation',
        'protective_sensation_lost',
        'infection_present',
        'infection_severity',
        'wound_culture_organism',
        'osteomyelitis_suspected',
        'treatment_plan',
        'hba1c',
        'assessment_notes',
    ];

    protected $casts = [
        'abi_left' => 'decimal:2',
        'abi_right' => 'decimal:2',
        'tbpi' => 'decimal:2',
        'hba1c' => 'decimal:1',
        'neuropathy_present' => 'boolean',
        'monofilament_10g' => 'boolean',
        'vibration_sensation' => 'boolean',
        'protective_sensation_lost' => 'boolean',
        'infection_present' => 'boolean',
        'osteomyelitis_suspected' => 'boolean',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public static function classifyWagner(int $grade): array
    {
        $map = [
            0 => ['description' => 'Không có loét hở, bàn chân nguy cơ cao', 'treatment' => 'Chăm sóc phòng ngừa, giày chỉnh hình chuyên biệt'],
            1 => ['description' => 'Loét nông qua lớp da chưa đến gân', 'treatment' => 'Cắt lọc mô hoại tử, băng gạc ẩm, chống tải bàn chân'],
            2 => ['description' => 'Loét sâu đến gân/khớp không viêm xương', 'treatment' => 'Điều trị ngoại trú tích cực, kháng sinh phổ rộng'],
            3 => ['description' => 'Loét sâu có áp xe hoặc viêm xương tủy', 'treatment' => 'Nhập viện khẩn cấp, kháng sinh IV, phẫu thuật rạch tháo mủ'],
            4 => ['description' => 'Hoại thư khu trú phần trước bàn chân', 'treatment' => 'Đánh giá mạch máu, phẫu thuật cắt cụt ngón hoặc tia'],
            5 => ['description' => 'Hoại thư toàn bộ bàn chân', 'treatment' => 'Cắt cụt chi thể dưới gối (trên/dưới mắt cá)'],
        ];
        return $map[$grade] ?? ['description' => 'Không xác định', 'treatment' => 'Cần hội chẩn đa chuyên khoa'];
    }

    public static function interpretAbi(float $abi): string
    {
        if ($abi > 1.3) return 'medial_calcification';
        if ($abi >= 0.9) return 'normal';
        if ($abi >= 0.7) return 'mild_pad';
        if ($abi >= 0.4) return 'moderate_pad';
        return 'severe_pad';
    }

    public static function calculateAmputationRisk(int $wagnerGrade, float $abi, bool $osteomyelitis): string
    {
        $risk = 0;
        $risk += $wagnerGrade * 2;
        if ($abi < 0.4) $risk += 4;
        elseif ($abi < 0.7) $risk += 2;
        if ($osteomyelitis) $risk += 3;

        if ($risk >= 10) return 'very_high';
        if ($risk >= 7) return 'high';
        if ($risk >= 4) return 'moderate';
        return 'low';
    }
}
