<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BloodUnit extends Model
{
    use HasFactory;

    public const STATUS_IN_STOCK = 'in_stock';
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_TRANSFUSING = 'transfusing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_DISCARDED = 'discarded';

    public const PRODUCT_PRBC = 'prbc'; // Hồng cầu lắng
    public const PRODUCT_FFP = 'ffp';   // Huyết tương tươi
    public const PRODUCT_PLATELET = 'platelet'; // Tiểu cầu
    public const PRODUCT_CRYO = 'cryo'; // Tủa lạnh

    protected $fillable = [
        'unit_code',
        'blood_type_abo',
        'blood_type_rh',
        'product_type',
        'volume_ml',
        'collection_date',
        'expiry_date',
        'status',
        'storage_location',
    ];

    protected function casts(): array
    {
        return [
            'volume_ml' => 'integer',
            'collection_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function transfusions(): HasMany
    {
        return $this->hasMany(BloodTransfusion::class);
    }

    public function getProductNameAttribute(): string
    {
        return match ($this->product_type) {
            self::PRODUCT_PRBC => 'Khối Hồng Cầu Lắng (PRBC)',
            self::PRODUCT_FFP => 'Huyết Tương Tươi Đông Lạnh (FFP)',
            self::PRODUCT_PLATELET => 'Khối Tiểu Cầu Đậm Đặc',
            self::PRODUCT_CRYO => 'Tủa Lạnh Yếu Tố VIII (Cryo)',
            default => 'Chế phẩm máu',
        };
    }

    public function getFullGroupAttribute(): string
    {
        $rhSymbol = $this->blood_type_rh === 'positive' ? '(+)' : '(-)';
        return "{$this->blood_type_abo} Rh{$rhSymbol}";
    }

    public static function generateUnitCode(): string
    {
        $today = Carbon::now()->format('Ym');
        do {
            $code = "BLD-{$today}-" . strtoupper(substr(uniqid(), -5));
        } while (self::where('unit_code', $code)->exists());

        return $code;
    }
}
