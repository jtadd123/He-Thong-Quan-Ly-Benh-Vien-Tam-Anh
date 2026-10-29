<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HospitalWayfinding extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_location',
        'destination_department',
        'floor_level',
        'estimated_walk_minutes',
        'step_directions',
        'accessibility_friendly',
    ];

    protected $casts = [
        'step_directions' => 'array',
    ];
}
