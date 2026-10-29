<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrBlockSchedule extends Model
{
    protected $table = 'or_block_schedules';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'or_room',
        'surgery_date',
        'scheduled_start_time',
        'scheduled_end_time',
        'actual_start_time',
        'actual_end_time',
        'surgery_name',
        'surgery_category',
        'anesthesia_type',
        'surgical_team',
        'anesthesia_team',
        'scrub_nurse',
        'estimated_duration_minutes',
        'actual_duration_minutes',
        'turnover_time_minutes',
        'block_status',
        'cancellation_reason',
        'equipment_needed',
        'implants_needed',
        'case_complexity',
        'pre_op_notes',
        'post_op_notes',
    ];

    protected $casts = [
        'surgery_date' => 'date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public static function calculateOrUtilization(int $scheduledMinutes, int $availableMinutes): float
    {
        if ($availableMinutes <= 0) return 0.0;
        return round(($scheduledMinutes / $availableMinutes) * 100, 1);
    }

    public static function calculateOnTimeStartRate(array $cases): float
    {
        if (empty($cases)) return 0.0;
        $onTime = count(array_filter($cases, fn($c) => !empty($c['actual_start_time']) && $c['actual_start_time'] <= $c['scheduled_start_time']));
        return round(($onTime / count($cases)) * 100, 1);
    }

    public static function prioritySort(array $cases): array
    {
        $priority = ['emergency' => 0, 'urgent' => 1, 'elective' => 2];
        usort($cases, fn($a, $b) => ($priority[$a['surgery_category']] ?? 2) <=> ($priority[$b['surgery_category']] ?? 2));
        return $cases;
    }
}
