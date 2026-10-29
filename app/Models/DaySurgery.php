<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DaySurgery extends Model
{
    use HasFactory;

    protected $fillable = [
        'surgery_code',
        'patient_profile_id',
        'appointment_id',
        'procedure_name',
        'surgeon_doctor_id',
        'scheduled_at',
        'operating_room',
        'fasting_hours',
        'checklist_passed',
        'status',
        'pre_op_notes',
        'checked_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'fasting_hours' => 'integer',
            'checklist_passed' => 'boolean',
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

    public function surgeon(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'surgeon_doctor_id');
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by_user_id');
    }

    public static function generateSurgeryCode(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "PT-{$today}-";

        $latest = self::where('surgery_code', 'LIKE', "{$prefix}____")
            ->orderBy('surgery_code', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d{4})$/', $latest->surgery_code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
            $nextNumber++;
        } while (self::where('surgery_code', $candidate)->exists());

        return $candidate;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pre_op_ready' => '<span class="badge bg-info text-dark"><i class="bi bi-clipboard2-pulse me-1"></i>Sẵn Sàng Vào Mổ</span>',
            'in_surgery' => '<span class="badge bg-danger animate-pulse"><i class="bi bi-activity me-1"></i>Đang Phẫu Thuật</span>',
            'recovery' => '<span class="badge bg-warning text-dark"><i class="bi bi-heart me-1"></i>Hồi Tỉnh Sau Mổ</span>',
            'discharged' => '<span class="badge bg-success"><i class="bi bi-house-check me-1"></i>Đã Xuất Viện Về Nhà</span>',
            'cancelled' => '<span class="badge bg-secondary">Hủy Phẫu Thuật</span>',
            default => '<span class="badge bg-primary"><i class="bi bi-calendar-event me-1"></i>Đã Đặt Lịch</span>',
        };
    }
}
