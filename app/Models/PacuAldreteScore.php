<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PacuAldreteScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'surgical_case_id',
        'respiration_score',
        'circulation_score',
        'consciousness_score',
        'activity_score',
        'color_score',
        'total_score',
        'is_eligible_for_discharge',
        'evaluated_by',
        'notes',
    ];

    protected $casts = [
        'is_eligible_for_discharge' => 'boolean',
    ];

    public function surgicalCase()
    {
        return $this->belongsTo(SurgicalCase::class, 'surgical_case_id');
    }
}
