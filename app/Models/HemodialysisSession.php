<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HemodialysisSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_profile_id',
        'session_date',
        'shift',
        'machine_number',
        'dry_weight_kg',
        'pre_weight_kg',
        'post_weight_kg',
        'uf_target_ml',
        'uf_actual_ml',
        'dialyzer_reuse_count',
        'dialyzer_barcode',
        'status',
        'nurse_in_charge',
    ];

    protected $casts = [
        'dry_weight_kg' => 'decimal:1',
        'pre_weight_kg' => 'decimal:1',
        'post_weight_kg' => 'decimal:1',
        'session_date' => 'date',
    ];

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }
}
