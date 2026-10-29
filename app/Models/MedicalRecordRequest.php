<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalRecordRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_code',
        'patient_profile_id',
        'medical_record_id',
        'requester_type',
        'requester_name',
        'requester_phone',
        'requester_identity_card',
        'relationship_to_patient',
        'purpose',
        'copies_count',
        'status',
        'approved_by_user_id',
        'rejection_reason',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'copies_count' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public static function generateRequestCode(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "SL-{$today}-";

        $latest = self::where('request_code', 'LIKE', "{$prefix}____")
            ->orderBy('request_code', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d{4})$/', $latest->request_code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
            $nextNumber++;
        } while (self::where('request_code', $candidate)->exists());

        return $candidate;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'approved' => '<span class="badge bg-info text-dark"><i class="bi bi-file-earmark-check me-1"></i>Đã Phê Duyệt</span>',
            'printed' => '<span class="badge bg-success"><i class="bi bi-printer me-1"></i>Đã Xuất Bản Sao</span>',
            'rejected' => '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Từ Chối</span>',
            'reviewing' => '<span class="badge bg-primary"><i class="bi bi-search me-1"></i>KHTH Đang Thẩm Tra</span>',
            default => '<span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Chờ Tiếp Nhận</span>',
        };
    }
}
