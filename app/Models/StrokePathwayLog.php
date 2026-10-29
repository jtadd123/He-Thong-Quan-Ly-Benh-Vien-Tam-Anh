<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class StrokePathwayLog extends Model
{
    protected $table = 'stroke_pathway_logs';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'stroke_type',
        'symptom_onset_time',
        'door_time',
        'ct_scan_time',
        'tpa_decision_time',
        'tpa_start_time',
        'thrombectomy_time',
        'nihss_score',
        'aspects_score',
        'mrs_at_admission',
        'mrs_at_discharge',
        'tpa_eligible',
        'tpa_given',
        'tpa_contraindication',
        'tpa_dose_mg',
        'thrombectomy_done',
        'door_to_ct_minutes',
        'door_to_needle_minutes',
        'onset_to_treatment_minutes',
        'target_dtn_met',
        'outcome',
        'clinical_notes',
    ];

    protected $casts = [
        'symptom_onset_time' => 'datetime',
        'door_time' => 'datetime',
        'ct_scan_time' => 'datetime',
        'tpa_start_time' => 'datetime',
        'tpa_eligible' => 'boolean',
        'tpa_given' => 'boolean',
        'thrombectomy_done' => 'boolean',
        'target_dtn_met' => 'boolean',
        'tpa_dose_mg' => 'decimal:2',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public static function calculateDoorToNeedle(?string $doorTime, ?string $tpaTime): ?int
    {
        if (!$doorTime || !$tpaTime) return null;
        return (int)Carbon::parse($doorTime)->diffInMinutes(Carbon::parse($tpaTime));
    }

    public static function isTpaEligible(int $nihssScore, ?int $aspectsScore, int $onsetMinutes, array $contraindications = []): bool
    {
        if (!empty($contraindications)) return false;
        if ($onsetMinutes > 270) return false; // >4.5 hours
        if ($nihssScore < 4 || $nihssScore > 25) return false;
        if ($aspectsScore !== null && $aspectsScore < 7) return false;
        return true;
    }

    public static function calculateTpaDose(float $weightKg): float
    {
        return min(round($weightKg * 0.9, 1), 90.0);
    }

    public static function classifyNihss(int $score): string
    {
        if ($score == 0) return 'Không triệu chứng';
        if ($score <= 4) return 'Nhẹ';
        if ($score <= 15) return 'Trung bình';
        if ($score <= 20) return 'Nặng';
        return 'Rất nặng';
    }
}
