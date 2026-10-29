<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NarcoticDispenseLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'box_id',
        'patient_profile_id',
        'doctor_name',
        'nurse_name',
        'witness_name',
        'quantity_dispensed',
        'indication',
        'empty_ampoules_returned',
        'disposal_verified',
    ];

    protected $casts = [
        'disposal_verified' => 'boolean',
    ];

    public function box(): BelongsTo
    {
        return $this->belongsTo(NarcoticEmergencyBox::class, 'box_id');
    }

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }
}
