<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use App\Services\MedicationScheduleService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MedicationScheduleController extends Controller
{
    public function __construct(
        protected MedicationScheduleService $scheduleService
    ) {}

    /**
     * Hiển thị thời gian biểu dùng thuốc trực quan và thông tin tái khám.
     */
    public function show(MedicalRecord $record)
    {
        $record->load(['patientProfile', 'doctor', 'prescriptions']);
        
        $schedule = $this->scheduleService->parseDailySchedule($record);
        $followUp = $this->scheduleService->calculateFollowUpRecall($record);

        return view('receptionist.pharmacy.schedule', compact('record', 'schedule', 'followUp'));
    }

    /**
     * Xuất tệp iCalendar (.ics) đồng bộ Google/Apple/Outlook Calendar cho điện thoại thông minh.
     */
    public function downloadIcal(MedicalRecord $record)
    {
        $record->load(['patientProfile', 'doctor', 'prescriptions']);

        $icalContent = $this->scheduleService->generateIcalendar($record);
        $filename = "BVTamAnh_LichUongThuoc_{$record->record_code}.ics";

        return response($icalContent, Response::HTTP_OK, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
