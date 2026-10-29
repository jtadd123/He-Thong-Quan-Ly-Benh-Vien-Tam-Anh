<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medication extends Model
{
    use HasFactory;

    protected $fillable = [
        'medication_code',
        'name',
        'active_ingredient',
        'dosage_form',
        'unit',
        'stock_quantity',
        'min_stock_alert',
        'unit_price',
        'expiry_date',
        'batch_number',
        'dosage_default',
        'frequency_default',
        'duration_default',
        'instructions_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'stock_quantity' => 'integer',
            'min_stock_alert' => 'integer',
            'unit_price' => 'float',
            'expiry_date' => 'date',
            'duration_default' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity > 0 && $this->stock_quantity <= $this->min_stock_alert;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    public function isExpiringSoon(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isFuture() && $this->expiry_date->diffInDays(Carbon::today()) <= 90;
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Calculate remaining days until expiration.
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }

        return (int) Carbon::today()->diffInDays($this->expiry_date, false);
    }

    /**
     * FEFO (First-Expired, First-Out) Clinical Drug Classification according to GPP & JCI standards.
     *
     * @return array{status: string, label: string, color: string, is_dispensable: bool, action: string, days_left: int|null}
     */
    public function getFefoStatusAttribute(): array
    {
        if (!$this->expiry_date) {
            return [
                'status' => 'unknown',
                'label' => 'Chưa rõ hạn dùng',
                'color' => 'secondary',
                'is_dispensable' => true,
                'action' => 'Kiểm tra bao bì thực tế',
                'days_left' => null,
            ];
        }

        $days = $this->days_until_expiry;

        if ($days < 0) {
            return [
                'status' => 'expired',
                'label' => 'ĐÃ HẾT HẠN',
                'color' => 'danger',
                'is_dispensable' => false,
                'action' => 'KHÓA XUẤT CẤP PHÁT (TIÊU HỦY/TRẢ NCC)',
                'days_left' => $days,
            ];
        }

        if ($days <= 30) {
            return [
                'status' => 'critical',
                'label' => 'CẬN HẠN NGUY HIỂM (<=30 ngày)',
                'color' => 'danger',
                'is_dispensable' => false,
                'action' => 'CẤM CẤP PHÁT CHO BỆNH NHÂN (FEFO)',
                'days_left' => $days,
            ];
        }

        if ($days <= 90) {
            return [
                'status' => 'warning',
                'label' => 'CẬN HẠN (<=90 ngày)',
                'color' => 'warning',
                'is_dispensable' => true,
                'action' => 'ƯU TIÊN XUẤT TRƯỚC (FEFO)',
                'days_left' => $days,
            ];
        }

        return [
            'status' => 'good',
            'label' => 'HẠN DÙNG AN TOÀN',
            'color' => 'success',
            'is_dispensable' => true,
            'action' => 'ĐỦ ĐIỀU KIỆN LƯU HÀNH',
            'days_left' => $days,
        ];
    }

    /**
     * Render FEFO Badge for tables and UI cards.
     */
    public function getFefoBadgeAttribute(): string
    {
        $fefo = $this->fefo_status;
        $expDateStr = $this->expiry_date ? $this->expiry_date->format('d/m/Y') : '--';

        if ($fefo['status'] === 'expired') {
            return '<span class="badge bg-danger text-white border border-danger"><i class="bi bi-x-octagon-fill me-1"></i>HẾT HẠN (' . $expDateStr . ')</span>';
        }

        if ($fefo['status'] === 'critical') {
            return '<span class="badge bg-danger text-white border border-danger animate-pulse"><i class="bi bi-exclamation-triangle-fill me-1"></i>Cận hạn ' . $fefo['days_left'] . ' ngày (' . $expDateStr . ')</span>';
        }

        if ($fefo['status'] === 'warning') {
            return '<span class="badge bg-warning text-dark border border-warning"><i class="bi bi-clock-history me-1"></i>FEFO: Còn ' . $fefo['days_left'] . ' ngày (' . $expDateStr . ')</span>';
        }

        return '<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-shield-check me-1"></i>' . $expDateStr . ' (Còn ' . $fefo['days_left'] . ' ngày)</span>';
    }

    /**
     * Scope query to order by FEFO (First-Expired, First-Out).
     */
    public function scopeFefoOrder($query)
    {
        return $query->orderByRaw('expiry_date IS NULL, expiry_date ASC');
    }

    /**
     * Scope query for medications expiring soon (within $days).
     */
    public function scopeExpiringSoon($query, int $days = 90)
    {
        $today = Carbon::today();
        return $query->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', $today)
            ->whereDate('expiry_date', '<=', $today->copy()->addDays($days));
    }

    /**
     * Scope query for expired medications.
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', Carbon::today());
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->unit_price, 0, ',', '.') . ' đ';
    }

    public function getStockBadgeAttribute(): string
    {
        if ($this->isOutOfStock()) {
            return '<span class="badge bg-danger text-white border border-danger">Hết hàng (0 ' . $this->unit . ')</span>';
        }

        if ($this->isLowStock()) {
            return '<span class="badge bg-warning text-dark border border-warning">Sắp hết (' . $this->stock_quantity . ' ' . $this->unit . ')</span>';
        }

        return '<span class="badge bg-success-subtle text-success border border-success-subtle">Còn ' . $this->stock_quantity . ' ' . $this->unit . '</span>';
    }

    public static function generateMedicationCode(): string
    {
        $prefix = 'MED-';
        $latest = self::where('medication_code', 'LIKE', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) preg_replace('/[^0-9]/', '', substr($latest->medication_code, 4));
            $nextNumber = str_pad((string)($lastNumber + 1), 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return "{$prefix}{$nextNumber}";
    }
}
