<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BhutaniJaundiceEvaluation extends Model
{
    use HasFactory;

    protected $table = 'bhutani_jaundice_evaluations';

    protected $fillable = [
        'infant_patient_id',
        'age_in_hours',
        'total_serum_bilirubin_mg_dl',
        'risk_zone',
        'phototherapy_indicated',
        'exchange_transfusion_indicated',
        'neonatologist',
    ];

    protected $casts = [
        'total_serum_bilirubin_mg_dl' => 'decimal:2',
        'phototherapy_indicated' => 'boolean',
        'exchange_transfusion_indicated' => 'boolean',
    ];

    public function infant()
    {
        return $this->belongsTo(PatientProfile::class, 'infant_patient_id');
    }

    /**
     * Thuật toán Bhutani Nomogram:
     * - > 72 giờ tuổi: Bilirubin >= 15 mg/dL -> High risk (Chỉ định chiếu đèn)
     * - > 72 giờ tuổi: Bilirubin >= 20 mg/dL -> Chỉ định thay máu cấp cứu
     */
    public static function evaluateRisk(int $hours, float $tsb): array
    {
        $zone = 'LOW_RISK';
        $photo = false;
        $exchange = false;
        $photoIndication = 'none';

        if ($hours >= 72) {
            if ($tsb >= 20.0) {
                $zone = 'HIGH_RISK';
                $photo = true;
                $exchange = true;
                $photoIndication = 'exchange_and_phototherapy';
            } elseif ($tsb >= 15.0) {
                $zone = 'HIGH_RISK';
                $photo = true;
                $photoIndication = 'intensive_phototherapy';
            } elseif ($tsb >= 12.0) {
                $zone = 'HIGH_INTERMEDIATE';
            }
        } elseif ($hours >= 48) {
            if ($tsb >= 13.0) {
                $zone = 'HIGH_RISK';
                $photo = true;
                $photoIndication = 'intensive_phototherapy';
            }
        }

        return [
            0 => $zone,
            1 => $photo,
            2 => $exchange,
            'risk_zone' => strtolower($zone),
            'phototherapy_indicated' => $photo,
            'phototherapy_indication' => $photoIndication,
            'exchange_transfusion_indicated' => $exchange,
        ];
    }
}
