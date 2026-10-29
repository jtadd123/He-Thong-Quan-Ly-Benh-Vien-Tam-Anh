<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientSurvey extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'survey_code',
        'appointment_id',
        'patient_profile_id',
        'rating_reception',
        'rating_doctor',
        'rating_facilities',
        'rating_waiting_time',
        'overall_score',
        'nps_score',
        'feedback_text',
        'requires_followup',
        'followup_status',
        'followup_notes',
        'resolved_by_user_id',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'rating_reception' => 'integer',
            'rating_doctor' => 'integer',
            'rating_facilities' => 'integer',
            'rating_waiting_time' => 'integer',
            'overall_score' => 'decimal:2',
            'nps_score' => 'integer',
            'requires_followup' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }

    public static function generateSurveyCode(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "KS-{$today}-";

        $latest = self::where('survey_code', 'LIKE', "{$prefix}____")
            ->orderBy('survey_code', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d{4})$/', $latest->survey_code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
            $nextNumber++;
        } while (self::where('survey_code', $candidate)->exists());

        return $candidate;
    }

    /**
     * Phân loại NPS: Promoter (9-10), Passive (7-8), Detractor (0-6)
     */
    public function getNpsCategoryAttribute(): string
    {
        if ($this->nps_score >= 9) {
            return 'Promoter (Khách hàng tích cực)';
        } elseif ($this->nps_score >= 7) {
            return 'Passive (Trung lập)';
        } else {
            return 'Detractor (Không hài lòng)';
        }
    }
}
