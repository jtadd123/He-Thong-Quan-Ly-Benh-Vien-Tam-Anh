<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TherapeuticDrugMonitoring extends Model
{
    use HasFactory;

    protected $table = 'therapeutic_drug_monitorings';

    protected $fillable = [
        'patient_id',
        'medication_name',
        'trough_concentration_cmin',
        'peak_concentration_cmax',
        'creatinine_clearance_crcl',
        'target_range',
        'is_within_target',
        'dose_adjustment_recommendation',
        'pharmacist_evaluator',
    ];

    protected $casts = [
        'trough_concentration_cmin' => 'decimal:2',
        'peak_concentration_cmax' => 'decimal:2',
        'creatinine_clearance_crcl' => 'decimal:2',
        'is_within_target' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    /**
     * Đánh giá nồng độ thuốc hẹp (Vancomycin, Aminoglycoside, v.v.)
     */
    public static function evaluateConcentration(string $drugName, float $cmin): array
    {
        $drug = strtolower($drugName);
        if (str_contains($drug, 'vancomycin')) {
            if ($cmin < 10.0) {
                return [
                    'status' => 'subtherapeutic',
                    'is_within_target' => false,
                    'recommendation' => 'Nồng độ đáy Cmin < 10 mg/L dưới ngưỡng điều trị. Khuyến cáo tăng liều dùng hoặc rút ngắn khoảng cách đưa liều để đạt đích 15-20 mg/L trong nhiễm khuẩn nặng MRSA.',
                ];
            } elseif ($cmin > 20.0) {
                return [
                    'status' => 'toxic',
                    'is_within_target' => false,
                    'recommendation' => 'CẢNH BÁO: Nồng độ đáy Cmin > 20 mg/L vượt ngưỡng an toàn, nguy cơ độc tính thận cao! Tạm dừng hoặc giảm liều, theo dõi sát Creatinine huyết thanh.',
                ];
            } else {
                return [
                    'status' => 'therapeutic',
                    'is_within_target' => true,
                    'recommendation' => 'Nồng độ đáy Cmin đạt mục tiêu điều trị tối ưu (10 - 20 mg/L). Tiếp tục duy trì chế độ liều hiện tại.',
                ];
            }
        }

        return [
            'status' => 'therapeutic',
            'is_within_target' => true,
            'recommendation' => 'Nồng độ thuốc trong phạm vi an toàn theo dõi lâm sàng.',
        ];
    }
}
