<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\PatientSurvey;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PatientSurveyService
{
    /**
     * Ghi nhận khảo sát đánh giá hài lòng của người bệnh
     */
    public function submitSurvey(array $data): PatientSurvey
    {
        return DB::transaction(function () use ($data) {
            $rReception = (int) ($data['rating_reception'] ?? 5);
            $rDoctor = (int) ($data['rating_doctor'] ?? 5);
            $rFacilities = (int) ($data['rating_facilities'] ?? 5);
            $rWaiting = (int) ($data['rating_waiting_time'] ?? 5);
            $nps = (int) ($data['nps_score'] ?? 10);

            // Điểm trung bình 4 trụ cột Bộ Y Tế
            $overall = round(($rReception + $rDoctor + $rFacilities + $rWaiting) / 4, 2);

            // Tự động gắn cờ đỏ CSKH nếu có điểm <= 2 hoặc NPS <= 4
            $requiresFollowup = ($overall <= 2.5) || ($rReception <= 2) || ($rDoctor <= 2) || ($nps <= 4);

            return PatientSurvey::create([
                'survey_code' => PatientSurvey::generateSurveyCode(),
                'appointment_id' => $data['appointment_id'] ?? null,
                'patient_profile_id' => $data['patient_profile_id'] ?? null,
                'rating_reception' => $rReception,
                'rating_doctor' => $rDoctor,
                'rating_facilities' => $rFacilities,
                'rating_waiting_time' => $rWaiting,
                'overall_score' => $overall,
                'nps_score' => $nps,
                'feedback_text' => $data['feedback_text'] ?? null,
                'requires_followup' => $requiresFollowup,
                'followup_status' => $requiresFollowup ? PatientSurvey::STATUS_PENDING : PatientSurvey::STATUS_CLOSED,
            ]);
        });
    }

    /**
     * CSKH giải quyết khiếu nại / hỗ trợ người bệnh
     */
    public function resolveFollowup(PatientSurvey $survey, string $notes, int $userId): void
    {
        $survey->update([
            'followup_status' => PatientSurvey::STATUS_RESOLVED,
            'followup_notes' => $notes,
            'resolved_by_user_id' => $userId,
            'resolved_at' => Carbon::now(),
        ]);
    }
}
