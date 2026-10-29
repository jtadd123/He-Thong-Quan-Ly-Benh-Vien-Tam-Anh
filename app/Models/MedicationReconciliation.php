<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicationReconciliation extends Model
{
    protected $table = 'medication_reconciliations';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'reconciliation_type',
        'home_medications',
        'hospital_medications',
        'discrepancies',
        'total_home_meds',
        'total_hospital_meds',
        'discrepancies_count',
        'discrepancies_resolved',
        'risk_level',
        'status',
        'pharmacist_notes',
        'pharmacist_name',
        'completed_at',
    ];

    protected $casts = [
        'home_medications' => 'array',
        'hospital_medications' => 'array',
        'discrepancies' => 'array',
        'completed_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public static function assessRisk(int $discrepancyCount, int $totalMeds): string
    {
        $ratio = $totalMeds > 0 ? $discrepancyCount / $totalMeds : 0;
        if ($ratio >= 0.5 || $discrepancyCount >= 5) return 'critical';
        if ($ratio >= 0.3 || $discrepancyCount >= 3) return 'high';
        if ($ratio >= 0.1 || $discrepancyCount >= 1) return 'medium';
        return 'low';
    }

    public static function findDiscrepancies(array $homeMeds, array $hospitalMeds): array
    {
        $discrepancies = [];
        $homeCodes = array_filter(array_column($homeMeds, 'code'));
        $hospCodes = array_filter(array_column($hospitalMeds, 'code'));

        foreach ($homeMeds as $med) {
            $c = $med['code'] ?? null;
            if ($c && !in_array($c, $hospCodes)) {
                $discrepancies[] = [
                    'type' => 'omission',
                    'medication' => $med['name'] ?? 'Không rõ',
                    'detail' => 'Thuốc dùng tại nhà chưa được đưa vào y lệnh bệnh viện'
                ];
            }
        }

        foreach ($hospitalMeds as $med) {
            $c = $med['code'] ?? null;
            if ($c && !in_array($c, $homeCodes)) {
                $discrepancies[] = [
                    'type' => 'commission',
                    'medication' => $med['name'] ?? 'Không rõ',
                    'detail' => 'Thuốc mới chỉ định tại viện cần đối chiếu tương tác'
                ];
            }
        }

        return $discrepancies;
    }
}
