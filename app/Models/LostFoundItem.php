<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LostFoundItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code',
        'item_name',
        'found_location',
        'found_at',
        'description',
        'status',
        'claimant_name',
        'claimant_phone',
        'claimant_id_card',
        'claimed_at',
        'received_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'found_at' => 'datetime',
            'claimed_at' => 'datetime',
        ];
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    public static function generateItemCode(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "LF-{$today}-";

        $latest = self::where('item_code', 'LIKE', "{$prefix}____")
            ->orderBy('item_code', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d{4})$/', $latest->item_code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
            $nextNumber++;
        } while (self::where('item_code', $candidate)->exists());

        return $candidate;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'claimed' => '<span class="badge bg-success"><i class="bi bi-check2-circle me-1"></i>Đã Nhận Lại</span>',
            'disposed' => '<span class="badge bg-secondary"><i class="bi bi-archive me-1"></i>Đã Lưu Kho/Xử Lý</span>',
            default => '<span class="badge bg-warning text-dark"><i class="bi bi-search me-1"></i>Chưa Có Người Nhận</span>',
        };
    }
}
