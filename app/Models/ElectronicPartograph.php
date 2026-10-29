<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElectronicPartograph extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'observation_time',
        'cervical_dilation_cm',
        'fetal_head_station',
        'fetal_heart_rate',
        'contractions_per_10min',
        'crosses_alert_line',
        'crosses_action_line',
        'midwife_name',
    ];

    protected $casts = [
        'observation_time' => 'datetime',
        'cervical_dilation_cm' => 'decimal:1',
        'fetal_head_station' => 'decimal:1',
        'crosses_alert_line' => 'boolean',
        'crosses_action_line' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }
}
