<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VaccinationRecord extends Model
{
    use HasFactory;

    public const STATUS_OBSERVING = 'observing';
    public const STATUS_CLEARED = 'cleared';
    public const STATUS_ADVERSE = 'adverse_reaction';

    protected $fillable = [
        'record_code',
        'patient_profile_id',
        'vaccine_id',
        'appointment_id',
        'dose_number',
        'batch_number',
        'batch_expiry_date',
        'injection_site',
        'administered_by_user_id',
        'screening_passed',
        'body_temperature',
        'screening_notes',
        'administered_at',
        'next_dose_due_date',
        'observation_status',
        'observation_cleared_at',
        'adverse_reaction_notes',
    ];

    protected function casts(): array
    {
        return [
            'batch_expiry_date' => 'date',
            'screening_passed' => 'boolean',
            'body_temperature' => 'decimal:1',
            'administered_at' => 'datetime',
            'next_dose_due_date' => 'date',
            'observation_cleared_at' => 'datetime',
        ];
    }

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_profile_id');
    }

    public function vaccine(): BelongsTo
    {
        return $this->belongsTo(Vaccine::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function administeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'administered_by_user_id');
    }

    public static function generateRecordCode(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "VX-{$today}-";

        $latest = self::where('record_code', 'LIKE', "{$prefix}____")
            ->orderBy('record_code', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d{4})$/', $latest->record_code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
            $nextNumber++;
        } while (self::where('record_code', $candidate)->exists());

        return $candidate;
    }

    public function getObservationElapsedMinutesAttribute(): int
    {
        return (int) $this->administered_at->diffInMinutes(Carbon::now());
    }

    public function getObservationRemainingMinutesAttribute(): int
    {
        $remaining = 30 - $this->observation_elapsed_minutes;
        return max(0, $remaining);
    }

    public function getIsObservationCompleteAttribute(): bool
    {
        return $this->observation_elapsed_minutes >= 30;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->observation_status) {
            self::STATUS_OBSERVING => '<span class="badge bg-warning text-dark"><i class="bi bi-stopwatch me-1"></i>Đang theo dõi 30p</span>',
            self::STATUS_CLEARED => '<span class="badge bg-success"><i class="bi bi-shield-check me-1"></i>Đã an toàn xuất viện</span>',
            self::STATUS_ADVERSE => '<span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>Phản ứng bất thường</span>',
            default => '<span class="badge bg-secondary">' . e($this->observation_status) . '</span>',
        };
    }
}
