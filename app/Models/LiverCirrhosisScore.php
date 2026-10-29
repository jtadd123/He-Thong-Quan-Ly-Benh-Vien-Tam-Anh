<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiverCirrhosisScore extends Model
{
    protected $table = 'liver_cirrhosis_scores';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'bilirubin_cp',
        'albumin_cp',
        'inr_cp',
        'ascites_grade',
        'encephalopathy_grade',
        'child_pugh_score',
        'child_pugh_class',
        'one_year_survival',
        'creatinine_meld',
        'bilirubin_meld',
        'inr_meld',
        'sodium_meld',
        'dialysis_twice_weekly',
        'meld_score',
        'meld_na_score',
        'three_month_mortality_pct',
        'transplant_urgency',
        'clinical_notes',
    ];

    protected $casts = [
        'bilirubin_cp' => 'decimal:2',
        'albumin_cp' => 'decimal:1',
        'inr_cp' => 'decimal:2',
        'creatinine_meld' => 'decimal:2',
        'bilirubin_meld' => 'decimal:2',
        'inr_meld' => 'decimal:2',
        'sodium_meld' => 'decimal:1',
        'meld_score' => 'decimal:1',
        'meld_na_score' => 'decimal:1',
        'dialysis_twice_weekly' => 'boolean',
        'one_year_survival' => 'decimal:1',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public static function calculateChildPugh(float $bilirubin, float $albumin, float $inr, string $ascites, string $encephalopathy): array
    {
        $score = 0;
        // Bilirubin mg/dL
        $score += $bilirubin < 2.0 ? 1 : ($bilirubin <= 3.0 ? 2 : 3);
        // Albumin g/dL
        $score += $albumin > 3.5 ? 1 : ($albumin >= 2.8 ? 2 : 3);
        // INR/PT
        $score += $inr < 1.7 ? 1 : ($inr <= 2.2 ? 2 : 3);
        // Ascites
        $score += match($ascites) { 'none' => 1, 'mild' => 2, default => 3 };
        // Encephalopathy
        $score += match($encephalopathy) { 'none' => 1, 'grade1', 'grade2' => 2, default => 3 };

        $class = $score <= 6 ? 'A' : ($score <= 9 ? 'B' : 'C');
        $survival = match($class) { 'A' => 100.0, 'B' => 80.0, default => 45.0 };

        return ['score' => $score, 'class' => $class, 'one_year_survival' => $survival];
    }

    public static function calculateMeld(float $creatinine, float $bilirubin, float $inr, bool $dialysis = false): float
    {
        $cr = $dialysis ? 4.0 : min($creatinine, 4.0);
        $cr = max($cr, 1.0);
        $bili = max($bilirubin, 1.0);
        $inrVal = max($inr, 1.0);
        $meld = 10 * (0.957 * log($cr) + 0.378 * log($bili) + 1.12 * log($inrVal)) + 6.43;
        return (float)round($meld, 1);
    }

    public static function calculateMeldNa(float $meld, float $sodium): float
    {
        $na = max(125, min($sodium, 137));
        $meldNa = $meld + 1.32 * (137 - $na) - (0.033 * $meld * (137 - $na));
        return (float)round($meldNa, 1);
    }

    public static function getMeldMortality(float $meldScore): int
    {
        if ($meldScore < 10) return 2;
        if ($meldScore < 20) return 6;
        if ($meldScore < 30) return 19;
        if ($meldScore < 40) return 52;
        return 71;
    }
}
