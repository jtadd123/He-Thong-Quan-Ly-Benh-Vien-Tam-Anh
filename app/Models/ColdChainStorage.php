<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ColdChainStorage extends Model
{
    use HasFactory;

    protected $fillable = [
        'storage_name',
        'storage_code',
        'storage_type',
        'temp_min',
        'temp_max',
        'current_temp',
        'status',
        'is_locked',
        'last_reading_at',
    ];

    protected $casts = [
        'temp_min' => 'decimal:1',
        'temp_max' => 'decimal:1',
        'current_temp' => 'decimal:1',
        'is_locked' => 'boolean',
        'last_reading_at' => 'datetime',
    ];

    public function temperatureLogs(): HasMany
    {
        return $this->hasMany(TemperatureLog::class, 'storage_id');
    }
}
