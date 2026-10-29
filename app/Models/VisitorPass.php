<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorPass extends Model
{
    use HasFactory;

    protected $fillable = [
        'pass_code',
        'patient_profile_id',
        'visitor_name',
        'visitor_phone',
        'visitor_id_card',
        'relationship',
        'target_department',
        'valid_from',
        'valid_to',
        'infection_screening_passed',
        'status',
        'checked_out_at',
        'issued_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'valid_from' => 'datetime',
            'valid_to' => 'datetime',
            'checked_out_at' => 'datetime',
            'infection_screening_passed' => 'boolean',
        ];
    }

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by_user_id');
    }

    public static function generatePassCode(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "VP-{$today}-";

        $latest = self::where('pass_code', 'LIKE', "{$prefix}____")
            ->orderBy('pass_code', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d{4})$/', $latest->pass_code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
            $nextNumber++;
        } while (self::where('pass_code', $candidate)->exists());

        return $candidate;
    }

    public function getIsExpiredAttribute(): bool
    {
        return Carbon::now()->isAfter($this->valid_to) || $this->status === 'checked_out';
    }

    public function getStatusBadgeAttribute(): string
    {
        if ($this->status === 'checked_out') {
            return '<span class="badge bg-secondary"><i class="bi bi-box-arrow-right me-1"></i>Đã Check-out</span>';
        }
        if (Carbon::now()->isAfter($this->valid_to)) {
            return '<span class="badge bg-danger"><i class="bi bi-clock-history me-1"></i>Quá Giờ Thăm</span>';
        }
        return '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Hợp Lệ (Đang Thăm)</span>';
    }
}
