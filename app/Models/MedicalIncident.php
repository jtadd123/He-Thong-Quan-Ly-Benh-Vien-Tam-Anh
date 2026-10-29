<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalIncident extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_code',
        'reporter_type',
        'reporter_user_id',
        'location',
        'occurred_at',
        'severity_level',
        'incident_category',
        'description',
        'immediate_action',
        'root_cause_analysis',
        'status',
        'reviewed_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public static function generateIncidentCode(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "SC-{$today}-";

        $latest = self::where('incident_code', 'LIKE', "{$prefix}____")
            ->orderBy('incident_code', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d{4})$/', $latest->incident_code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
            $nextNumber++;
        } while (self::where('incident_code', $candidate)->exists());

        return $candidate;
    }

    public function getSeverityBadgeAttribute(): string
    {
        return match ($this->severity_level) {
            'NC0' => '<span class="badge bg-info text-dark"><i class="bi bi-shield-exclamation me-1"></i>NC0 (Suýt Xảy Ra)</span>',
            'NC1' => '<span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle me-1"></i>NC1 (Mức Nhẹ)</span>',
            'NC2' => '<span class="badge bg-danger"><i class="bi bi-exclamation-triangle me-1"></i>NC2 (Mức Trung Bình)</span>',
            'NC3' => '<span class="badge bg-dark text-danger"><i class="bi bi-radioactive me-1"></i>NC3 (Nghiêm Trọng)</span>',
            default => '<span class="badge bg-secondary">' . $this->severity_level . '</span>',
        };
    }
}
