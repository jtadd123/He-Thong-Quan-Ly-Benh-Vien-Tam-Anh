<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalInterpreter extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_code',
        'patient_profile_id',
        'appointment_id',
        'language',
        'interpreter_name',
        'interpreter_phone',
        'status',
        'notes',
        'assigned_by_user_id',
    ];

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public static function generateRequestCode(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "ND-{$today}-";

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
            'completed' => '<span class="badge bg-success"><i class="bi bi-check2 me-1"></i>Hoàn Thành</span>',
            'in_progress' => '<span class="badge bg-primary animate-pulse"><i class="bi bi-translate me-1"></i>Đang Hỗ Trợ Dịch</span>',
            'cancelled' => '<span class="badge bg-secondary">Hủy Bỏ</span>',
            default => '<span class="badge bg-warning text-dark"><i class="bi bi-person-plus me-1"></i>Đã Điều Động</span>',
        };
    }
}
