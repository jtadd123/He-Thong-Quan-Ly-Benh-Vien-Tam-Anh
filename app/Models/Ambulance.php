<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ambulance extends Model
{
    use HasFactory;

    protected $fillable = [
        'license_plate',
        'ambulance_type',
        'driver_name',
        'driver_phone',
        'status',
        'current_location',
    ];

    public function missions(): HasMany
    {
        return $this->hasMany(AmbulanceMission::class)->orderBy('created_at', 'desc');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'dispatched' => '<span class="badge bg-danger animate-pulse"><i class="bi bi-truck me-1"></i>Đang Điều Động</span>',
            'maintenance' => '<span class="badge bg-secondary"><i class="bi bi-wrench me-1"></i>Bảo Dưỡng</span>',
            default => '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Sẵn Sàng Xuất Bến</span>',
        };
    }
}
