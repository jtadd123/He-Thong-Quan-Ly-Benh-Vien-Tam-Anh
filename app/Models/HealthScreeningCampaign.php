<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthScreeningCampaign extends Model
{
    protected $table = 'health_screening_campaigns';

    protected $fillable = [
        'campaign_code',
        'campaign_name',
        'campaign_type',
        'target_population',
        'age_range',
        'start_date',
        'end_date',
        'venue',
        'target_participants',
        'actual_participants',
        'positive_findings',
        'referred_for_followup',
        'cost_per_participant',
        'total_cost',
        'screening_tests',
        'lead_doctor',
        'sponsor',
        'status',
        'outcomes_summary',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'screening_tests' => 'array',
        'cost_per_participant' => 'decimal:0',
        'total_cost' => 'decimal:0',
    ];

    public static function calculateDetectionRate(int $positiveFindings, int $actualParticipants): float
    {
        if ($actualParticipants <= 0) return 0.0;
        return round(($positiveFindings / $actualParticipants) * 100, 2);
    }

    public static function calculateCostEffectiveness(float $totalCost, int $positiveFindings): float
    {
        if ($positiveFindings <= 0) return 0.0;
        return round($totalCost / $positiveFindings, 0);
    }

    public static function getParticipationRate(int $actual, int $target): float
    {
        if ($target <= 0) return 0.0;
        return round(($actual / $target) * 100, 1);
    }
}
