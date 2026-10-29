<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HospitalQualityCriteria extends Model
{
    use HasFactory;

    protected $table = 'hospital_quality_criteria';

    protected $fillable = [
        'criteria_code',
        'criteria_name',
        'section',
        'current_level',
        'target_level',
        'evidence_summary',
        'last_audited_date',
    ];

    protected $casts = [
        'last_audited_date' => 'date',
    ];
}
