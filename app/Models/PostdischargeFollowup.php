<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostdischargeFollowup extends Model
{
    protected $table = 'postdischarge_followups';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'days_post_discharge',
        'contact_method',
        'contact_attempt',
        'patient_reached',
        'readmission_occurred',
        'readmission_date',
        'readmission_reason',
        'medication_adherence',
        'follow_up_appointment_kept',
        'symptoms_resolved',
        'new_symptoms',
        'patient_wellbeing_score',
        'escalation_needed',
        'escalation_action',
        'call_agent_name',
        'call_duration_minutes',
        'call_notes',
        'outcome',
    ];

    protected $casts = [
        'readmission_date' => 'date',
        'patient_reached' => 'boolean',
        'readmission_occurred' => 'boolean',
        'medication_adherence' => 'boolean',
        'follow_up_appointment_kept' => 'boolean',
        'symptoms_resolved' => 'boolean',
        'escalation_needed' => 'boolean',
        'patient_wellbeing_score' => 'decimal:1',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public static function getContactSchedule(): array
    {
        return [
            ['days' => 2, 'method' => 'phone_call', 'purpose' => 'Kiểm tra ban đầu sau xuất viện'],
            ['days' => 7, 'method' => 'phone_call', 'purpose' => 'Tuần đầu theo dõi phục hồi'],
            ['days' => 30, 'method' => 'phone_call', 'purpose' => 'Theo dõi định kỳ 30 ngày'],
        ];
    }

    public static function calculateReadmissionRate(array $followups): float
    {
        if (empty($followups)) return 0.0;
        $readmitted = count(array_filter($followups, fn($f) => !empty($f['readmission_occurred'])));
        return round(($readmitted / count($followups)) * 100, 1);
    }

    public static function needsEscalation(bool $patientReached, bool $symptomsResolved, float $wellbeingScore): bool
    {
        if (!$patientReached) return true;
        if (!$symptomsResolved) return true;
        if ($wellbeingScore < 5.0) return true;
        return false;
    }
}
