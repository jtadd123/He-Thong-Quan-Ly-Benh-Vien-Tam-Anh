<?php

namespace App\Services;

use App\Models\MedicalRecord;
use Carbon\Carbon;
use Illuminate\Support\Str;

class MedicationScheduleService
{
    /**
     * Phân tích và cấu trúc hóa thời gian biểu dùng thuốc 4 buổi/ngày chuẩn y khoa.
     *
     * @param MedicalRecord $record
     * @return array
     */
    public function parseDailySchedule(MedicalRecord $record): array
    {
        $prescriptions = $record->prescriptions;

        $sessions = [
            'morning' => [
                'id' => 'morning',
                'name' => 'Buổi Sáng',
                'time' => '07:00 - 08:00',
                'hour' => 7,
                'minute' => 0,
                'icon' => 'bi-brightness-alt-high-fill',
                'color' => 'warning',
                'bg_color' => 'warning-subtle',
                'description' => 'Uống sau bữa ăn sáng 15 - 30 phút',
                'items' => [],
            ],
            'noon' => [
                'id' => 'noon',
                'name' => 'Buổi Trưa',
                'time' => '11:30 - 12:30',
                'hour' => 12,
                'minute' => 0,
                'icon' => 'bi-sun-fill',
                'color' => 'danger',
                'bg_color' => 'danger-subtle',
                'description' => 'Uống sau bữa ăn trưa no',
                'items' => [],
            ],
            'evening' => [
                'id' => 'evening',
                'name' => 'Buổi Chiều / Tối',
                'time' => '17:30 - 18:30',
                'hour' => 18,
                'minute' => 0,
                'icon' => 'bi-sunset-fill',
                'color' => 'primary',
                'bg_color' => 'primary-subtle',
                'description' => 'Uống sau bữa ăn chiều hoặc ăn tối',
                'items' => [],
            ],
            'bedtime' => [
                'id' => 'bedtime',
                'name' => 'Trước Khi Đi Ngủ',
                'time' => '21:00 - 21:30',
                'hour' => 21,
                'minute' => 0,
                'icon' => 'bi-moon-stars-fill',
                'color' => 'dark',
                'bg_color' => 'secondary-subtle',
                'description' => 'Uống khoảng 30 phút trước khi đi ngủ',
                'items' => [],
            ],
        ];

        foreach ($prescriptions as $p) {
            $freq = Str::lower($p->frequency ?? '');
            $inst = Str::lower($p->instructions ?? '');
            $combined = $freq . ' ' . $inst;

            $assignedSessions = [];

            // 1. Kiểm tra từ khóa cụ thể từng buổi
            $hasMorning = str_contains($combined, 'sáng') || str_contains($combined, 'sang') || str_contains($combined, 'morning');
            $hasNoon = str_contains($combined, 'trưa') || str_contains($combined, 'trua') || str_contains($combined, 'noon');
            $hasEvening = str_contains($combined, 'chiều') || str_contains($combined, 'chieu') || str_contains($combined, 'tối') || str_contains($combined, 'toi') || str_contains($combined, 'evening');
            $hasBedtime = str_contains($combined, 'ngủ') || str_contains($combined, 'ngu') || str_contains($combined, 'bedtime');

            if ($hasMorning) $assignedSessions[] = 'morning';
            if ($hasNoon) $assignedSessions[] = 'noon';
            if ($hasEvening) $assignedSessions[] = 'evening';
            if ($hasBedtime) $assignedSessions[] = 'bedtime';

            // 2. Nếu không có từ khóa cụ thể, xét theo số lần uống mỗi ngày
            if (empty($assignedSessions)) {
                if (str_contains($combined, '4 lần') || str_contains($combined, '4 lan')) {
                    $assignedSessions = ['morning', 'noon', 'evening', 'bedtime'];
                } elseif (str_contains($combined, '3 lần') || str_contains($combined, '3 lan')) {
                    $assignedSessions = ['morning', 'noon', 'evening'];
                } elseif (str_contains($combined, '2 lần') || str_contains($combined, '2 lan')) {
                    $assignedSessions = ['morning', 'evening'];
                } else {
                    // Mặc định uống buổi sáng
                    $assignedSessions = ['morning'];
                }
            }

            $item = [
                'id' => $p->id,
                'medication_name' => $p->medication_name,
                'dosage' => $p->dosage,
                'frequency' => $p->frequency,
                'duration_days' => $p->duration_days ?: 7,
                'instructions' => $p->instructions ?: 'Uống với nước đun sôi để nguội',
            ];

            foreach (array_unique($assignedSessions) as $sessKey) {
                if (isset($sessions[$sessKey])) {
                    $sessions[$sessKey]['items'][] = $item;
                }
            }
        }

        // Đếm tổng số cữ uống
        $activeSessionsCount = 0;
        foreach ($sessions as $key => $s) {
            if (!empty($s['items'])) {
                $activeSessionsCount++;
            }
        }

        return [
            'sessions' => $sessions,
            'active_sessions_count' => $activeSessionsCount,
            'total_medications' => $prescriptions->count(),
            'max_duration_days' => $prescriptions->max('duration_days') ?? 7,
        ];
    }

