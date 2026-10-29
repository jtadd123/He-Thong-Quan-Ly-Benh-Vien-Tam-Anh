<?php

namespace App\Services;

use App\Models\EmergencyCodeRed;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CodeRedEmergencyService
{
    /**
     * Kích hoạt tiếp nhận cấp cứu trước viện & Báo động đỏ toàn viện
     */
    public function activateCodeRed(array $data, int $userId): EmergencyCodeRed
    {
        return DB::transaction(function () use ($data, $userId) {
            $vitals = [
                'blood_pressure' => $data['blood_pressure'] ?? 'Chưa đo',
                'pulse' => $data['pulse'] ?? '--',
                'spo2' => $data['spo2'] ?? '--',
                'glasgow' => $data['glasgow'] ?? '15/15',
            ];

            return EmergencyCodeRed::create([
                'code_red_number' => EmergencyCodeRed::generateCodeRedNumber(),
                'patient_name' => $data['patient_name'],
                'gender' => $data['gender'] ?? 'unknown',
                'estimated_age' => $data['estimated_age'] ?? 'Khoảng 50 tuổi',
                'patient_profile_id' => $data['patient_profile_id'] ?? null,
                'ambulance_unit' => $data['ambulance_unit'] ?? 'Xe Cấp Cứu 115 Tâm Anh',
                'paramedic_in_charge' => $data['paramedic_in_charge'] ?? 'Kíp cấp cứu lưu động',
                'paramedic_phone' => $data['paramedic_phone'] ?? '1800 6858',
                'eta_minutes' => (int) ($data['eta_minutes'] ?? 10),
                'emergency_type' => $data['emergency_type'],
                'clinical_condition' => $data['clinical_condition'],
                'in_transit_vitals' => $vitals,
                'activated_by_user_id' => $userId,
                'activated_at' => Carbon::now(),
                'status' => EmergencyCodeRed::STATUS_EN_ROUTE,
            ]);
        });
    }

    /**
     * Đánh dấu mốc xe cấp cứu tới cửa sảnh viện (Door Time)
     */
    public function markArrived(EmergencyCodeRed $codeRed): void
    {
        $codeRed->update([
            'status' => EmergencyCodeRed::STATUS_ARRIVED,
            'arrived_at' => Carbon::now(),
        ]);
    }

    /**
     * Đánh dấu bắt đầu can thiệp (Needle / Balloon / Surgery)
     */
    public function startIntervention(EmergencyCodeRed $codeRed): void
    {
        $codeRed->update([
            'status' => EmergencyCodeRed::STATUS_IN_INTERVENTION,
            'intervention_started_at' => Carbon::now(),
        ]);
    }

    /**
     * Hoàn tất xử trí ca cấp cứu báo động đỏ
     */
    public function resolveCodeRed(EmergencyCodeRed $codeRed, string $outcomeNotes): void
    {
        $codeRed->update([
            'status' => EmergencyCodeRed::STATUS_RESOLVED,
            'resolved_at' => Carbon::now(),
            'outcome_notes' => $outcomeNotes,
        ]);
    }
}
