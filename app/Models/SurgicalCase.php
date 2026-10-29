<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurgicalCase extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_code',
        'patient_id',
        'room_id',
        'lead_surgeon_id',
        'anesthesiologist_id',
        'surgical_procedure',
        'icd10_code',
        'priority',
        'phase',
        'scheduled_start',
        'actual_start',
        'actual_end',
        'surgical_notes',
    ];

    protected $casts = [
        'scheduled_start' => 'datetime',
        'actual_start' => 'datetime',
        'actual_end' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    public function room()
    {
        return $this->belongsTo(OperatingRoom::class, 'room_id');
    }

    public function leadSurgeon()
    {
        return $this->belongsTo(Doctor::class, 'lead_surgeon_id');
    }

    public function anesthesiologist()
    {
        return $this->belongsTo(Doctor::class, 'anesthesiologist_id');
    }

    public function spongeCounts()
    {
        return $this->hasMany(SurgicalSpongeCount::class, 'surgical_case_id');
    }

    public function aldreteScores()
    {
        return $this->hasMany(PacuAldreteScore::class, 'surgical_case_id');
    }
}
