<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GreenHospitalEsgLog extends Model
{
    use HasFactory;

    protected $table = 'green_hospital_esg_logs';

    protected $fillable = [
        'log_date',
        'paperless_sheets_saved',
        'carbon_footprint_saved_kg',
        'energy_kwh_conserved',
        'medical_waste_recycled_kg',
        'esg_score',
    ];

    protected $casts = [
        'log_date' => 'date',
        'carbon_footprint_saved_kg' => 'decimal:2',
        'energy_kwh_conserved' => 'decimal:2',
        'medical_waste_recycled_kg' => 'decimal:2',
    ];
}
