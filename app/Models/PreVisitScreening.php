<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreVisitScreening extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_code',
        'patient_name',
        'patient_phone',
        'symptoms_declared',
        'home_medications',
        'known_allergies',
        'pregnancy_or_breastfeeding',
        'family_medical_history',
        'risk_stratification',
        'synced_to_emr',
    ];

    protected $casts = [
        'symptoms_declared' => 'array',
        'pregnancy_or_breastfeeding' => 'boolean',
        'synced_to_emr' => 'boolean',
    ];
}
