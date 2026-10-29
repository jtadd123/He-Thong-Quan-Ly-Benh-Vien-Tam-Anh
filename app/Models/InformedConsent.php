<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InformedConsent extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_profile_id',
        'appointment_id',
        'doctor_id',
        'created_by_user_id',
        'consent_code',
        'procedure_name',
        'department_name',
        'signer_name',
        'signer_relationship',
        'signer_phone',
        'signer_id_card',
        'diagnosis_description',
        'risks_explained',
        'patient_undertaking',
        'signature_data',
        'signed_at',
        'ip_address',
        'status',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_profile_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function getSignerRelationshipTextAttribute(): string
    {
        return match ($this->signer_relationship) {
            'patient' => 'Bản thân người bệnh',
            'family' => 'Người nhà / Thân nhân',
            'guardian' => 'Người giám hộ hợp pháp',
            default => 'Người bệnh',
        };
    }

    public static function generateConsentCode(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "CAM-{$today}-";

        $latest = self::where('consent_code', 'LIKE', "{$prefix}____")
            ->orderBy('consent_code', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d{4})$/', $latest->consent_code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
            $nextNumber++;
        } while (self::where('consent_code', $candidate)->exists());

        return $candidate;
    }
}
