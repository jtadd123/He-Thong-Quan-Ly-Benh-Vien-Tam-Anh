<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceClaimScrub extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_profile_id',
        'icd10_code',
        'icd10_name',
        'claim_amount',
        'scrub_status',
        'scrub_violations',
        'xml_4210_exported',
        'verified_by',
    ];

    protected $casts = [
        'claim_amount' => 'decimal:2',
        'scrub_violations' => 'array',
        'xml_4210_exported' => 'boolean',
    ];

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }
}
