<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabService extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'sample_type',
        'normal_range',
        'unit',
        'price',
        'execution_room',
        'panic_low',
        'panic_high',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'panic_low' => 'float',
            'panic_high' => 'float',
            'is_active' => 'boolean',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(LabOrderItem::class);
    }
}
