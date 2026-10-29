<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AntimicrobialApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'antibiotic_name',
        'tier_level',
        'prescribing_doctor',
        'clinical_indication',
        'approval_status',
        'pharmacist_approver',
        'microbiologist_approver',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }
}
