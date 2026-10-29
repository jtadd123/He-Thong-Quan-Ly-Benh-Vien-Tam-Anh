<?php

namespace App\Services;

use App\Models\MedicalRecord;
use App\Models\Prescription;

class PrescriptionLabelService
{
    /**
     * Bảng quy tắc phát hiện cảnh báo phụ lâm sàng (Auxiliary Caution Flags)
     */
    protected const CAUTION_RULES = [
        [
            'keywords' => ['aspirin', 'ibuprofen', 'diclofenac', 'meloxicam', 'celecoxib', 'ketoprofen', 'prednisolon', 'medrol', 'dexamethasone'],
            'type' => 'after_meals',
            'badge_text' => 'UỐNG SAU KHI ĂN NO',
            'icon' => 'bi-cup-hot-fill',
            'color' => '#dc2626', // red
            'description' => 'Tránh kích ứng niêm mạc và viêm loét dạ dày tá tràng.',
        ],
        [
            'keywords' => ['paracetamol', 'panadol', 'efferalgan', 'hapacol', 'metronidazol', 'diazepam', 'klion'],
            'type' => 'no_alcohol',
            'badge_text' => 'KHÔNG RƯỢU BIA',
            'icon' => 'bi-slash-circle-fill',
            'color' => '#ea580c', // orange
            'description' => 'Tránh nguy cơ hủy hoại tế bào gan cấp tính hoặc ức chế thần kinh.',
        ],
        [
            'keywords' => ['clorpheniramin', 'chlorpheniramine', 'cetirizin', 'loratadin', 'fexofenadin', 'diazepam', 'tramadol', 'codein'],
            'type' => 'drowsiness',
            'badge_text' => 'GÂY BUỒN NGỦ - THẬN TRỌNG LÁI XE',
            'icon' => 'bi-car-front-fill',
            'color' => '#ca8a04', // yellow-amber
            'description' => 'Thuốc có thể gây ức chế phản xạ thần kinh và giảm tập trung.',
        ],
        [
            'keywords' => ['ciprofloxacin', 'doxycyclin', 'alendronat', 'cotrimoxazol', 'bactrim'],
            'type' => 'full_water',
            'badge_text' => 'UỐNG NHIỀU NƯỚC (250ML)',
            'icon' => 'bi-droplet-fill',
            'color' => '#0284c7', // blue
            'description' => 'Uống với một cốc nước đầy và giữ tư thế thẳng đứng để tránh nghẽn thực quản/sỏi thận.',
        ],
        [
            'keywords' => ['amoxicillin', 'augmentin', 'cefixim', 'cefpodoxim', 'azithromycin', 'clarithromycin'],
            'type' => 'finish_course',
            'badge_text' => 'UỐNG ĐỦ LIỆU TRÌNH KHÁNG SINH',
            'icon' => 'bi-shield-check',
            'color' => '#16a34a', // green
            'description' => 'Không được tự ý dừng thuốc khi thấy đỡ để tránh vi khuẩn kháng thuốc.',
        ],
    ];

    /**
     * Phân tích và tạo nhãn dán cho toàn bộ thuốc trong đơn
     */
    public function generateLabelsForRecord(MedicalRecord $record): array
    {
        $record->load(['appointment.patientProfile', 'appointment.doctor.specialty', 'prescriptions']);
        $patient = $record->patientProfile ?? $record->appointment->patientProfile;
        $doctor = $record->doctor ?? $record->appointment->doctor;

        $labels = [];
        foreach ($record->prescriptions as $prescription) {
            $labels[] = $this->buildSingleLabelData($record, $prescription, $patient, $doctor);
        }

        return $labels;
    }

