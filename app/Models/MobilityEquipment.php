<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MobilityEquipment extends Model
{
    use HasFactory;

    protected $table = 'mobility_equipments';

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_IN_USE = 'in_use';
    public const STATUS_DISINFECTING = 'disinfecting';
    public const STATUS_MAINTENANCE = 'maintenance';

    public const TYPE_WHEELCHAIR = 'wheelchair';
    public const TYPE_ELECTRIC_WHEELCHAIR = 'electric_wheelchair';
    public const TYPE_STRETCHER = 'stretcher';
    public const TYPE_WALKER = 'walker';
    public const TYPE_OXYGEN_TANK = 'oxygen_tank';

    protected $fillable = [
        'device_code',
        'name',
        'device_type',
        'current_location',
        'status',
        'last_disinfected_at',
        'last_disinfected_by_user_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'last_disinfected_at' => 'datetime',
        ];
    }

    public function lastDisinfectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_disinfected_by_user_id');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(EquipmentLoan::class);
    }

    public function activeLoan(): ?EquipmentLoan
    {
        return $this->loans()->where('status', EquipmentLoan::STATUS_BORROWED)->latest()->first();
    }

    public function getTypeNameAttribute(): string
    {
        return match ($this->device_type) {
            self::TYPE_WHEELCHAIR => 'Xe lăn cơ',
            self::TYPE_ELECTRIC_WHEELCHAIR => 'Xe lăn điện',
            self::TYPE_STRETCHER => 'Cáng đẩy cấp cứu',
            self::TYPE_WALKER => 'Khung tập đi',
            self::TYPE_OXYGEN_TANK => 'Bình Oxy mini 2L',
            default => 'Thiết bị hỗ trợ',
        };
    }
}
