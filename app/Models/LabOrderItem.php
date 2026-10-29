<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabOrderItem extends Model
{
    use HasFactory;

    public const RESULT_NORMAL = 'normal';
    public const RESULT_HIGH = 'high';
    public const RESULT_LOW = 'low';
    public const RESULT_CRITICAL_PANIC = 'critical_panic';

    protected $fillable = [
        'lab_order_id',
        'lab_service_id',
        'result_value',
        'result_number',
        'result_status',
        'technician_notes',
        'dicom_image_url',
        'is_panic_alert',
        'panic_alerted_at',
        'technician_user_id',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'result_number' => 'float',
            'is_panic_alert' => 'boolean',
            'panic_alerted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function labOrder(): BelongsTo
    {
        return $this->belongsTo(LabOrder::class);
    }

    public function labService(): BelongsTo
    {
        return $this->belongsTo(LabService::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_user_id');
    }
}
