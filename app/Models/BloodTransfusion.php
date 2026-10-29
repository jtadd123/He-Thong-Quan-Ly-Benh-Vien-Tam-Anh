<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BloodTransfusion extends Model
{
    use HasFactory;

    public const STATUS_PREPARED = 'prepared';
    public const STATUS_TRANSFUSING = 'transfusing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ABORTED = 'aborted';

    protected $fillable = [
        'transfusion_code',
        'patient_profile_id',
        'blood_unit_id',
        'doctor_id',
        'indication',
        'patient_abo',
        'patient_rh',
        'is_crossmatch_compatible',
        'crossmatch_notes',
        'nurse_user_id_1',
        'nurse_user_id_2',
        'started_at',
        'completed_at',
        'vitals_baseline',
        'vitals_15min',
        'reaction_occurred',
        'reaction_details',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_crossmatch_compatible' => 'boolean',
            'reaction_occurred' => 'boolean',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'vitals_baseline' => 'array',
            'vitals_15min' => 'array',
        ];
    }

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public function bloodUnit(): BelongsTo
    {
        return $this->belongsTo(BloodUnit::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function nurse1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nurse_user_id_1');
    }

    public function nurse2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nurse_user_id_2');
    }

    public static function generateTransfusionCode(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "TRF-{$today}-";

        $latest = self::where('transfusion_code', 'LIKE', "{$prefix}____")
            ->orderBy('transfusion_code', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d{4})$/', $latest->transfusion_code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
            $nextNumber++;
        } while (self::where('transfusion_code', $candidate)->exists());

        return $candidate;
    }
}
