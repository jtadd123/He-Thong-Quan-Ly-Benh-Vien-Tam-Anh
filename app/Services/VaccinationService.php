<?php

namespace App\Services;

use App\Models\VaccinationRecord;
use App\Models\Vaccine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VaccinationService
{
    /**
     * Thực hiện tiêm vắc-xin cho người bệnh
     */
    public function administerVaccine(array $data, int $nurseUserId): VaccinationRecord
    {
        return DB::transaction(function () use ($data, $nurseUserId) {
            $vaccine = Vaccine::findOrFail($data['vaccine_id']);
            $administeredAt = isset($data['administered_at']) ? Carbon::parse($data['administered_at']) : Carbon::now();

            // Tính ngày hẹn mũi tiếp theo dựa trên khoảng cách phác đồ
            $nextDoseDueDate = null;
            $currentDose = (int) ($data['dose_number'] ?? 1);
            if ($currentDose < $vaccine->total_doses && $vaccine->interval_days_between_doses > 0) {
                $nextDoseDueDate = (clone $administeredAt)->addDays($vaccine->interval_days_between_doses)->toDateString();
            }

            // Sàng lọc nhiệt độ sốt
            $temp = isset($data['body_temperature']) ? (float) $data['body_temperature'] : 36.5;
            $screeningPassed = $temp < 38.0;

            return VaccinationRecord::create([
                'record_code' => VaccinationRecord::generateRecordCode(),
                'patient_profile_id' => $data['patient_profile_id'],
                'vaccine_id' => $vaccine->id,
                'appointment_id' => $data['appointment_id'] ?? null,
                'dose_number' => $currentDose,
                'batch_number' => $data['batch_number'] ?? ('LOT-' . date('Ym') . '-A'),
                'batch_expiry_date' => $data['batch_expiry_date'] ?? Carbon::now()->addMonths(12)->toDateString(),
                'injection_site' => $data['injection_site'] ?? 'Cơ delta cánh tay trái',
                'administered_by_user_id' => $nurseUserId,
                'screening_passed' => $screeningPassed,
                'body_temperature' => $temp,
                'screening_notes' => $data['screening_notes'] ?? null,
                'administered_at' => $administeredAt,
                'next_dose_due_date' => $nextDoseDueDate,
                'observation_status' => VaccinationRecord::STATUS_OBSERVING,
            ]);
        });
    }

    /**
     * Xác nhận an toàn sau 30 phút theo dõi
     */
    public function clearObservation(VaccinationRecord $record, ?string $notes = null): void
    {
        $record->update([
            'observation_status' => VaccinationRecord::STATUS_CLEARED,
            'observation_cleared_at' => Carbon::now(),
            'adverse_reaction_notes' => $notes,
        ]);
    }

    /**
     * Ghi nhận phản ứng sau tiêm bất thường (sốt cao, mề đay, khó thở...)
     */
    public function recordAdverseReaction(VaccinationRecord $record, string $notes): void
    {
        $record->update([
            'observation_status' => VaccinationRecord::STATUS_ADVERSE,
            'adverse_reaction_notes' => $notes,
        ]);
    }
}
