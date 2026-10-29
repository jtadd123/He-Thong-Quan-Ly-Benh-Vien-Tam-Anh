<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyCodeRed extends Model
{
    use HasFactory;

    public const TYPE_STROKE = 'stroke';              // Đột quỵ não cấp (Code Stroke)
    public const TYPE_STEMI = 'stemi';                // Nhồi máu cơ tim cấp (Code STEMI)
    public const TYPE_TRAUMA = 'trauma';              // Đa chấn thương sốc mất máu (Code Red Trauma)
    public const TYPE_CARDIAC_ARREST = 'cardiac_arrest'; // Ngừng tuần hoàn
    public const TYPE_ANAPHYLAXIS = 'anaphylaxis';    // Sốc phản vệ độ 4

    public const STATUS_EN_ROUTE = 'en_route';              // Xe đang trên đường tới viện
    public const STATUS_ARRIVED = 'arrived';                // Đã cập sảnh cấp cứu
    public const STATUS_IN_INTERVENTION = 'in_intervention'; // Đang can thiệp khẩn cấp (Cathlab/OR)
    public const STATUS_RESOLVED = 'resolved';              // Đã xử trí ổn định
    public const STATUS_CANCELLED = 'cancelled';            // Báo động giả / Hủy

    protected $fillable = [
        'code_red_number',
        'patient_name',
        'gender',
        'estimated_age',
        'patient_profile_id',
        'ambulance_unit',
        'paramedic_in_charge',
        'paramedic_phone',
        'eta_minutes',
        'emergency_type',
        'clinical_condition',
        'in_transit_vitals',
        'activated_by_user_id',
        'activated_at',
        'arrived_at',
        'intervention_started_at',
        'resolved_at',
        'status',
        'outcome_notes',
    ];

    protected function casts(): array
    {
        return [
            'eta_minutes' => 'integer',
            'in_transit_vitals' => 'array',
            'activated_at' => 'datetime',
            'arrived_at' => 'datetime',
            'intervention_started_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public function activatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'activated_by_user_id');
    }

    public static function generateCodeRedNumber(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "CR-{$today}-";

        $latest = self::where('code_red_number', 'LIKE', "{$prefix}____")
            ->orderBy('code_red_number', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d{4})$/', $latest->code_red_number, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
            $nextNumber++;
        } while (self::where('code_red_number', $candidate)->exists());

        return $candidate;
    }

    /**
     * Thời gian vàng Door-to-Intervention (Door-to-Needle / Door-to-Balloon) tính theo phút
     */
    public function getDoorToInterventionMinutesAttribute(): ?int
    {
        if (!$this->arrived_at || !$this->intervention_started_at) {
            return null;
        }
        return (int) $this->arrived_at->diffInMinutes($this->intervention_started_at);
    }

    public function getEmergencyTypeNameAttribute(): string
    {
        return match ($this->emergency_type) {
            self::TYPE_STROKE => 'Code Stroke (Đột quỵ não cấp)',
            self::TYPE_STEMI => 'Code STEMI (Nhồi máu cơ tim cấp)',
            self::TYPE_TRAUMA => 'Code Red (Đa chấn thương mất máu)',
            self::TYPE_CARDIAC_ARREST => 'Code Blue (Ngừng tuần hoàn ngoại viện)',
            self::TYPE_ANAPHYLAXIS => 'Sốc phản vệ nguy kịch độ IV',
            default => strtoupper($this->emergency_type),
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_EN_ROUTE => '<span class="badge bg-danger p-2 animate-pulse text-white"><i class="bi bi-truck me-1"></i>Xe đang tới (ETA ' . $this->eta_minutes . 'p)</span>',
            self::STATUS_ARRIVED => '<span class="badge bg-warning text-dark p-2"><i class="bi bi-hospital me-1"></i>Đã tới cửa viện</span>',
            self::STATUS_IN_INTERVENTION => '<span class="badge bg-primary p-2 text-white"><i class="bi bi-heart-pulse-fill me-1"></i>Đang can thiệp</span>',
            self::STATUS_RESOLVED => '<span class="badge bg-success p-2 text-white"><i class="bi bi-check-circle-fill me-1"></i>Đã xử trí an toàn</span>',
            self::STATUS_CANCELLED => '<span class="badge bg-secondary p-2"><i class="bi bi-x-circle me-1"></i>Đã hủy</span>',
            default => '<span class="badge bg-light text-dark">' . e($this->status) . '</span>',
        };
    }
}
