<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyPointsLog extends Model
{
    protected $table = 'loyalty_points_logs';

    protected $fillable = [
        'patient_id',
        'transaction_type',
        'points_change',
        'points_balance',
        'earn_reason',
        'redeem_for',
        'monetary_value',
        'tier_level',
        'expiry_date',
        'reference_number',
        'notes',
    ];

    protected $casts = [
        'monetary_value' => 'decimal:0',
        'expiry_date' => 'date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    public static function calculateTierLevel(int $totalPoints): string
    {
        if ($totalPoints >= 50000) return 'Platinum';
        if ($totalPoints >= 20000) return 'Gold';
        if ($totalPoints >= 5000) return 'Silver';
        return 'Bronze';
    }

    public static function calculateEarnPoints(float $amountVnd): int
    {
        return (int)floor($amountVnd / 10000);
    }

    public static function calculateMonetaryValue(int $points, float $pointValueVnd = 100): float
    {
        return $points * $pointValueVnd;
    }

    public static function getTierBenefits(string $tier): array
    {
        return match($tier) {
            'Bronze' => ['discount_pct' => 0, 'priority_booking' => false, 'free_parking_hours' => 0, 'annual_checkup_discount' => 0],
            'Silver' => ['discount_pct' => 5, 'priority_booking' => true, 'free_parking_hours' => 2, 'annual_checkup_discount' => 10],
            'Gold' => ['discount_pct' => 10, 'priority_booking' => true, 'free_parking_hours' => 4, 'annual_checkup_discount' => 20],
            'Platinum' => ['discount_pct' => 15, 'priority_booking' => true, 'free_parking_hours' => 8, 'annual_checkup_discount' => 30, 'dedicated_coordinator' => true],
            default => []
        };
    }
}
