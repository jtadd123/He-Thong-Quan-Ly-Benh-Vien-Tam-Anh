<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NutritionAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_code',
        'patient_profile_id',
        'doctor_id',
        'appointment_id',
        'height_cm',
        'weight_kg',
        'bmi',
        'bmi_category',
        'tdee_kcal',
        'diet_regimen_id',
        'water_intake_ml',
        'custom_instructions',
        'consultant_user_id',
        'assessed_at',
    ];

    protected function casts(): array
    {
        return [
            'height_cm' => 'float',
            'weight_kg' => 'float',
            'bmi' => 'float',
            'tdee_kcal' => 'integer',
            'water_intake_ml' => 'integer',
            'assessed_at' => 'datetime',
        ];
    }

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function dietRegimen(): BelongsTo
    {
        return $this->belongsTo(DietRegimen::class);
    }

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consultant_user_id');
    }

    public static function generateAssessmentCode(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "NUT-{$today}-";

        $latest = self::where('assessment_code', 'LIKE', "{$prefix}____")
            ->orderBy('assessment_code', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d{4})$/', $latest->assessment_code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
            $nextNumber++;
        } while (self::where('assessment_code', $candidate)->exists());

        return $candidate;
    }
}
