<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class StemiCathlabActivation extends Model
{
    protected $table = 'stemi_cathlab_activations';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'ecg_time',
        'diagnosis_time',
        'cathlab_activation_time',
        'balloon_time',
        'thrombolysis_time',
        'door_to_balloon_minutes',
        'door_to_ecg_minutes',
        'target_dtb_met',
        'infarct_territory',
        'culprit_vessel',
        'timi_flow_post_pci',
        'ef_post_pci',
        'reperfusion_strategy',
        'medications_given',
        'killip_class',
        'outcome',
        'clinical_notes',
    ];

    protected $casts = [
        'ecg_time' => 'datetime',
        'diagnosis_time' => 'datetime',
        'cathlab_activation_time' => 'datetime',
        'balloon_time' => 'datetime',
        'medications_given' => 'array',
        'target_dtb_met' => 'boolean',
        'ef_post_pci' => 'decimal:1',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public static function calculateDoorToBalloon(?string $doorTime, ?string $balloonTime): ?int
    {
        if (!$doorTime || !$balloonTime) return null;
        return (int)Carbon::parse($doorTime)->diffInMinutes(Carbon::parse($balloonTime));
    }

    public static function isTargetMet(?int $dtbMinutes, string $strategy = 'primary_pci'): bool
    {
        if ($dtbMinutes === null) return false;
        return match($strategy) {
            'primary_pci' => $dtbMinutes <= 90,
            'thrombolysis' => $dtbMinutes <= 30,
            default => false
        };
    }

    public static function classifyKillip(string $class): array
    {
        return match($class) {
            'I' => ['description' => 'Không suy tim', 'mortality_30d' => '6%'],
            'II' => ['description' => 'Suy tim nhẹ-trung bình (rale ẩm < 50%)', 'mortality_30d' => '17%'],
            'III' => ['description' => 'Phù phổi cấp', 'mortality_30d' => '38%'],
            'IV' => ['description' => 'Sốc tim', 'mortality_30d' => '81%'],
            default => ['description' => 'Không xác định', 'mortality_30d' => 'N/A']
        };
    }

    public static function timiRiskScore(array $params): int
    {
        $score = 0;
        if (($params['age'] ?? 0) >= 65) $score++;
        if (($params['age'] ?? 0) >= 75) $score++;
        if (!empty($params['diabetes_or_hypertension_or_angina'])) $score++;
        if (($params['sbp'] ?? 999) < 100) $score++;
        if (($params['heart_rate'] ?? 0) > 100) $score++;
        if (($params['killip_class'] ?? 1) >= 2) $score++;
        if (($params['weight_kg'] ?? 100) < 67) $score++;
        if (!empty($params['anterior_stemi_or_lbbb'])) $score++;
        if (($params['time_to_treatment_hours'] ?? 0) > 4) $score++;
        return $score;
    }
}
