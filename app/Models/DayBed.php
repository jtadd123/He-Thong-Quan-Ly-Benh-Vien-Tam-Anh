<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DayBed extends Model
{
    use HasFactory;

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_OCCUPIED = 'occupied';
    public const STATUS_CLEANING = 'cleaning';
    public const STATUS_MAINTENANCE = 'maintenance';

    public const TYPE_GENERAL = 'general';
    public const TYPE_INFUSION = 'infusion';
    public const TYPE_RECOVERY = 'recovery';
    public const TYPE_OBSERVATION = 'observation';

    protected $fillable = [
        'room_name',
        'bed_code',
        'bed_type',
        'status',
        'hourly_rate',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'hourly_rate' => 'decimal:2',
        ];
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(DayBedAdmission::class);
    }

    public function currentAdmission(): HasOne
    {
        return $this->hasOne(DayBedAdmission::class)->where('status', DayBedAdmission::STATUS_ACTIVE)->latestOfMany();
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_AVAILABLE => '<span class="badge bg-success-subtle text-success border border-success"><i class="bi bi-check-circle me-1"></i>Sẵn sàng tiếp nhận</span>',
            self::STATUS_OCCUPIED => '<span class="badge bg-danger-subtle text-danger border border-danger"><i class="bi bi-person-fill me-1"></i>Đang có bệnh nhân</span>',
            self::STATUS_CLEANING => '<span class="badge bg-warning-subtle text-warning border border-warning"><i class="bi bi-brush me-1"></i>Đang khử khuẩn / Dọn vệ sinh</span>',
            self::STATUS_MAINTENANCE => '<span class="badge bg-secondary-subtle text-secondary border border-secondary"><i class="bi bi-tools me-1"></i>Đang bảo trì</span>',
            default => '<span class="badge bg-light text-dark">' . e($this->status) . '</span>',
        };
    }

    public function getBedTypeLabelAttribute(): string
    {
        return match ($this->bed_type) {
            self::TYPE_INFUSION => 'Giường truyền dịch',
            self::TYPE_RECOVERY => 'Giường hồi tỉnh sau nội soi',
            self::TYPE_OBSERVATION => 'Giường theo dõi cấp cứu',
            default => 'Giường lưu bệnh thông thường',
        };
    }
}
