<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialWorkCase extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_code',
        'patient_profile_id',
        'difficulty_type',
        'proposed_discount_amount',
        'approved_discount_amount',
        'funding_source',
        'situation_assessment',
        'status',
        'officer_user_id',
        'approved_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'proposed_discount_amount' => 'decimal:2',
            'approved_discount_amount' => 'decimal:2',
        ];
    }

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public static function generateCaseCode(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "CTXH-{$today}-";

        $latest = self::where('case_code', 'LIKE', "{$prefix}____")
            ->orderBy('case_code', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d{4})$/', $latest->case_code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
            $nextNumber++;
        } while (self::where('case_code', $candidate)->exists());

        return $candidate;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'approved' => '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Đã Duyệt Hỗ Trợ</span>',
            'disbursed' => '<span class="badge bg-primary"><i class="bi bi-cash-stack me-1"></i>Đã Giải Ngân</span>',
            'rejected' => '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Không Đạt Điều Kiện</span>',
            default => '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Đang Xét Duyệt</span>',
        };
    }
}
