<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcupointTherapy extends Model
{
    use HasFactory;

    protected $table = 'acupoint_therapies';

    protected $fillable = [
        'patient_id',
        'meridian_name',
        'acupoints_selected',
        'therapy_method',
        'treatment_course_days',
        'acupuncturist',
    ];

    public function patient()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }
}
