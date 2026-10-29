<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PorterTransportRequest extends Model
{
    protected $table = 'porter_transport_requests';

    protected $fillable = [
        'patient_id',
        'from_location',
        'to_location',
        'transport_mode',
        'priority',
        'requested_by',
        'porter_name',
        'requested_at',
        'assigned_at',
        'picked_up_at',
        'delivered_at',
        'wait_time_minutes',
        'transport_duration_minutes',
        'status',
        'oxygen_required',
        'iv_line_present',
        'monitoring_required',
        'special_instructions',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'assigned_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'oxygen_required' => 'boolean',
        'iv_line_present' => 'boolean',
        'monitoring_required' => 'boolean',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    public static function calculateWaitTime(?string $requested, ?string $assigned): ?int
    {
        if (!$requested || !$assigned) return null;
        return (int)Carbon::parse($requested)->diffInMinutes(Carbon::parse($assigned));
    }

    public static function getSlaThreshold(string $priority): int
    {
        return match($priority) {
            'emergency' => 5,
            'urgent' => 15,
            default => 30
        };
    }

    public static function isSlaBreach(int $waitMinutes, string $priority): bool
    {
        return $waitMinutes > self::getSlaThreshold($priority);
    }
}
