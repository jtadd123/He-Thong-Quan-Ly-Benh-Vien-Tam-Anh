<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentilatorRecord extends Model
{
    protected $table = 'ventilator_records';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'ventilation_mode',
        'tidal_volume_ml',
        'respiratory_rate_set',
        'peep',
        'fio2',
        'inspiratory_pressure',
        'driving_pressure',
        'plateau_pressure',
        'peak_pressure',
        'compliance',
        'spo2',
        'etco2',
        'pao2',
        'pco2',
        'ph_value',
        'berlin_category',
        'prone_positioning',
        'recruitment_maneuver',
        'weaning_assessment',
        'weaning_status',
        'total_ventilation_hours',
        'nursing_notes',
    ];

    protected $casts = [
        'weaning_assessment' => 'array',
        'prone_positioning' => 'boolean',
        'recruitment_maneuver' => 'boolean',
        'fio2' => 'decimal:2',
        'tidal_volume_ml' => 'decimal:1',
        'peep' => 'decimal:1',
        'driving_pressure' => 'decimal:1',
        'plateau_pressure' => 'decimal:1',
        'ph_value' => 'decimal:2',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public static function calculateDrivingPressure(float $plateauPressure, float $peep): float
    {
        return round($plateauPressure - $peep, 1);
    }

    public static function calculatePf(float $pao2, float $fio2): float
    {
        if ($fio2 <= 0) return 0;
        return round($pao2 / $fio2, 1);
    }

    public static function classifyBerlin(float $pfRatio): string
    {
        if ($pfRatio < 100) return 'severe';
        if ($pfRatio < 200) return 'moderate';
        if ($pfRatio < 300) return 'mild';
        return 'normal';
    }

    public static function assessWeaningReadiness(array $params): array
    {
        $criteria = [];
        $criteria['fio2_le_40'] = ($params['fio2'] ?? 1) <= 0.4;
        $criteria['peep_le_8'] = ($params['peep'] ?? 20) <= 8;
        $criteria['rsbi_ok'] = ($params['rsbi'] ?? 200) < 105;
        $criteria['spo2_ge_92'] = ($params['spo2'] ?? 0) >= 92;
        $criteria['gcs_ge_13'] = ($params['gcs'] ?? 0) >= 13;
        $allMet = count(array_filter($criteria)) === count($criteria);
        return [
            'criteria' => $criteria,
            'ready_for_sbt' => $allMet,
            'recommendation' => $allMet ? 'Thực hiện SBT 30-120 phút' : 'Chưa đủ điều kiện cai máy'
        ];
    }

    public static function calculateIdealTidalVolume(float $heightCm, string $gender = 'male'): float
    {
        $ibw = $gender === 'male' ? 50 + 0.91 * ($heightCm - 152.4) : 45.5 + 0.91 * ($heightCm - 152.4);
        return round(max($ibw, 30) * 6, 0);
    }
}
