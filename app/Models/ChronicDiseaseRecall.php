<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChronicDiseaseRecall extends Model
{
    use HasFactory;

    protected $fillable = [
        'recall_code',
        'patient_profile_id',
        'chronic_condition',
        'last_visit_date',
        'prescription_days',
        'due_recall_date',
        'status',
        'reminder_sent_at',
        'reminder_channel',
        'appointment_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'last_visit_date' => 'date',
            'due_recall_date' => 'date',
            'prescription_days' => 'integer',
            'reminder_sent_at' => 'datetime',
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

    public static function generateRecallCode(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "TK-{$today}-";

        $latest = self::where('recall_code', 'LIKE', "{$prefix}____")
            ->orderBy('recall_code', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d{4})$/', $latest->recall_code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
            $nextNumber++;
        } while (self::where('recall_code', $candidate)->exists());

        return $candidate;
    }

    public function getDaysRemainingAttribute(): int
    {
        return (int) Carbon::today()->diffInDays($this->due_recall_date, false);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'attended' => '<span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Đã Tái Khám Đúng Hẹn</span>',
            'reminder_sent' => '<span class="badge bg-info text-dark"><i class="bi bi-send-check me-1"></i>Đã Gửi Tin Nhắc</span>',
            'missed' => '<span class="badge bg-danger"><i class="bi bi-exclamation-triangle me-1"></i>Quá Hạn Tái Khám</span>',
            default => '<span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Chờ Đến Ngày Nhắc</span>',
        };
    }
}
