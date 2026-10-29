<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\PatientJourneyService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientJourneyController extends Controller
{
    public function __construct(
        protected PatientJourneyService $journeyService
    ) {}

    /**
     * Tra cứu hành trình khám theo mã cuộc hẹn (hoặc quét QR từ phiếu khám)
     */
    public function show(string $appointment_code): View
    {
        $appointment = Appointment::where('appointment_code', $appointment_code)->firstOrFail();
        $journeyData = $this->journeyService->getJourneyData($appointment);

        return view('public.patient_journey', $journeyData);
    }
}
