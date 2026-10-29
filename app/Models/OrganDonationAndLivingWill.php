<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganDonationAndLivingWill extends Model
{
    use HasFactory;

    protected $table = 'organ_donations_and_living_wills';

    protected $fillable = [
        'patient_profile_id',
        'record_type',
        'registration_number',
        'organs_pledged',
        'dnr_directives',
        'representative_name',
        'representative_phone',
        'representative_relationship',
        'legal_consent_signed',
        'status',
    ];

    protected $casts = [
        'organs_pledged' => 'array',
        'dnr_directives' => 'array',
        'legal_consent_signed' => 'boolean',
    ];

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public static function generateCode(string $type): string
    {
        $prefix = ($type === 'organ_donation') ? 'OD' : 'DNR';
        return $prefix . '-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
    }
}
