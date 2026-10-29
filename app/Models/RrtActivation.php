<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RrtActivation extends Model
{
    use HasFactory;

    protected $fillable = [
        'news2_alert_id',
        'patient_profile_id',
        'activation_code',
        'ward_location',
        'status',
        'lead_doctor',
        'icu_nurse',
        'response_time_minutes',
        'intervention_summary',
    ];

    public function news2Alert(): BelongsTo
    {
        return $this->belongsTo(News2VitalAlert::class, 'news2_alert_id');
    }

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public static function generateCode(): string
    {
        return 'RRT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
    }
}
