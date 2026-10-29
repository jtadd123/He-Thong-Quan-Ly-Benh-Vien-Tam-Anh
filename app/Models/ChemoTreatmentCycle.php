<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChemoTreatmentCycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'regimen_name',
        'cycle_number',
        'total_cycles',
        'body_surface_area',
        'anc_neutrophil_count',
        'platelet_count',
        'is_eligible_to_dispense',
        'scheduled_date',
        'status',
    ];

    protected $casts = [
        'body_surface_area' => 'decimal:2',
        'is_eligible_to_dispense' => 'boolean',
        'scheduled_date' => 'date',
    ];

    public function patient()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    /**
     * Kiểm tra điều kiện sinh học an toàn để truyền hóa chất:
     * - Bạch cầu hạt trung tính (ANC) >= 1500 /mcL
     * - Tiểu cầu (Platelets) >= 100,000 /mcL
     */
    public static function checkEligibility(int $anc, int $platelets): bool
    {
        return ($anc >= 1500 && $platelets >= 100000);
    }
}
