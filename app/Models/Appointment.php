<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const SHIFT_MORNING = 'morning';
    public const SHIFT_AFTERNOON = 'afternoon';

    protected $fillable = [
        'appointment_code',
        'patient_profile_id',
        'doctor_id',
        'specialty_id',
        'appointment_date',
        'shift',
        'appointment_time',
        'symptom_description',
        'status',
        'cancellation_reason',
        'created_by_user_id',
        'esi_level',
        'esi_criteria',
        'triage_notes',
        'triaged_by_user_id',
        'triaged_at',
        'called_at',
        'counter_number',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'esi_level' => 'integer',
            'esi_criteria' => 'array',
            'triaged_at' => 'datetime',
            'called_at' => 'datetime',
        ];
    }

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_profile_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function triagedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triaged_by_user_id');
    }

    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PatientInvoice::class);
    }

    public function getEsiInfoAttribute(): array
    {
        return \App\Services\EmergencyTriageService::ESI_LEVELS[$this->esi_level ?? 5] ?? \App\Services\EmergencyTriageService::ESI_LEVELS[5];
    }

    public function getEsiBadgeAttribute(): string
    {
        return \App\Services\EmergencyTriageService::renderBadge($this->esi_level ?? 5);
    }

    public function scopePriorityEsiOrder(Builder $query): Builder
    {
        return $query->orderBy('esi_level', 'asc')->orderBy('created_at', 'asc');
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class, 'appointment_id');
    }

    public function vitals(): HasMany
    {
        return $this->hasMany(PatientVital::class, 'appointment_id');
    }

    public function notifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PatientNotification::class);
    }

    public function dayBedAdmissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DayBedAdmission::class);
    }

    public function clinicalConsultations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ClinicalConsultation::class);
    }

    public function vaccinationRecords(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VaccinationRecord::class);
    }

    public function surveys(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PatientSurvey::class);
    }

    public function latestVital(): HasOne
    {
        return $this->hasOne(PatientVital::class, 'appointment_id')->latestOfMany('recorded_at');
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('appointment_date', Carbon::today());
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function getStatusNameAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Chờ tiếp nhận',
            self::STATUS_CONFIRMED => 'Đã tiếp nhận (Chờ khám)',
            self::STATUS_COMPLETED => 'Đã hoàn thành',
            self::STATUS_CANCELLED => 'Đã hủy',
            default => 'Không xác định',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-warning text-dark',
            self::STATUS_CONFIRMED => 'bg-primary text-white',
            self::STATUS_COMPLETED => 'bg-success text-white',
            self::STATUS_CANCELLED => 'bg-danger text-white',
            default => 'bg-secondary text-white',
        };
    }

    public function getShiftNameAttribute(): string
    {
        return match ($this->shift) {
            self::SHIFT_MORNING => 'Ca Sáng (07:30 - 11:30)',
            self::SHIFT_AFTERNOON => 'Ca Chiều (13:30 - 17:00)',
            default => $this->shift,
        };
    }

    public function getQueueNumberAttribute(): int
    {
        return self::where('doctor_id', $this->doctor_id)
            ->whereDate('appointment_date', $this->appointment_date)
            ->where('shift', $this->shift)
            ->where('id', '<=', $this->id)
            ->count();
    }

    public function getWaitingMinutesAttribute(): int
    {
        if ($this->status !== self::STATUS_CONFIRMED) {
            return 0;
        }
        return (int) Carbon::now()->diffInMinutes($this->updated_at);
    }

    public function getIsPriorityAttribute(): bool
    {
        if ($this->patientProfile && $this->patientProfile->dob) {
            $age = $this->patientProfile->dob->age;
            if ($age >= 75 || $age <= 1) {
                return true;
            }
        }
        return str_contains($this->symptom_description ?? '', '[ƯU TIÊN]');
    }

    public static function generateAppointmentCode(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "AP-{$today}-";

        // Generate 4-character alphanumeric token (avoiding ambiguous chars 0, O, 1, I)
        $charset = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $charsetLen = strlen($charset);

        do {
            $token = '';
            for ($i = 0; $i < 4; $i++) {
                $token .= $charset[random_int(0, $charsetLen - 1)];
            }
            $code = "{$prefix}{$token}";
        } while (self::where('appointment_code', $code)->exists());

        return $code;
    }
}