    /**
     * Tính toán ngày tái khám thông minh dựa trên phác đồ dùng thuốc.
     *
     * @param MedicalRecord $record
     * @return array
     */
    public function calculateFollowUpRecall(MedicalRecord $record): array
    {
        $maxDurationDays = $record->prescriptions->max('duration_days') ?: 7;

        // Nếu bác sĩ đã chỉ định ngày tái khám cụ thể
        if ($record->follow_up_date) {
            $date = Carbon::parse($record->follow_up_date);
            return [
                'date' => $date,
                'formatted' => $date->format('d/m/Y'),
                'day_of_week' => $this->getVietnameseDayOfWeek($date),
                'days_left' => (int) ceil(now()->diffInDays($date, false)),
                'source' => 'doctor',
                'explanation' => 'Ngày tái khám do Bác sĩ phụ trách ' . ($record->doctor ? $record->doctor->full_name_with_title : '') . ' chỉ định.',
            ];
        }

        // Tính toán tự động theo ngày cấp phát hoặc ngày tạo bệnh án + thời gian dùng thuốc
        $baseDate = $record->dispensed_at ? Carbon::parse($record->dispensed_at) : ($record->created_at ? Carbon::parse($record->created_at) : Carbon::today());
        $calculatedDate = $baseDate->copy()->addDays($maxDurationDays);

        // Kiểm tra nếu rơi vào Chủ Nhật -> Đề xuất chuyển sang Thứ Hai hoặc Thứ Bảy
        $note = "Tính toán tự động theo đợt dùng thuốc {$maxDurationDays} ngày.";
        if ($calculatedDate->isSunday()) {
            $note .= " (Khuyên dời sang Thứ Bảy hoặc Thứ Hai kế tiếp do Bệnh viện làm việc ngoài giờ vào Chủ Nhật).";
        }

        return [
            'date' => $calculatedDate,
            'formatted' => $calculatedDate->format('d/m/Y'),
            'day_of_week' => $this->getVietnameseDayOfWeek($calculatedDate),
            'days_left' => (int) ceil(now()->diffInDays($calculatedDate, false)),
            'source' => 'automatic',
            'explanation' => $note,
        ];
    }

    /**
     * Tạo chuỗi tệp iCalendar chuẩn RFC 5545 để người bệnh import vào Google/Apple Calendar.
     *
     * @param MedicalRecord $record
     * @return string
     */
    public function generateIcalendar(MedicalRecord $record): string
    {
        $schedule = $this->parseDailySchedule($record);
        $patient = $record->patientProfile;
        $doctor = $record->doctor;

        $startDate = $record->dispensed_at ? Carbon::parse($record->dispensed_at) : Carbon::today();
        $timestamp = gmdate('Ymd\THis\Z');

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//BV Da Khoa Tam Anh//He Thong Quan Ly Y Te//VI',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:Lich Uong Thuoc - ' . $this->escapeIcal($patient->full_name) . ' (' . $record->record_code . ')',
            'X-WR-TIMEZONE:Asia/Ho_Chi_Minh',
        ];

