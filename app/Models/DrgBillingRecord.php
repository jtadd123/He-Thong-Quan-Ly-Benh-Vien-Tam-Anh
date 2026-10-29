<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrgBillingRecord extends Model
{
    protected $table = 'drg_billing_records';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'drg_code',
        'drg_description',
        'mdc_code',
        'principal_diagnosis_icd10',
        'secondary_diagnoses',
        'procedures_icd9',
        'length_of_stay_days',
        'geometric_mean_los',
        'drg_weight',
        'base_rate',
        'expected_payment',
        'actual_cost',
        'cost_variance',
        'mcc_cc',
        'outlier_case',
        'outlier_payment',
        'insurance_type',
        'insurance_code',
        'insurance_coverage_pct',
        'patient_copay',
        'billing_status',
        'coding_notes',
    ];

    protected $casts = [
        'secondary_diagnoses' => 'array',
        'procedures_icd9' => 'array',
        'drg_weight' => 'decimal:4',
        'insurance_coverage_pct' => 'decimal:2',
        'outlier_case' => 'boolean',
        'expected_payment' => 'decimal:0',
        'actual_cost' => 'decimal:0',
        'cost_variance' => 'decimal:0',
        'patient_copay' => 'decimal:0',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public static function calculateExpectedPayment(float $drgWeight, float $baseRate): float
    {
        return round($drgWeight * $baseRate, 0);
    }

    public static function calculateCostVariance(float $actualCost, float $expectedPayment): float
    {
        return round($actualCost - $expectedPayment, 0);
    }

    public static function isOutlierCase(int $lengthOfStay, int $geometricMeanLos, float $drgWeight): bool
    {
        $threshold = $geometricMeanLos + max(1, (int)($geometricMeanLos * 0.5));
        return $lengthOfStay > $threshold;
    }

    public static function calculatePatientCopay(float $totalCost, float $coveragePct): float
    {
        return round($totalCost * (1 - $coveragePct / 100), 0);
    }

    public static function getCaseMixIndex(array $drgWeights): float
    {
        if (empty($drgWeights)) return 0.0;
        return round(array_sum($drgWeights) / count($drgWeights), 4);
    }
}
