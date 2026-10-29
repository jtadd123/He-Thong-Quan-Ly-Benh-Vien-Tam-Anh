<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HaiSurveillance extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'infection_type',
        'pathogen_detected',
        'department',
        'identified_date',
        'containment_action',
        'is_resolved',
    ];

    protected $casts = [
        'identified_date' => 'date',
        'is_resolved' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }
}
