<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParenteralNutrition extends Model
{
    use HasFactory;

    protected $table = 'parenteral_nutritions';

    protected $fillable = [
        'patient_id',
        'total_calories_kcal',
        'glucose_grams',
        'amino_acids_grams',
        'lipids_grams',
        'sodium_meq',
        'potassium_meq',
        'calcium_meq',
        'phosphate_mmol',
        'osmolarity_mosm_l',
        'infusion_route',
        'refeeding_risk',
        'clinical_dietitian',
    ];

    protected $casts = [
        'glucose_grams' => 'decimal:1',
        'amino_acids_grams' => 'decimal:1',
        'lipids_grams' => 'decimal:1',
        'sodium_meq' => 'decimal:1',
        'potassium_meq' => 'decimal:1',
        'calcium_meq' => 'decimal:1',
        'phosphate_mmol' => 'decimal:1',
        'refeeding_risk' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    /**
     * Tính toán tổng Calo dinh dưỡng tĩnh mạch (Glucose 3.4 kcal/g, Protein 4 kcal/g, Lipid 20% ~ 2 kcal/mL)
     */
    public static function calculateCalories(float $aminoMl, float $glucoseMl, float $lipidMl, float $weightKg = 50.0): array
    {
        // Giả định chuẩn lâm sàng: Dextrose 20% (0.2g/ml), Amino acid 10% (0.1g/ml), Lipid 20% (2 kcal/ml)
        $glucoseG = $glucoseMl * 0.20;
        $aminoG = $aminoMl * 0.10;
        $lipidG = $lipidMl * 0.20;

        $glucoseKcal = $glucoseG * 3.4;
        $aminoKcal = $aminoG * 4.0;
        $lipidKcal = $lipidMl * 2.0;

        $totalKcal = round($glucoseKcal + $aminoKcal + $lipidKcal);
        $kcalPerKg = $weightKg > 0 ? round($totalKcal / $weightKg, 1) : 0;

        return [
            'total_calories' => $totalKcal,
            'calories_per_kg' => $kcalPerKg,
            'glucose_grams' => round($glucoseG, 1),
            'amino_acids_grams' => round($aminoG, 1),
            'lipids_grams' => round($lipidG, 1),
        ];
    }
}
