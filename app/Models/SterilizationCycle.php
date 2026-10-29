<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SterilizationCycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'cycle_code',
        'machine_name',
        'method',
        'temp_celsius',
        'pressure_bar',
        'chemical_indicator_pass',
        'biological_indicator_pass',
        'operator_name',
        'status',
        'cycle_time',
    ];

    protected $casts = [
        'pressure_bar' => 'decimal:1',
        'chemical_indicator_pass' => 'boolean',
        'biological_indicator_pass' => 'boolean',
        'cycle_time' => 'datetime',
    ];

    public function instrumentTrays(): HasMany
    {
        return $this->hasMany(CssdInstrumentTray::class, 'last_cycle_id');
    }

    public static function generateCode(): string
    {
        return 'STER-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
    }
}
