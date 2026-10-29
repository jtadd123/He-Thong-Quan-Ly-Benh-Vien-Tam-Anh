<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadiationDoseLog extends Model
{
    use HasFactory;

    protected $table = 'radiation_dose_logs';

    protected $fillable = [
        'patient_id',
        'procedure_name',
        'dose_msv',
        'cumulative_dose_msv',
        'alara_status',
        'radiology_technician',
    ];

    protected $casts = [
        'dose_msv' => 'decimal:3',
        'cumulative_dose_msv' => 'decimal:3',
    ];

    public function patient()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    /**
     * Phân loại mức độ nguy cơ liều bức xạ theo chuẩn ALARA & ICRP
     */
    public static function classifyRiskLevel(float $msv): string
    {
        if ($msv >= 20.0) return 'critical'; // Vượt ngưỡng an toàn hàng năm (20 mSv)
        if ($msv >= 10.0) return 'high'; // Liều cao (CT đa dãy, can thiệp mạch kéo dài)
        if ($msv >= 2.0) return 'medium'; // Liều trung bình (CT ngực/bụng)
        return 'low'; // Liều thấp (X-quang thông thường < 1 mSv)
    }
}
