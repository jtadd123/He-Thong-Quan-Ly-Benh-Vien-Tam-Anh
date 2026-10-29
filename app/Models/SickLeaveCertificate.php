<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SickLeaveCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'certificate_code',
        'medical_record_id',
        'patient_profile_id',
        'doctor_id',
        'issued_by_user_id',
        'employer_name',
        'diagnosis',
        'treatment_method',
        'leave_days',
        'from_date',
        'to_date',
        'father_mother_name',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'leave_days' => 'integer',
            'from_date' => 'date',
            'to_date' => 'date',
        ];
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by_user_id');
    }

    public static function generateCertificateCode(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "GCN-{$today}-";

        $latest = self::where('certificate_code', 'LIKE', "{$prefix}____")
            ->orderBy('certificate_code', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d{4})$/', $latest->certificate_code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
            $nextNumber++;
        } while (self::where('certificate_code', $candidate)->exists());

        return $candidate;
    }

    public function getLeaveDaysTextAttribute(): string
    {
        $days = $this->leave_days;
        $ones = ['', 'Một', 'Hai', 'Ba', 'Bốn', 'Năm', 'Sáu', 'Bảy', 'Tám', 'Chín'];
        
        if ($days <= 9) {
            $words = $ones[$days] ?? (string)$days;
        } elseif ($days == 10) {
            $words = 'Mười';
        } elseif ($days < 20) {
            $tail = ($days % 10 == 5) ? 'lăm' : (($days % 10 == 1) ? 'mốt' : mb_strtolower($ones[$days % 10]));
            $words = 'Mười ' . $tail;
        } elseif ($days == 20) {
            $words = 'Hai mươi';
        } elseif ($days < 30) {
            $tail = ($days % 10 == 5) ? 'lăm' : (($days % 10 == 1) ? 'mốt' : mb_strtolower($ones[$days % 10]));
            $words = 'Hai mươi ' . $tail;
        } elseif ($days == 30) {
            $words = 'Ba mươi';
        } else {
            $words = (string)$days;
        }

        return "{$days} ngày ({$words} ngày)";
    }
}