        foreach ($schedule['sessions'] as $sKey => $session) {
            if (empty($session['items'])) {
                continue;
            }

            // Tìm số ngày dùng thuốc lớn nhất trong buổi này
            $maxDaysInSession = 1;
            $drugList = [];
            foreach ($session['items'] as $item) {
                $maxDaysInSession = max($maxDaysInSession, $item['duration_days']);
                $drugList[] = "- " . $item['medication_name'] . ": " . $item['dosage'] . " (" . $item['instructions'] . ")";
            }

            $dtStart = $startDate->copy()->setTime($session['hour'], $session['minute'], 0)->format('Ymd\THis');
            $dtEnd = $startDate->copy()->setTime($session['hour'], $session['minute'] + 15, 0)->format('Ymd\THis');

            $uid = "med-rx-{$record->id}-{$sKey}-" . Str::random(8) . "@tamanhhospital.vn";
            $summary = "💊 [TÂM ANH] Nhắc uống thuốc " . $session['name'] . " - " . $patient->full_name;

            $description = "LỊCH DÙNG THUỐC BỆNH VIỆN ĐA KHOA TÂM ANH\\n"
                . "Hồ sơ: " . $record->record_code . "\\n"
                . "Bệnh nhân: " . $patient->full_name . " (Mã: " . $patient->patient_code . ")\\n"
                . "Chẩn đoán: " . ($record->diagnosis ?: 'Đơn thuốc ngoại trú') . "\\n"
                . "Bác sĩ kê: " . ($doctor ? $doctor->full_name_with_title : 'Bác sĩ Tâm Anh') . "\\n"
                . "---------------------------------\\n"
                . "DANH SÁCH THUỐC " . mb_strtoupper($session['name'], 'UTF-8') . ":\\n"
                . implode("\\n", $drugList) . "\\n"
                . "---------------------------------\\n"
                . "Lời dặn: " . $session['description'] . "\\n"
                . "Tổng đài CSKH & Cấp cứu: 1800 6858";

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:' . $uid;
            $lines[] = 'DTSTAMP:' . $timestamp;
            $lines[] = 'DTSTART;TZID=Asia/Ho_Chi_Minh:' . $dtStart;
            $lines[] = 'DTEND;TZID=Asia/Ho_Chi_Minh:' . $dtEnd;
            $lines[] = 'RRULE:FREQ=DAILY;COUNT=' . $maxDaysInSession;
            $lines[] = 'SUMMARY:' . $this->escapeIcal($summary);
            $lines[] = 'DESCRIPTION:' . $description;
            $lines[] = 'LOCATION:Bệnh Viện Đa Khoa Tâm Anh';
            $lines[] = 'STATUS:CONFIRMED';
            
            // Alarm notification 15 minutes before
            $lines[] = 'BEGIN:VALARM';
            $lines[] = 'TRIGGER:-PT15M';
            $lines[] = 'ACTION:DISPLAY';
            $lines[] = 'DESCRIPTION:Nhắc nhở: Sắp đến giờ uống thuốc ' . $session['name'] . ' theo chỉ định Bác sĩ BV Tâm Anh!';
            $lines[] = 'END:VALARM';
            
            $lines[] = 'END:VEVENT';
        }

        // Thêm sự kiện nhắc Tái Khám (Follow-up Appointment)
        $followUp = $this->calculateFollowUpRecall($record);
        if ($followUp['date']) {
            $followUpDateStr = $followUp['date']->format('Ymd');
            $followUpUid = "recall-rx-{$record->id}-" . Str::random(8) . "@tamanhhospital.vn";
            $followUpSummary = "🏥 [TÂM ANH] TÁI KHÁM THEO HẸN - " . $patient->full_name;
            $followUpDesc = "LỊCH TÁI KHÁM ĐỊNH KỲ BỆNH VIỆN TÂM ANH\\n"
                . "Bệnh nhân: " . $patient->full_name . "\\n"
                . "Bác sĩ khám: " . ($doctor ? $doctor->full_name_with_title : 'Khoa Khám Bệnh') . "\\n"
                . "Ghi chú: " . $followUp['explanation'] . "\\n"
                . "Vui lòng mang theo đơn thuốc và hồ sơ bệnh án cũ khi đến tái khám.\\n"
                . "Đặt hẹn trước: 1800 6858";

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:' . $followUpUid;
            $lines[] = 'DTSTAMP:' . $timestamp;
            $lines[] = 'DTSTART;VALUE=DATE:' . $followUpDateStr;
            $lines[] = 'DTEND;VALUE=DATE:' . $followUpDateStr;
            $lines[] = 'SUMMARY:' . $this->escapeIcal($followUpSummary);
            $lines[] = 'DESCRIPTION:' . $followUpDesc;
            $lines[] = 'LOCATION:Bệnh Viện Đa Khoa Tâm Anh';
            $lines[] = 'STATUS:CONFIRMED';

            // Alarm notification 1 day before
            $lines[] = 'BEGIN:VALARM';
            $lines[] = 'TRIGGER:-P1D';
            $lines[] = 'ACTION:DISPLAY';
            $lines[] = 'DESCRIPTION:Ngày mai bạn có lịch tái khám tại Bệnh Viện Tâm Anh!';
            $lines[] = 'END:VALARM';

            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines);
    }

    private function escapeIcal(string $text): string
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace(',', '\,', $text);
        $text = str_replace(';', '\;', $text);
        return $text;
    }

    private function getVietnameseDayOfWeek(Carbon $date): string
    {
        return match ($date->dayOfWeek) {
            0 => 'Chủ Nhật',
            1 => 'Thứ Hai',
            2 => 'Thứ Ba',
            3 => 'Thứ Tư',
            4 => 'Thứ Năm',
            5 => 'Thứ Sáu',
            6 => 'Thứ Bảy',
        };
    }
}
