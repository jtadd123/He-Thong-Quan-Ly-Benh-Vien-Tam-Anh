<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\ClinicalConsultation;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\PatientProfile;
use Carbon\Carbon;

class ClinicalConsultationService
{
    /**
     * Tạo yêu cầu hội chẩn liên chuyên khoa nội viện
     */
    public function requestConsultation(array $data, int $requestingUserId): ClinicalConsultation
    {
        $patient = PatientProfile::findOrFail($data['patient_profile_id']);

        // Tự động bổ sung sinh hiệu và tiền sử dị ứng nếu người dùng chưa điền chi tiết
        $clinicalSummary = $data['clinical_summary'] ?? '';
        if (empty($clinicalSummary) || strlen($clinicalSummary) < 10) {
            $latestVital = $patient->latestVital;
            $vitalsInfo = $latestVital 
                ? "Mạch: {$latestVital->pulse} l/p, HA: {$latestVital->blood_pressure_systolic}/{$latestVital->blood_pressure_diastolic} mmHg, Nhiệt độ: {$latestVital->temperature}°C, SpO2: {$latestVital->spo2}%"
                : "Chưa ghi nhận sinh hiệu gần nhất";
            $allergies = $patient->allergies ?: 'Không ghi nhận tiền sử dị ứng';
            $clinicalSummary = "Sinh hiệu: [{$vitalsInfo}]. Tiền sử dị ứng: [{$allergies}]. Diễn biến: " . ($data['reason'] ?? 'Cần hội chẩn chuyên khoa');
        }

        return ClinicalConsultation::create([
            'consultation_code' => ClinicalConsultation::generateConsultationCode(),
            'medical_record_id' => $data['medical_record_id'] ?? null,
            'appointment_id' => $data['appointment_id'] ?? null,
            'patient_profile_id' => $patient->id,
            'requesting_user_id' => $requestingUserId,
            'requesting_specialty_id' => $data['requesting_specialty_id'] ?? null,
            'target_specialty_id' => $data['target_specialty_id'],
            'target_doctor_id' => $data['target_doctor_id'] ?? null,
            'urgency_level' => $data['urgency_level'] ?? ClinicalConsultation::URGENCY_ROUTINE,
            'reason' => $data['reason'],
            'clinical_summary' => $clinicalSummary,
            'proposed_questions' => $data['proposed_questions'],
            'status' => ClinicalConsultation::STATUS_REQUESTED,
        ]);
    }

    /**
     * Bác sĩ chuyên khoa được mời phản hồi kết luận hội chẩn
     */
    public function respondConsultation(ClinicalConsultation $consultation, array $data, ?int $consultantDoctorId = null): void
    {
        $consultation->update([
            'consultation_result' => $data['consultation_result'],
            'treatment_recommendations' => $data['treatment_recommendations'] ?? null,
            'consultant_doctor_id' => $consultantDoctorId,
            'responded_at' => Carbon::now(),
            'status' => ClinicalConsultation::STATUS_COMPLETED,
        ]);
    }
}
