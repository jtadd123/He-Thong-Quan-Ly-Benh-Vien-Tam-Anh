<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FluidBalanceRecord extends Model
{
    protected $table = 'fluid_balance_records';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'record_date',
        'oral_intake',
        'iv_intake',
        'feeding_tube_intake',
        'total_intake',
        'urine_output_ml',
        'drain_output',
        'stool_output',
        'insensible_loss',
        'total_output',
        'fluid_balance',
        'cumulative_balance',
        'body_weight',
        'cvp',
        'balance_status',
        'nursing_notes',
    ];

    protected $casts = [
        'record_date' => 'date',
        'oral_intake' => 'decimal:1',
        'iv_intake' => 'decimal:1',
        'feeding_tube_intake' => 'decimal:1',
        'total_intake' => 'decimal:1',
        'urine_output_ml' => 'decimal:1',
        'drain_output' => 'decimal:1',
        'stool_output' => 'decimal:1',
        'insensible_loss' => 'decimal:1',
        'total_output' => 'decimal:1',
        'fluid_balance' => 'decimal:1',
        'cumulative_balance' => 'decimal:1',
        'body_weight' => 'decimal:2',
        'cvp' => 'decimal:1',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public static function calculateBalance(float $intake, float $output): float
    {
        return round($intake - $output, 1);
    }

    public static function calculateInsensibleLoss(float $weightKg, float $tempC = 37.0): float
    {
        $base = $weightKg * 10;
        $feverBonus = max(0, ($tempC - 37) * 200);
        return round($base + $feverBonus, 1);
    }

    public static function classifyBalance(float $balanceMl): string
    {
        if ($balanceMl > 2000) return 'severely_positive';
        if ($balanceMl > 500) return 'positive';
        if ($balanceMl < -2000) return 'severely_negative';
        if ($balanceMl < -500) return 'negative';
        return 'balanced';
    }
}
