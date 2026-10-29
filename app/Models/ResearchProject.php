<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResearchProject extends Model
{
    protected $table = 'research_projects';

    protected $fillable = [
        'project_code',
        'title',
        'principal_investigator',
        'department',
        'research_type',
        'phase',
        'irb_approval_number',
        'irb_approval_date',
        'start_date',
        'end_date',
        'target_enrollment',
        'actual_enrollment',
        'budget_approved',
        'budget_spent',
        'funding_source',
        'grant_number',
        'status',
        'clinicaltrials_gov_id',
        'description',
        'co_investigators',
    ];

    protected $casts = [
        'irb_approval_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'co_investigators' => 'array',
        'budget_approved' => 'decimal:0',
        'budget_spent' => 'decimal:0',
    ];

    public static function getEnrollmentRate(int $actual, int $target): float
    {
        if ($target <= 0) return 0.0;
        return round(($actual / $target) * 100, 1);
    }

    public static function getBudgetUtilization(float $spent, float $approved): float
    {
        if ($approved <= 0) return 0.0;
        return round(($spent / $approved) * 100, 1);
    }

    public static function isOnTrack(string $status, int $actual, int $target, float $timeElapsedPct): bool
    {
        if (!in_array($status, ['recruiting', 'data_collection'])) return true;
        $expectedEnrollment = $target * ($timeElapsedPct / 100);
        return $actual >= ($expectedEnrollment * 0.8);
    }
}
