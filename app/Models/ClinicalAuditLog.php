<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ClinicalAuditLog extends Model
{
    use HasFactory;

    protected $table = 'clinical_audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'patient_profile_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }

    /**
     * Helper to quickly log clinical & administrative events.
     */
    public static function record(
        PatientProfile|int $patient,
        string $action,
        string $description,
        ?array $oldValues = null,
        ?array $newValues = null
    ): self {
        $patientId = $patient instanceof PatientProfile ? $patient->id : $patient;

        return self::create([
            'user_id' => Auth::id(),
            'patient_profile_id' => $patientId,
            'action' => $action,
            'description' => $description,
            'ip_address' => Request::ip() ?? '127.0.0.1',
            'user_agent' => substr(Request::userAgent() ?? 'System', 0, 500),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'created_at' => Carbon::now(),
        ]);
    }

    /**
     * Visual formatting for UI.
     */
    public function getActionBadgeAttribute(): array
    {
        return match ($this->action) {
            'view' => [
                'label' => 'Xem hồ sơ',
                'color' => 'info',
                'icon' => 'bi-eye',
            ],
            'create' => [
                'label' => 'Tạo mới hồ sơ',
                'color' => 'success',
                'icon' => 'bi-plus-circle',
            ],
            'update_profile' => [
                'label' => 'Cập nhật hành chính',
                'color' => 'primary',
                'icon' => 'bi-pencil-square',
            ],
            'update_safety' => [
                'label' => 'Cập nhật Nhóm máu/Dị ứng',
                'color' => 'danger',
                'icon' => 'bi-shield-exclamation',
            ],
            'record_vitals' => [
                'label' => 'Đo sinh hiệu',
                'color' => 'danger',
                'icon' => 'bi-heart-pulse',
            ],
            'checkin' => [
                'label' => 'Check-in tiếp nhận',
                'color' => 'warning',
                'icon' => 'bi-check2-circle',
            ],
            'dispense' => [
                'label' => 'Cấp phát thuốc',
                'color' => 'success',
                'icon' => 'bi-capsule-pill',
            ],
            'undo_dispense' => [
                'label' => 'Hủy phát thuốc',
                'color' => 'secondary',
                'icon' => 'bi-arrow-counterclockwise',
            ],
            'issue_certificate' => [
                'label' => 'Cấp giấy nghỉ BHXH',
                'color' => 'primary',
                'icon' => 'bi-file-earmark-medical',
            ],
            'print_card', 'print_ticket', 'print_wristband' => [
                'label' => 'In thẻ / Phiếu / Vòng tay',
                'color' => 'info',
                'icon' => 'bi-printer',
            ],
            default => [
                'label' => $this->action,
                'color' => 'secondary',
                'icon' => 'bi-clock-history',
            ],
        };
    }
}
