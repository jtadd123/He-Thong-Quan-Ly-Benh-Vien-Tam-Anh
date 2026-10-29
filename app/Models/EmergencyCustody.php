<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyCustody extends Model
{
    use HasFactory;

    protected $fillable = [
        'custody_code',
        'patient_profile_id',
        'appointment_id',
        'bag_seal_number',
        'item_description',
        'cash_amount',
        'nurse_user_id',
        'security_user_id',
        'status',
        'return_recipient_name',
        'return_recipient_id_card',
        'return_relationship',
        'returned_at',
        'returned_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'cash_amount' => 'decimal:2',
            'returned_at' => 'datetime',
        ];
    }

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nurse_user_id');
    }

    public function security(): BelongsTo
    {
        return $this->belongsTo(User::class, 'security_user_id');
    }

    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by_user_id');
    }

    public static function generateCustodyCode(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "TS-{$today}-";

        $latest = self::where('custody_code', 'LIKE', "{$prefix}____")
            ->orderBy('custody_code', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d{4})$/', $latest->custody_code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
            $nextNumber++;
        } while (self::where('custody_code', $candidate)->exists());

        return $candidate;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'returned' => '<span class="badge bg-success"><i class="bi bi-person-check-fill me-1"></i>Đã Bàn Giao Trả</span>',
            default => '<span class="badge bg-danger animate-pulse"><i class="bi bi-lock-fill me-1"></i>Đang Niêm Phong</span>',
        };
    }
}
