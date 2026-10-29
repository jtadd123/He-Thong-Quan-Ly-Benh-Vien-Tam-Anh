<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AmbulanceMission extends Model
{
    use HasFactory;

    protected $fillable = [
        'mission_code',
        'ambulance_id',
        'patient_profile_id',
        'caller_name',
        'caller_phone',
        'pickup_address',
        'destination_address',
        'mission_type',
        'distance_km',
        'total_cost',
        'status',
        'doctor_in_charge',
        'nurse_in_charge',
        'dispatched_at',
        'completed_at',
        'dispatched_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'distance_km' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'dispatched_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function ambulance(): BelongsTo
    {
        return $this->belongsTo(Ambulance::class);
    }

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public function dispatchedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by_user_id');
    }

    public static function generateMissionCode(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "CC-{$today}-";

        $latest = self::where('mission_code', 'LIKE', "{$prefix}____")
            ->orderBy('mission_code', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d{4})$/', $latest->mission_code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
            $nextNumber++;
        } while (self::where('mission_code', $candidate)->exists());

        return $candidate;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'completed' => '<span class="badge bg-success"><i class="bi bi-check2-all me-1"></i>Đã Hoàn Thành</span>',
            'transporting' => '<span class="badge bg-primary animate-pulse"><i class="bi bi-arrow-right-circle me-1"></i>Đang Chuyển Viện</span>',
            'on_scene' => '<span class="badge bg-warning text-dark"><i class="bi bi-geo-alt-fill me-1"></i>Tại Hiện Trường</span>',
            'en_route' => '<span class="badge bg-info text-dark"><i class="bi bi-truck me-1"></i>Đang Di Chuyển</span>',
            'cancelled' => '<span class="badge bg-secondary">Hủy Bỏ</span>',
            default => '<span class="badge bg-danger animate-pulse"><i class="bi bi-bell-fill me-1"></i>Vừa Xuất Bến</span>',
        };
    }
}
