<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransfusionReactionReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'blood_unit_id',
        'patient_id',
        'onset_time',
        'reaction_type',
        'symptoms',
        'immediate_transfusion_stopped',
        'blood_unit_quarantined',
        'reporting_physician',
        'outcome',
    ];

    protected $casts = [
        'onset_time' => 'datetime',
        'immediate_transfusion_stopped' => 'boolean',
        'blood_unit_quarantined' => 'boolean',
    ];

    public function bloodUnit()
    {
        return $this->belongsTo(BloodUnit::class, 'blood_unit_id');
    }

    public function patient()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }
}
