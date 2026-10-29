<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DischargeChecklist extends Model
{
    protected $table = 'discharge_checklists';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'discharge_date',
        'discharge_type',
        'discharge_summary_signed',
        'medications_reconciled',
        'medications_dispensed',
        'patient_education_done',
        'follow_up_appointment_booked',
        'follow_up_date',
        'follow_up_doctor',
        'lab_results_reviewed',
        'imaging_reviewed',
        'wound_care_instructions',
        'activity_restrictions_explained',
        'diet_instructions_given',
        'red_flag_symptoms_explained',
        'emergency_contact_confirmed',
        'transport_arranged',
        'transport_mode',
        'discharge_instructions',
        'home_care_plan',
        'medications_on_discharge',
        'checklist_complete',
        'readmission_risk_score',
        'nursing_notes',
    ];

    protected $casts = [
        'discharge_date' => 'date',
        'follow_up_date' => 'date',
        'medications_on_discharge' => 'array',
        'discharge_summary_signed' => 'boolean',
        'medications_reconciled' => 'boolean',
        'medications_dispensed' => 'boolean',
        'patient_education_done' => 'boolean',
        'follow_up_appointment_booked' => 'boolean',
        'lab_results_reviewed' => 'boolean',
        'imaging_reviewed' => 'boolean',
        'wound_care_instructions' => 'boolean',
        'activity_restrictions_explained' => 'boolean',
        'diet_instructions_given' => 'boolean',
        'red_flag_symptoms_explained' => 'boolean',
        'emergency_contact_confirmed' => 'boolean',
        'transport_arranged' => 'boolean',
        'checklist_complete' => 'boolean',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public static function calculateCompletionPercentage(array $checklistData): float
    {
        $boolFields = [
            'discharge_summary_signed',
            'medications_reconciled',
            'medications_dispensed',
            'patient_education_done',
            'follow_up_appointment_booked',
            'lab_results_reviewed',
            'imaging_reviewed',
            'red_flag_symptoms_explained',
            'emergency_contact_confirmed',
        ];

        $completed = 0;
        foreach ($boolFields as $f) {
            if (!empty($checklistData[$f])) {
                $completed++;
            }
        }

        return round(($completed / count($boolFields)) * 100, 1);
    }

    public static function calculateReadmissionRisk(array $factors): int
    {
        $score = 0;
        if (!empty($factors['multiple_comorbidities'])) $score += 3;
        if (!empty($factors['prior_30d_readmission'])) $score += 3;
        if (!empty($factors['polypharmacy'])) $score += 2;
        if (!empty($factors['low_health_literacy'])) $score += 2;
        if (!empty($factors['lives_alone'])) $score += 2;
        if (!empty($factors['no_follow_up'])) $score += 3;
        if (!empty($factors['substance_abuse'])) $score += 2;
        return min($score, 15);
    }

    public static function isReadyForDischarge(array $checklist): bool
    {
        $mandatory = ['discharge_summary_signed', 'medications_reconciled', 'patient_education_done'];
        foreach ($mandatory as $field) {
            if (empty($checklist[$field])) return false;
        }
        return true;
    }
}
