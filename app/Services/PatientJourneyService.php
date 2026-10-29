<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\PatientNotification;
use App\Models\PatientProfile;
use Carbon\Carbon;

class PatientJourneyService
{
    /**
     * Xác định chi tiết trạng thái tiến trình khám của bệnh nhân
     */
    public function getJourneyData(Appointment $appointment): array
    {
        $appointment->load(['patientProfile', 'doctor.specialty', 'specialty', 'medicalRecord.prescriptions', 'invoices']);

        $patient = $appointment->patientProfile;
        $doctor = $appointment->doctor;
        $record = $appointment->medicalRecord;
        $invoices = $appointment->invoices;

        // Tính số bệnh nhân đang chờ phía trước tại cùng bác sĩ/chuyên khoa
        $peopleAhead = 0;
        if ($appointment->status === Appointment::STATUS_CONFIRMED) {
            $peopleAhead = Appointment::where('doctor_id', $appointment->doctor_id)
                ->whereDate('appointment_date', $appointment->appointment_date)
                ->where('status', Appointment::STATUS_CONFIRMED)
                ->where('id', '<', $appointment->id)
                ->count();
        }

        // Ước tính thời gian chờ: trung bình 15 phút / ca khám
        $estimatedWaitMinutes = $peopleAhead * 15;

        // Xác định bước hiện tại trong 5 bước hành trình
        // 1: Tiếp nhận tại quầy / Kiosk
        // 2: Chờ vào phòng khám
        // 3: Đang trong phòng khám / Chẩn đoán
        // 4: Thanh toán viện phí & Quầy dược
        // 5: Hoàn tất buổi khám
        $currentStep = 1;
        $stepStatus = [
            1 => ['label' => 'Tiếp nhận thành công', 'state' => 'completed', 'time' => $appointment->created_at->format('H:i d/m/Y')],
            2 => ['label' => 'Hàng đợi phòng khám', 'state' => 'pending', 'time' => null],
            3 => ['label' => 'Bác sĩ thăm khám', 'state' => 'pending', 'time' => null],
            4 => ['label' => 'Viện phí & Đơn thuốc', 'state' => 'pending', 'time' => null],
            5 => ['label' => 'Hoàn tất & Cấp phát thuốc', 'state' => 'pending', 'time' => null],
        ];

        if ($appointment->status === Appointment::STATUS_CANCELLED) {
            $currentStep = 0;
        } elseif ($record && $record->dispensed_at) {
            $currentStep = 5;
            $stepStatus[2]['state'] = 'completed';
            $stepStatus[3]['state'] = 'completed';
            $stepStatus[4]['state'] = 'completed';
            $stepStatus[5]['state'] = 'completed';
            $stepStatus[5]['time'] = $record->dispensed_at->format('H:i d/m/Y');
        } elseif ($record) {
            $currentStep = 4;
            $stepStatus[2]['state'] = 'completed';
            $stepStatus[3]['state'] = 'completed';
            $stepStatus[4]['state'] = 'active';
            $stepStatus[3]['time'] = $record->created_at->format('H:i d/m/Y');
        } elseif ($appointment->called_at) {
            $currentStep = 3;
            $stepStatus[2]['state'] = 'completed';
            $stepStatus[3]['state'] = 'active';
            $stepStatus[2]['time'] = $appointment->called_at->format('H:i d/m/Y');
        } elseif ($appointment->status === Appointment::STATUS_CONFIRMED) {
            $currentStep = 2;
            $stepStatus[2]['state'] = 'active';
        }

        return [
            'appointment' => $appointment,
            'patient' => $patient,
            'doctor' => $doctor,
            'specialty' => $appointment->specialty ?? $doctor?->specialty,
            'current_step' => $currentStep,
            'step_status' => $stepStatus,
            'people_ahead' => $peopleAhead,
            'estimated_wait_minutes' => $estimatedWaitMinutes,
            'medical_record' => $record,
            'invoices' => $invoices,
            'notifications' => $appointment->notifications()->latest()->get(),
        ];
    }

    /**
     * Gửi tin nhắn SMS / Zalo ZNS cảnh báo sắp đến lượt khám
     */
    public function dispatchQueueAlert(Appointment $appointment, int $turnsRemaining = 2): PatientNotification
    {
        $patient = $appointment->patientProfile;
        $doctorName = $appointment->doctor ? $appointment->doctor->user->name : 'Bác sĩ chuyên khoa';
        $specialtyName = $appointment->specialty ? $appointment->specialty->name : 'Khoa Khám Bệnh';
        $room = $appointment->doctor ? $appointment->doctor->room_number : 'P.Khám tiếp nhận';

        $phone = $patient->phone ?: '0900000000';
        $title = "🔔 [TÂM ANH] Sắp đến lượt khám của Quý khách";
        $content = "Kính gửi Ông/Bà {$patient->full_name}. Hiện tại chỉ còn {$turnsRemaining} lượt nữa là đến STT của Quý khách tại {$specialtyName} (Phòng: {$room} - BS: {$doctorName}). Xin vui lòng di chuyển đến cửa phòng khám để chuẩn bị vào khám.";

        return PatientNotification::create([
            'appointment_id' => $appointment->id,
            'patient_profile_id' => $patient->id,
            'channel' => PatientNotification::CHANNEL_ZALO_ZNS,
            'notification_type' => PatientNotification::TYPE_QUEUE_ALERT,
            'recipient_phone' => $phone,
            'title' => $title,
            'message_content' => $content,
            'status' => PatientNotification::STATUS_SENT,
            'sent_at' => Carbon::now(),
        ]);
    }

    /**
     * Gửi thông báo khi đơn thuốc đã sẵn sàng lấy tại quầy dược
     */
    public function dispatchPrescriptionReadyAlert(Appointment $appointment): PatientNotification
    {
        $patient = $appointment->patientProfile;
        $phone = $patient->phone ?: '0900000000';
        $title = "💊 [TÂM ANH] Thuốc của Quý khách đã sẵn sàng nhận";
        $content = "Kính gửi Ông/Bà {$patient->full_name}. Bác sĩ đã kê đơn thuốc hoàn tất cho mã khám {$appointment->appointment_code}. Quý khách vui lòng đến Quầy Dược Ngoại Trú (Tầng 1) xuất trình mã bệnh nhân để nhận thuốc và hướng dẫn sử dụng.";

        return PatientNotification::create([
            'appointment_id' => $appointment->id,
            'patient_profile_id' => $patient->id,
            'channel' => PatientNotification::CHANNEL_ZALO_ZNS,
            'notification_type' => PatientNotification::TYPE_PRESCRIPTION_READY,
            'recipient_phone' => $phone,
            'title' => $title,
            'message_content' => $content,
            'status' => PatientNotification::STATUS_SENT,
            'sent_at' => Carbon::now(),
        ]);
    }
}
