<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vaccine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'disease_prevented',
        'manufacturer',
        'origin_country',
        'standard_dosage',
        'route',
        'total_doses',
        'interval_days_between_doses',
        'storage_temp',
        'price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'total_doses' => 'integer',
            'interval_days_between_doses' => 'integer',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function records(): HasMany
    {
        return $this->hasMany(VaccinationRecord::class);
    }
}
