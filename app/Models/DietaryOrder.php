<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DietaryOrder extends Model
{
    protected $table = 'dietary_orders';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'diet_type',
        'caloric_target',
        'protein_target_g',
        'fluid_restriction_ml',
        'sodium_restriction_mg',
        'allergy_notes',
        'food_preferences',
        'texture',
        'halal',
        'vegetarian',
        'meal_frequency',
        'supplements',
        'bmi',
        'nutritional_risk_score',
        'nutritional_status',
        'order_start_date',
        'order_end_date',
        'is_active',
        'dietitian_notes',
    ];

    protected $casts = [
        'supplements' => 'array',
        'order_start_date' => 'date',
        'order_end_date' => 'date',
        'halal' => 'boolean',
        'vegetarian' => 'boolean',
        'is_active' => 'boolean',
        'caloric_target' => 'decimal:1',
        'protein_target_g' => 'decimal:1',
        'bmi' => 'decimal:2',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public static function calculateBMI(float $weightKg, float $heightCm): float
    {
        $heightM = $heightCm / 100;
        if ($heightM <= 0) return 0.0;
        return round($weightKg / ($heightM * $heightM), 2);
    }

    public static function classifyBMI(float $bmi): string
    {
        if ($bmi < 16) return 'severe_underweight';
        if ($bmi < 18.5) return 'underweight';
        if ($bmi < 23) return 'normal';
        if ($bmi < 25) return 'overweight';
        if ($bmi < 30) return 'obese_1';
        if ($bmi < 35) return 'obese_2';
        return 'obese_3';
    }

    public static function calculateCaloricNeeds(float $weightKg, string $activityLevel = 'bed_rest', string $condition = 'normal'): float
    {
        $base = $weightKg * match($activityLevel) { 'bed_rest' => 20, 'sedentary' => 25, 'moderate' => 30, default => 35 };
        $stressFactor = match($condition) { 'surgery' => 1.2, 'infection' => 1.3, 'burn' => 1.5, default => 1.0 };
        return round($base * $stressFactor, 1);
    }

    public static function assessNutritionalRisk(float $bmi, float $weightLossPct, bool $poorIntake): string
    {
        $score = 0;
        if ($bmi < 18.5) $score += 2;
        if ($weightLossPct >= 10) $score += 2; elseif ($weightLossPct >= 5) $score += 1;
        if ($poorIntake) $score += 1;

        if ($score >= 3) return 'malnourished';
        if ($score >= 2) return 'at_risk';
        return 'well_nourished';
    }
}
