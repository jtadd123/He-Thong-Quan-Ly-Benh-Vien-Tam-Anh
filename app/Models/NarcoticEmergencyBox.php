<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NarcoticEmergencyBox extends Model
{
    use HasFactory;

    protected $fillable = [
        'ward_name',
        'box_code',
        'medication_name',
        'concentration',
        'unit',
        'quantity_stock',
        'min_threshold',
    ];

    public function dispenseLogs(): HasMany
    {
        return $this->hasMany(NarcoticDispenseLog::class, 'box_id');
    }
}
