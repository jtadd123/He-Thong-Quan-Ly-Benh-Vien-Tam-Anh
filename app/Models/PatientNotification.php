<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientNotification extends Model
{
    use HasFactory;

    public const CHANNEL_SMS = 'sms';
    public const CHANNEL_ZALO_ZNS = 'zalo_zns';
    public const CHANNEL_IN_APP = 'in_app';

    public const TYPE_QUEUE_ALERT = 'queue_alert';
    public const TYPE_PRESCRIPTION_READY = 'prescription_ready';
    public const TYPE_DOCTOR_CALLED = 'doctor_called';
    public const TYPE_DISCHARGE_SUMMARY = 'discharge_summary';

    public const STATUS_SENT = 'sent';
    public const STATUS_PENDING = 'pending';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'appointment_id',
        'patient_profile_id',
        'channel',
        'notification_type',
        'recipient_phone',
        'title',
        'message_content',
        'status',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_profile_id');
    }

    public function getChannelBadgeAttribute(): string
    {
        return match ($this->channel) {
            self::CHANNEL_ZALO_ZNS => '<span class="badge bg-primary"><i class="bi bi-chat-dots-fill me-1"></i>Zalo ZNS</span>',
            self::CHANNEL_SMS => '<span class="badge bg-info text-dark"><i class="bi bi-chat-square-text-fill me-1"></i>SMS Brandname</span>',
            default => '<span class="badge bg-secondary"><i class="bi bi-bell-fill me-1"></i>In-App</span>',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SENT => '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Đã gửi thành công</span>',
            self::STATUS_PENDING => '<span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Đang chờ gửi</span>',
            self::STATUS_FAILED => '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Gửi thất bại</span>',
            default => '<span class="badge bg-secondary">' . e($this->status) . '</span>',
        };
    }
}
