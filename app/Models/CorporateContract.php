<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CorporateContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_code',
        'company_name',
        'company_tax_code',
        'contact_person',
        'contact_phone',
        'checkup_start_date',
        'checkup_end_date',
        'total_employees',
        'package_name',
        'cost_per_person',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'checkup_start_date' => 'date',
            'checkup_end_date' => 'date',
            'total_employees' => 'integer',
            'cost_per_person' => 'decimal:2',
        ];
    }

    public function employees(): HasMany
    {
        return $this->hasMany(CorporateEmployee::class);
    }

    public static function generateContractCode(): string
    {
        $today = Carbon::now()->format('Ym');
        $prefix = "HĐ-{$today}-";

        $latest = self::where('contract_code', 'LIKE', "{$prefix}____")
            ->orderBy('contract_code', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d{4})$/', $latest->contract_code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
            $nextNumber++;
        } while (self::where('contract_code', $candidate)->exists());

        return $candidate;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'completed' => '<span class="badge bg-secondary">Đã Nghiệm Thu</span>',
            'draft' => '<span class="badge bg-warning text-dark">Dự Thảo</span>',
            default => '<span class="badge bg-success">Đang Triển Khai Khám</span>',
        };
    }
}
