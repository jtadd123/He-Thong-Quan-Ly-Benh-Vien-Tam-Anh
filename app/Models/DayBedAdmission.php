<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DayBedAdmission extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_DISCHARGED = 'discharged';
    public const STATUS_TRANSFERRED = 'transferred';

    protected $fillable = [
        'admission_code',
        'day_bed_id',
        'patient_profile_id',
        'appointment_id',
        'attending_doctor_id',
        'attending_nurse_name',
        'admission_reason',
        'admitted_at',
        'expected_discharge_at',
        'discharged_at',
        'status',
        'discharge_notes',
    ];

    protected function casts(): array
    {
        return [
            'admitted_at' => 'datetime',
            'expected_discharge_at' => 'datetime',
            'discharged_at' => 'datetime',
        ];
    }

    public function dayBed(): BelongsTo
    {
        return $this->belongsTo(DayBed::class);
    }

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_profile_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function attendingDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'attending_doctor_id');
    }

    public function ivInfusions(): HasMany
    {
        return $this->hasMany(IvInfusion::class);
    }

    public function getDurationHoursAttribute(): float
    {
        $endTime = $this->discharged_at ?? Carbon::now();
        return round($this->admitted_at->diffInMinutes($endTime) / 60, 1);
    }

    public function getIsOverstayAttribute(): bool
    {
        // Theo quy định Bộ Y Tế, lưu bệnh ngoại trú quá 4 giờ cần cảnh báo
        return $this->status === self::STATUS_ACTIVE && $this->duration_hours >= 4.0;
    }

    public static function generateAdmissionCode(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "ADM-{$today}-";

        $latest = self::where('admission_code', 'LIKE', "{$prefix}____")
            ->orderBy('admission_code', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d{4})$/', $latest->admission_code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
            $nextNumber++;
        } while (self::where('admission_code', $candidate)->exists());

        return $candidate;
    }
}
