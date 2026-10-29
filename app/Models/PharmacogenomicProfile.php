<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacogenomicProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'gene_name',
        'genotype',
        'phenotype',
        'contraindicated_medication',
        'recommended_alternative',
        'clinical_guideline_source',
    ];

    public function patient()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }
}
