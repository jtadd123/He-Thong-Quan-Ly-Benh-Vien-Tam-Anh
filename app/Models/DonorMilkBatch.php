<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonorMilkBatch extends Model
{
    use HasFactory;

    protected $table = 'donor_milk_batches';

    protected $fillable = [
        'batch_code',
        'donor_code',
        'volume_ml',
        'pasteurization_temperature',
        'pasteurization_duration_min',
        'microbiology_result',
        'status',
        'expiry_date',
    ];

    protected $casts = [
        'pasteurization_temperature' => 'decimal:1',
        'expiry_date' => 'date',
    ];
}
