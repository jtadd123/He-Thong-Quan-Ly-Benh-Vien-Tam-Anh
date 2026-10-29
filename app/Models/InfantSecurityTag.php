<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfantSecurityTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'infant_tag_code',
        'mother_patient_id',
        'baby_gender',
        'birth_time',
        'birth_weight_grams',
        'mother_band_code',
        'is_paired',
        'perimeter_status',
    ];

    protected $casts = [
        'birth_time' => 'datetime',
        'birth_weight_grams' => 'decimal:1',
        'is_paired' => 'boolean',
    ];

    public function mother()
    {
        return $this->belongsTo(PatientProfile::class, 'mother_patient_id');
    }
}
