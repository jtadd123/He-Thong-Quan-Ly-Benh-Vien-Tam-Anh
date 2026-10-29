<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SepsisPredictionLog extends Model
{
    protected $table = 'sepsis_prediction_logs';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'qsofa_score',
        'sofa_score',
        'sirs_score',
        'temperature',
        'heart_rate',
        'respiratory_rate',
        'sbp',
        'gcs',
        'lactate',
        'creatinine',
        'bilirubin',
        'platelet_count',
        'pao2_fio2',
        'map',
        'urine_output',
        'sepsis_risk',
        'ai_prediction',
        'ai_confidence_score',
        'alert_sent',
        'alert_sent_to',
        'clinical_notes',
        'status',
    ];

    protected $casts = [
        'qsofa_score' => 'decimal:1',
        'sofa_score' => 'decimal:1',
        'sirs_score' => 'decimal:1',
        'temperature' => 'decimal:2',
        'sbp' => 'decimal:1',
        'gcs' => 'decimal:1',
        'lactate' => 'decimal:2',
        'creatinine' => 'decimal:2',
        'bilirubin' => 'decimal:2',
        'pao2_fio2' => 'decimal:1',
        'map' => 'decimal:1',
        'urine_output' => 'decimal:1',
        'ai_confidence_score' => 'decimal:2',
        'alert_sent' => 'boolean',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public static function calculateQsofa(float $respRate, float $sbp, float $gcs): float
    {
        $score = 0;
        if ($respRate >= 22) $score++;
        if ($sbp <= 100) $score++;
        if ($gcs < 15) $score++;
        return (float)$score;
    }

    public static function calculateSofa(float $pao2Fio2, float $plateletsK, float $bilirubinMgdl, float $mapMmhg, float $creatinineMgdl, float $glasgowComa, bool $onVasopressors = false): float
    {
        $score = 0;
        // Respiration
        if ($pao2Fio2 < 100) $score += 4;
        elseif ($pao2Fio2 < 200) $score += 3;
        elseif ($pao2Fio2 < 300) $score += 2;
        elseif ($pao2Fio2 < 400) $score += 1;

        // Coagulation
        if ($plateletsK < 20) $score += 4;
        elseif ($plateletsK < 50) $score += 3;
        elseif ($plateletsK < 100) $score += 2;
        elseif ($plateletsK < 150) $score += 1;

        // Liver
        if ($bilirubinMgdl >= 12) $score += 4;
        elseif ($bilirubinMgdl >= 6) $score += 3;
        elseif ($bilirubinMgdl >= 2) $score += 2;
        elseif ($bilirubinMgdl >= 1.2) $score += 1;

        // Cardiovascular
        if ($onVasopressors) $score += 3;
        elseif ($mapMmhg < 70) $score += 1;

        // CNS
        if ($glasgowComa < 6) $score += 4;
        elseif ($glasgowComa < 10) $score += 3;
        elseif ($glasgowComa < 13) $score += 2;
        elseif ($glasgowComa < 15) $score += 1;

        // Renal
        if ($creatinineMgdl >= 5) $score += 4;
        elseif ($creatinineMgdl >= 3.5) $score += 3;
        elseif ($creatinineMgdl >= 2) $score += 2;
        elseif ($creatinineMgdl >= 1.2) $score += 1;

        return (float)$score;
    }

    public static function classifySepsisRisk(float $qsofa, float $sofa): string
    {
        if ($sofa >= 11 || ($qsofa >= 2 && $sofa >= 8)) return 'critical';
        if ($sofa >= 6 || $qsofa >= 2) return 'high';
        if ($sofa >= 2 || $qsofa >= 1) return 'moderate';
        return 'low';
    }

    public static function predictAi(float $qsofa, float $sofa, float $lactate): string
    {
        if ($sofa >= 2 && $lactate > 2) return 'septic_shock';
        if ($sofa >= 2) return 'sepsis';
        if ($qsofa >= 2) return 'sepsis_watch';
        return 'no_sepsis';
    }
}
