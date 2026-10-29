<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class TelehealthConsultation extends Model
{
    protected $table = 'telehealth_consultations';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'consultation_type',
        'platform',
        'meeting_link',
        'meeting_id',
        'scheduled_at',
        'started_at',
        'ended_at',
        'duration_minutes',
        'consultation_reason',
        'chief_complaint',
        'assessment',
        'plan',
        'prescription_issued',
        'referral_needed',
        'referral_to',
        'status',
        'patient_satisfaction',
        'technical_issues',
        'technical_notes',
        'consultation_fee',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'prescription_issued' => 'boolean',
        'referral_needed' => 'boolean',
        'technical_issues' => 'boolean',
        'patient_satisfaction' => 'decimal:1',
        'consultation_fee' => 'decimal:0',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public static function generateMeetingId(): string
    {
        return strtoupper(substr(md5(uniqid()), 0, 8)) . '-' . rand(1000, 9999);
    }

    public static function generateMeetingLink(string $meetingId): string
    {
        return "https://meet.benhvientamanh.vn/{$meetingId}";
    }

    public static function calculateAvgSatisfaction(array $ratings): float
    {
        if (empty($ratings)) return 0.0;
        return round(array_sum($ratings) / count($ratings), 1);
    }

    public static function classifyConsultationStatus(string $scheduledAt): string
    {
        $now = now();
        $scheduled = Carbon::parse($scheduledAt);
        if ($scheduled->isFuture()) return 'upcoming';
        if ($scheduled->diffInMinutes($now) <= 60) return 'in_window';
        return 'overdue';
    }
}
