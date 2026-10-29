<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_wallet_id',
        'transaction_code',
        'type',
        'amount',
        'balance_after',
        'payment_method',
        'reference_invoice_id',
        'notes',
        'processed_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(PatientWallet::class, 'patient_wallet_id');
    }

    public function referenceInvoice(): BelongsTo
    {
        return $this->belongsTo(PatientInvoice::class, 'reference_invoice_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }

    public static function generateTransactionCode(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "TX-{$today}-";

        $latest = self::where('transaction_code', 'LIKE', "{$prefix}____")
            ->orderBy('transaction_code', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d{4})$/', $latest->transaction_code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
            $nextNumber++;
        } while (self::where('transaction_code', $candidate)->exists());

        return $candidate;
    }

    public function getTypeBadgeAttribute(): string
    {
        return match ($this->type) {
            'deposit' => '<span class="badge bg-success"><i class="bi bi-plus-circle me-1"></i>Nạp Ký Quỹ</span>',
            'deduction' => '<span class="badge bg-danger"><i class="bi bi-dash-circle me-1"></i>Trừ Viện Phí</span>',
            'refund' => '<span class="badge bg-primary"><i class="bi bi-arrow-return-left me-1"></i>Hoàn Ứng</span>',
            default => '<span class="badge bg-secondary">' . $this->type . '</span>',
        };
    }
}
