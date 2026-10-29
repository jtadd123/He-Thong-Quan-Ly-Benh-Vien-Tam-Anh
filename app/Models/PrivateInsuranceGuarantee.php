<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivateInsuranceGuarantee extends Model
{
    use HasFactory;

    protected $fillable = [
        'gop_number',
        'patient_profile_id',
        'appointment_id',
        'tpa_company',
        'card_number',
        'coverage_limit',
        'approved_amount',
        'patient_copay_amount',
        'guarantee_status',
        'vip_level',
        'contact_officer',
        'contact_phone',
        'remarks',
        'handled_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'coverage_limit' => 'decimal:2',
            'approved_amount' => 'decimal:2',
            'patient_copay_amount' => 'decimal:2',
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

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by_user_id');
    }

    public static function generateGopNumber(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "GOP-{$today}-";

        $latest = self::where('gop_number', 'LIKE', "{$prefix}____")
            ->orderBy('gop_number', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d{4})$/', $latest->gop_number, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
            $nextNumber++;
        } while (self::where('gop_number', $candidate)->exists());

        return $candidate;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->guarantee_status) {
            'approved' => '<span class="badge bg-success"><i class="bi bi-shield-check me-1"></i>Đã Bảo Lãnh</span>',
            'settled' => '<span class="badge bg-primary"><i class="bi bi-check-all me-1"></i>Đã Quyết Toán</span>',
            'rejected' => '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Từ Chối</span>',
            default => '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Đang Chờ GOP</span>',
        };
    }

    public function getVipBadgeAttribute(): string
    {
        return match ($this->vip_level) {
            'diamond' => '<span class="badge bg-dark text-info"><i class="bi bi-gem me-1"></i>KIM CƯƠNG VIP</span>',
            'platinum' => '<span class="badge bg-info text-dark"><i class="bi bi-award me-1"></i>BẠCH KIM</span>',
            'gold' => '<span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>VÀNG</span>',
            'silver' => '<span class="badge bg-secondary"><i class="bi bi-star me-1"></i>BẠC</span>',
            default => '<span class="badge bg-light text-dark">TIÊU CHUẨN</span>',
        };
    }
}
