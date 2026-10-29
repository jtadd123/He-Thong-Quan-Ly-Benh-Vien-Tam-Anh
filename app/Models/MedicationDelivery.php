<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicationDelivery extends Model
{
    use HasFactory;

    public const STATUS_PENDING_PACKAGING = 'pending_packaging';
    public const STATUS_PACKAGED = 'packaged';
    public const STATUS_IN_TRANSIT = 'in_transit';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_FAILED = 'failed';

    public const CARRIER_INTERNAL = 'tam_anh_express';
    public const CARRIER_VIETTEL = 'viettel_post';
    public const CARRIER_GRAB = 'grab_express';

    protected $fillable = [
        'tracking_code',
        'medical_record_id',
        'patient_profile_id',
        'recipient_name',
        'recipient_phone',
        'delivery_address',
        'carrier_name',
        'waybill_number',
        'shipping_fee',
        'requires_cold_chain',
        'status',
        'pharmacist_notes',
        'shipped_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'shipping_fee' => 'decimal:2',
            'requires_cold_chain' => 'boolean',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
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

    public static function generateTrackingCode(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "TRK-{$today}-";

        $latest = self::where('tracking_code', 'LIKE', "{$prefix}____")
            ->orderBy('tracking_code', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d{4})$/', $latest->tracking_code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
            $nextNumber++;
        } while (self::where('tracking_code', $candidate)->exists());

        return $candidate;
    }

    public function getCarrierNameFormattedAttribute(): string
    {
        return match ($this->carrier_name) {
            self::CARRIER_INTERNAL => 'Đội Giao Thuốc Nội Bộ Tâm Anh Express',
            self::CARRIER_VIETTEL => 'Viettel Post Dược Phẩm',
            self::CARRIER_GRAB => 'GrabExpress Hỏa Tốc',
            default => strtoupper($this->carrier_name),
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_PACKAGING => '<span class="badge bg-warning text-dark"><i class="bi bi-box-seam me-1"></i>Chờ dược sĩ đóng gói</span>',
            self::STATUS_PACKAGED => '<span class="badge bg-info text-dark"><i class="bi bi-check-circle me-1"></i>Đã đóng gói - Chờ bàn giao</span>',
            self::STATUS_IN_TRANSIT => '<span class="badge bg-primary text-white"><i class="bi bi-truck me-1"></i>Đang giao hàng</span>',
            self::STATUS_DELIVERED => '<span class="badge bg-success text-white"><i class="bi bi-house-check-fill me-1"></i>Giao thành công</span>',
            self::STATUS_FAILED => '<span class="badge bg-danger text-white"><i class="bi bi-x-circle me-1"></i>Giao thất bại</span>',
            default => '<span class="badge bg-secondary">' . e($this->status) . '</span>',
        };
    }
}
