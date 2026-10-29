<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualTumorBoard extends Model
{
    use HasFactory;

    protected $fillable = [
        'board_code',
        'patient_id',
        'cancer_diagnosis',
        'meeting_date',
        'consensus_treatment_plan',
        'multidisciplinary_attendees',
        'status',
    ];

    protected $casts = [
        'meeting_date' => 'datetime',
        'multidisciplinary_attendees' => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }
}