    /**
     * Tạo dữ liệu nhãn dán cho 1 thuốc cụ thể
     */
    public function buildSingleLabelData(MedicalRecord $record, Prescription $prescription, $patient, $doctor): array
    {
        $nameLower = mb_strtolower($prescription->medication_name . ' ' . ($prescription->instructions ?? ''), 'UTF-8');

        // Phân tích liều dùng Sáng - Trưa - Chiều - Tối
        $dosing = $this->parseDosingSchedule($prescription->instructions ?? '', $prescription->frequency ?? '');

        // Trích xuất các cảnh báo phụ
        $cautions = [];
        foreach (self::CAUTION_RULES as $rule) {
            foreach ($rule['keywords'] as $kw) {
                if (str_contains($nameLower, $kw)) {
                    $cautions[] = [
                        'badge_text' => $rule['badge_text'],
                        'icon' => $rule['icon'],
                        'color' => $rule['color'],
                        'description' => $rule['description'],
                    ];
                    break;
                }
            }
        }

        // Nếu không có cảnh báo đặc biệt, mặc định cảnh báo bảo quản
        if (empty($cautions)) {
            $cautions[] = [
                'badge_text' => 'BẢO QUẢN DƯỚI 30°C - TRÁNH ÁNH SÁNG',
                'icon' => 'bi-thermometer-half',
                'color' => '#64748b',
                'description' => 'Bảo quản nơi khô ráo, tránh ẩm ướt và xa tầm tay trẻ em.',
            ];
        }

        return [
            'prescription_id' => $prescription->id,
            'hospital_name' => 'BV ĐA KHOA TÂM ANH',
            'department_name' => 'KHOA DƯỢC - NHÀ THUỐC NGOẠI TRÚ',
            'record_code' => $record->record_code,
            'patient_name' => $patient->full_name,
            'patient_code' => $patient->patient_code,
            'patient_age' => $patient->age_text,
            'patient_gender' => $patient->gender_name,
            'doctor_name' => $doctor ? $doctor->user->name : 'Bác sĩ điều trị',
            'medication_name' => $prescription->medication_name,
            'dosage' => $prescription->dosage,
            'duration_days' => $prescription->duration_days,
            'frequency' => $prescription->frequency,
            'instructions' => $prescription->instructions,
            'dosing' => $dosing,
            'cautions' => $cautions,
            'dispense_date' => $record->dispensed_at ? $record->dispensed_at->format('d/m/Y') : date('d/m/Y'),
            'qr_data' => route('public.billing.lookup', ['code' => $record->appointment->appointment_code ?? $record->record_code]),
        ];
    }

    /**
     * Tách liều dùng Sáng - Trưa - Chiều - Tối từ chuỗi hướng dẫn
     */
    protected function parseDosingSchedule(string $instructions, string $frequency): array
    {
        $text = mb_strtolower($instructions . ' ' . $frequency, 'UTF-8');

        $morning = 0;
        $noon = 0;
        $evening = 0;
        $night = 0;

        // Pattern 1: "Sáng 1 viên, Chiều 1 viên" hoặc "Sáng 1, Tối 1"
        if (preg_match('/sáng\s*[:\s-]?\s*(\d+(\.\d+)?)/u', $text, $m)) {
            $morning = (float) $m[1];
        }
        if (preg_match('/trưa\s*[:\s-]?\s*(\d+(\.\d+)?)/u', $text, $m)) {
            $noon = (float) $m[1];
        }
        if (preg_match('/chiều\s*[:\s-]?\s*(\d+(\.\d+)?)/u', $text, $m)) {
            $evening = (float) $m[1];
        }
        if (preg_match('/tối\s*[:\s-]?\s*(\d+(\.\d+)?)/u', $text, $m)) {
            $night = (float) $m[1];
        }

        // Pattern 2: Dạng "ngày 2 lần, mỗi lần 1 viên"
        if ($morning == 0 && $noon == 0 && $evening == 0 && $night == 0) {
            if (preg_match('/ngày\s*(\d+)\s*lần/u', $text, $mLan)) {
                $lan = (int) $mLan[1];
                $vien = 1;
                if (preg_match('/mỗi lần\s*(\d+(\.\d+)?)/u', $text, $mVien)) {
                    $vien = (float) $mVien[1];
                }
                if ($lan >= 1) $morning = $vien;
                if ($lan >= 2) $night = $vien;
                if ($lan >= 3) $noon = $vien;
                if ($lan >= 4) $evening = $vien;
            }
        }

        // Mặc định an toàn nếu không phân tích được
        if ($morning == 0 && $noon == 0 && $evening == 0 && $night == 0) {
            $morning = 1;
            $night = 1;
        }

        return [
            'morning' => $morning,
            'noon' => $noon,
            'evening' => $evening,
            'night' => $night,
        ];
    }
}
