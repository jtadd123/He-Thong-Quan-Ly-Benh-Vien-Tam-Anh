<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicalConsultation extends Model
{
    use HasFactory;

    public const URGENCY_STAT = 'stat';        // Tối khẩn (phản hồi trong 15 phút)
    public const URGENCY_URGENT = 'urgent';    // Khẩn cấp (phản hồi trong 1 giờ)
    public const URGENCY_ROUTINE = 'routine';  // Thường quy (trong ngày)

    public const STATUS_REQUESTED = 'requested';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'consultation_code',
        'medical_record_id',
        'appointment_id',
        'patient_profile_id',
        'requesting_user_id',
        'requesting_specialty_id',
        'target_specialty_id',
        'target_doctor_id',
        'urgency_level',
        'reason',
        'clinical_summary',
        'proposed_questions',
        'consultation_result',
        'treatment_recommendations',
        'consultant_doctor_id',
        'responded_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
        ];
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_profile_id');
    }

    public function requestingUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requesting_user_id');
    }

    public function requestingSpecialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class, 'requesting_specialty_id');
    }

    public function targetSpecialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class, 'target_specialty_id');
    }

    public function targetDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'target_doctor_id');
    }

    public function consultantDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'consultant_doctor_id');
    }

    public static function generateConsultationCode(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "HC-{$today}-";

        $latest = self::where('consultation_code', 'LIKE', "{$prefix}____")
            ->orderBy('consultation_code', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d{4})$/', $latest->consultation_code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
            $nextNumber++;
        } while (self::where('consultation_code', $candidate)->exists());

        return $candidate;
    }

    public function getUrgencyBadgeAttribute(): string
    {
        return match ($this->urgency_level) {
            self::URGENCY_STAT => '<span class="badge bg-danger p-2 text-white animate-pulse"><i class="bi bi-exclamation-octagon-fill me-1"></i>TỐI KHẨN (15 PHÚT)</span>',
            self::URGENCY_URGENT => '<span class="badge bg-warning text-dark p-2"><i class="bi bi-clock-history me-1"></i>KHẨN CẤP (1 GIỜ)</span>',
            default => '<span class="badge bg-info text-dark p-2"><i class="bi bi-calendar-event me-1"></i>THƯỜNG QUY</span>',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_REQUESTED => '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Đang chờ hội chẩn</span>',
            self::STATUS_IN_PROGRESS => '<span class="badge bg-primary"><i class="bi bi-arrow-repeat me-1"></i>Đang hội chẩn</span>',
            self::STATUS_COMPLETED => '<span class="badge bg-success"><i class="bi bi-check2-circle me-1"></i>Đã có kết luận</span>',
            self::STATUS_CANCELLED => '<span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i>Đã hủy</span>',
            default => '<span class="badge bg-light text-dark">' . e($this->status) . '</span>',
        };
    }
}
