<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DietRegimen extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'target_disease',
        'daily_calorie_target',
        'macronutrient_ratio',
        'foods_recommended',
        'foods_restricted',
        'foods_avoided',
        'sample_meal_plan',
    ];

    protected function casts(): array
    {
        return [
            'daily_calorie_target' => 'integer',
            'macronutrient_ratio' => 'array',
            'sample_meal_plan' => 'array',
        ];
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(NutritionAssessment::class);
    }
}
