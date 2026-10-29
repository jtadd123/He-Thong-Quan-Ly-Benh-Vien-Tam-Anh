<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentLoan extends Model
{
    use HasFactory;

    public const STATUS_BORROWED = 'borrowed';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_OVERDUE = 'overdue';

    protected $fillable = [
        'loan_code',
        'mobility_equipment_id',
        'patient_profile_id',
        'borrower_name',
        'borrower_phone',
        'id_card_held',
        'destination_department',
        'borrowed_at',
        'returned_at',
        'dispatched_by_user_id',
        'received_by_user_id',
        'disinfection_notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'borrowed_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(MobilityEquipment::class, 'mobility_equipment_id');
    }

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public function dispatchedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by_user_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    public static function generateLoanCode(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "LN-{$today}-";

        $latest = self::where('loan_code', 'LIKE', "{$prefix}____")
            ->orderBy('loan_code', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d{4})$/', $latest->loan_code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
            $nextNumber++;
        } while (self::where('loan_code', $candidate)->exists());

        return $candidate;
    }
}
