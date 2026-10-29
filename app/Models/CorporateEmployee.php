<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorporateEmployee extends Model
{
    use HasFactory;

    protected $fillable = [
        'corporate_contract_id',
        'patient_profile_id',
        'employee_code',
        'full_name',
        'dob',
        'gender',
        'phone',
        'department',
        'screening_status',
        'health_classification',
        'conclusion',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(CorporateContract::class, 'corporate_contract_id');
    }

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->screening_status) {
            'completed' => '<span class="badge bg-success"><i class="bi bi-check2-circle me-1"></i>Đã Khám Xong (' . ($this->health_classification ?? 'Chưa phân loại') . ')</span>',
            'in_progress' => '<span class="badge bg-primary animate-pulse"><i class="bi bi-person-walking me-1"></i>Đang Khám</span>',
            default => '<span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Chờ Khám</span>',
        };
    }
}
