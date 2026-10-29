<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnesthesiaRecord extends Model
{
    protected $table = 'anesthesia_records';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'surgery_name',
        'anesthesia_type',
        'asa_class',
        'mallampati_score',
        'premedication',
        'induction_agents',
        'maintenance_agents',
        'muscle_relaxants',
        'total_fentanyl_mcg',
        'total_propofol_mg',
        'total_blood_loss_ml',
        'total_fluid_given_ml',
        'units_prbc',
        'anesthesia_start_time',
        'surgery_start_time',
        'surgery_end_time',
        'anesthesia_end_time',
        'anesthesia_duration_minutes',
        'surgery_duration_minutes',
        'intraop_vitals',
        'complications',
        'pacu_vitals',
        'aldrete_score',
        'pacu_discharge_criteria',
        'anesthesia_notes',
    ];

    protected $casts = [
        'premedication' => 'array',
        'induction_agents' => 'array',
        'maintenance_agents' => 'array',
        'muscle_relaxants' => 'array',
        'intraop_vitals' => 'array',
        'pacu_vitals' => 'array',
        'anesthesia_start_time' => 'datetime',
        'surgery_start_time' => 'datetime',
        'surgery_end_time' => 'datetime',
        'anesthesia_end_time' => 'datetime',
        'total_fentanyl_mcg' => 'decimal:1',
        'total_propofol_mg' => 'decimal:1',
        'total_blood_loss_ml' => 'decimal:1',
        'total_fluid_given_ml' => 'decimal:1',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public static function classifyAsa(string $asaClass): string
    {
        return match($asaClass) {
            'I' => 'Bệnh nhân khỏe mạnh - Nguy cơ phẫu thuật 0.1%',
            'II' => 'Bệnh hệ thống nhẹ - Nguy cơ 0.5%',
            'III' => 'Bệnh hệ thống nặng - Nguy cơ 1-3%',
            'IV' => 'Bệnh hệ thống nặng đe dọa tính mạng - Nguy cơ 7-10%',
            'V' => 'Bệnh nhân hấp hối - Nguy cơ >10%',
            default => 'Chưa phân loại'
        };
    }

    public static function calculateAldreteScore(array $criteria): int
    {
        $score = 0;
        $score += min(2, (int)($criteria['activity'] ?? 0));
        $score += min(2, (int)($criteria['respiration'] ?? 0));
        $score += min(2, (int)($criteria['circulation'] ?? 0));
        $score += min(2, (int)($criteria['consciousness'] ?? 0));
        $score += min(2, (int)($criteria['oxygen_saturation'] ?? 0));
        return $score;
    }

    public static function isReadyForPacuDischarge(int $aldreteScore): bool
    {
        return $aldreteScore >= 9;
    }

    public static function classifyMallampati(int $score): string
    {
        return match($score) {
            1 => 'Class I: Toàn bộ khẩu hầu - Đặt NKQ dễ',
            2 => 'Class II: Phần trên amygdale - Dễ đặt',
            3 => 'Class III: Màn khẩu cái - Có thể khó đặt',
            4 => 'Class IV: Chỉ thấy khẩu cái cứng - Khó đặt NKQ',
            default => 'Không xác định'
        };
    }
}
