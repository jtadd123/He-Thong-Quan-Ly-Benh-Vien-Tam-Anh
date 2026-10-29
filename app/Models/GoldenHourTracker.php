<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoldenHourTracker extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'emergency_type',
        'door_arrival_time',
        'ct_ecg_done_time',
        'intervention_time',
        'door_to_needle_minutes',
        'door_to_balloon_minutes',
        'met_target',
        'attending_team',
    ];

    protected $casts = [
        'door_arrival_time' => 'datetime',
        'ct_ecg_done_time' => 'datetime',
        'intervention_time' => 'datetime',
        'met_target' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }
}
