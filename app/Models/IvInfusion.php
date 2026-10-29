<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IvInfusion extends Model
{
    use HasFactory;

    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_PAUSED = 'paused';

    protected $fillable = [
        'day_bed_admission_id',
        'fluid_name',
        'volume_ml',
        'drip_rate_drops_per_min',
        'started_at',
        'estimated_finish_at',
        'status',
        'nurse_name',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'volume_ml' => 'integer',
            'drip_rate_drops_per_min' => 'integer',
            'started_at' => 'datetime',
            'estimated_finish_at' => 'datetime',
        ];
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(DayBedAdmission::class, 'day_bed_admission_id');
    }

    /**
     * Tính thời gian truyền (phút):
     * Công thức y khoa chuẩn: 1ml dung dịch thông thường = 20 giọt (drops).
     * Tổng số giọt = Volume (ml) * 20.
     * Thời gian (phút) = Tổng số giọt / Tốc độ (giọt/phút).
     */
    public static function calculateDurationMinutes(int $volumeMl, int $dropsPerMin): int
    {
        if ($dropsPerMin <= 0) {
            $dropsPerMin = 30;
        }
        $totalDrops = $volumeMl * 20;
        return (int) round($totalDrops / $dropsPerMin);
    }

    public function getProgressPercentAttribute(): int
    {
        if ($this->status === self::STATUS_COMPLETED) {
            return 100;
        }
        if (!$this->estimated_finish_at || Carbon::now()->lt($this->started_at)) {
            return 0;
        }
        $totalMinutes = $this->started_at->diffInMinutes($this->estimated_finish_at);
        if ($totalMinutes <= 0) {
            return 100;
        }
        $elapsedMinutes = $this->started_at->diffInMinutes(Carbon::now());
        return min(100, (int) round(($elapsedMinutes / $totalMinutes) * 100));
    }
}
