<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatientWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_profile_id',
        'wallet_code',
        'balance',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
        ];
    }

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class)->orderBy('created_at', 'desc');
    }

    public static function generateWalletCode(): string
    {
        $today = Carbon::now()->format('Ym');
        $prefix = "WAL-{$today}-";

        $latest = self::where('wallet_code', 'LIKE', "{$prefix}____")
            ->orderBy('wallet_code', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d{4})$/', $latest->wallet_code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
            $nextNumber++;
        } while (self::where('wallet_code', $candidate)->exists());

        return $candidate;
    }

    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance, 0, ',', '.') . ' VNĐ';
    }
}
