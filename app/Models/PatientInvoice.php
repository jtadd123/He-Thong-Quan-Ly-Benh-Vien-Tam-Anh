<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientInvoice extends Model
{
    use HasFactory;

    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_PAID = 'paid';
    public const STATUS_REFUNDED = 'refunded';

    public const METHOD_CASH = 'cash';
    public const METHOD_VIETQR = 'vietqr';
    public const METHOD_CARD = 'card';
    public const METHOD_INSURANCE_DIRECT = 'insurance_direct';

    protected $fillable = [
        'invoice_code',
        'patient_profile_id',
        'appointment_id',
        'medical_record_id',
        'consultation_fee',
        'medication_fee',
        'procedure_fee',
        'total_amount',
        'insurance_covered_amount',
        'patient_co_pay_amount',
        'payment_status',
        'payment_method',
        'paid_at',
        'received_by_user_id',
        'vietqr_ref',
        'notes',
    ];

    protected $casts = [
        'consultation_fee' => 'decimal:2',
        'medication_fee' => 'decimal:2',
        'procedure_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'insurance_covered_amount' => 'decimal:2',
        'patient_co_pay_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_profile_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class, 'medical_record_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::STATUS_PAID;
    }

    public function getPaymentStatusBadgeAttribute(): array
    {
        return match ($this->payment_status) {
            self::STATUS_PAID => [
                'label' => 'ĐÃ THANH TOÁN',
                'color' => 'success',
                'badge_class' => 'bg-success text-white',
                'icon' => 'bi-check2-circle',
            ],
            self::STATUS_REFUNDED => [
                'label' => 'ĐÃ HOÀN TIỀN',
                'color' => 'secondary',
                'badge_class' => 'bg-secondary text-white',
                'icon' => 'bi-arrow-counterclockwise',
            ],
            default => [
                'label' => 'CHƯA THANH TOÁN',
                'color' => 'warning',
                'badge_class' => 'bg-warning text-dark border border-warning',
                'icon' => 'bi-clock-history',
            ],
        };
    }

    public function getPaymentMethodTextAttribute(): string
    {
        return match ($this->payment_method) {
            self::METHOD_VIETQR => 'Chuyển Khoản VietQR (Napas 247)',
            self::METHOD_CARD => 'Thẻ Ngân Hàng / POS',
            self::METHOD_INSURANCE_DIRECT => 'BHYT Bảo Lãnh Trực Tiếp',
            default => 'Tiền Mặt Tại Quầy',
        };
    }

    public static function generateInvoiceCode(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "HD-{$today}-";

        $latest = self::where('invoice_code', 'LIKE', "{$prefix}____")
            ->orderBy('invoice_code', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d{4})$/', $latest->invoice_code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
            $nextNumber++;
        } while (self::where('invoice_code', $candidate)->exists());

        return $candidate;
    }
}
