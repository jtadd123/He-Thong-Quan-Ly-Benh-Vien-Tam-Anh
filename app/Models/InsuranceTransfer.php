<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_code',
        'patient_profile_id',
        'appointment_id',
        'issuing_hospital_code',
        'issuing_hospital_name',
        'transfer_type',
        'icd10_code',
        'icd10_diagnosis',
        'clinical_summary',
        'valid_from',
        'valid_to',
        'bhyt_reimbursement_rate',
        'status',
        'registered_by_user_id',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'valid_from' => 'date',
            'valid_to' => 'date',
            'bhyt_reimbursement_rate' => 'integer',
        ];
    }

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by_user_id');
    }

    public static function generateTransferCode(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "CT-{$today}-";

        $latest = self::where('transfer_code', 'LIKE', "{$prefix}____")
            ->orderBy('transfer_code', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d{4})$/', $latest->transfer_code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
            $nextNumber++;
        } while (self::where('transfer_code', $candidate)->exists());

        return $candidate;
    }

    public function getIsValidAttribute(): bool
    {
        $today = Carbon::today();
        return $this->status === 'valid' && $today->between($this->valid_from, $this->valid_to);
    }

    public function getStatusBadgeAttribute(): string
    {
        if ($this->status === 'rejected') {
            return '<span class="badge bg-danger">Bị từ chối</span>';
        }
        if ($this->status === 'used') {
            return '<span class="badge bg-secondary">Đã sử dụng</span>';
        }
        if (!$this->is_valid) {
            return '<span class="badge bg-warning text-dark">Hết hiệu lực</span>';
        }
        return '<span class="badge bg-success">Còn hiệu lực (' . $this->bhyt_reimbursement_rate . '%)</span>';
    }
}
