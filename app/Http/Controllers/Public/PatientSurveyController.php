<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\PatientSurveyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientSurveyController extends Controller
{
    public function __construct(
        protected PatientSurveyService $surveyService
    ) {}

    public function create(string $appointment_code): View
    {
        $appointment = Appointment::where('appointment_code', $appointment_code)->with('patientProfile', 'doctor.specialty')->firstOrFail();
        return view('public.patient_survey', compact('appointment'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'appointment_id' => 'nullable|exists:appointments,id',
            'patient_profile_id' => 'nullable|exists:patient_profiles,id',
            'rating_reception' => 'required|integer|min:1|max:5',
            'rating_doctor' => 'required|integer|min:1|max:5',
            'rating_facilities' => 'required|integer|min:1|max:5',
            'rating_waiting_time' => 'required|integer|min:1|max:5',
            'nps_score' => 'required|integer|min:0|max:10',
            'feedback_text' => 'nullable|string|max:1000',
        ]);

        $this->surveyService->submitSurvey($validated);

        return redirect()->route('public.survey.thankyou');
    }

    public function thankyou(): View
    {
        return view('public.survey_thankyou');
    }
}
