<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\PatientSurvey;
use App\Services\PatientSurveyService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReceptionSurveyController extends Controller
{
    public function __construct(
        protected PatientSurveyService $surveyService
    ) {}

    public function index(Request $request): View
    {
        $query = PatientSurvey::with(['patientProfile', 'appointment.doctor.user'])->latest();

        if ($request->filled('followup')) {
            if ($request->followup === 'yes') {
                $query->where('requires_followup', true);
            } elseif ($request->followup === 'pending') {
                $query->where('requires_followup', true)->where('followup_status', PatientSurvey::STATUS_PENDING);
            }
        }

        $surveys = $query->paginate(15)->withQueryString();

        // Tính toán các chỉ số CSAT & NPS chuẩn Bộ Y Tế
        $totalCount = PatientSurvey::count();
        $avgOverall = $totalCount > 0 ? round(PatientSurvey::avg('overall_score'), 2) : 5.0;
        $avgReception = $totalCount > 0 ? round(PatientSurvey::avg('rating_reception'), 2) : 5.0;
        $avgDoctor = $totalCount > 0 ? round(PatientSurvey::avg('rating_doctor'), 2) : 5.0;
        $avgFacilities = $totalCount > 0 ? round(PatientSurvey::avg('rating_facilities'), 2) : 5.0;
        $avgWaiting = $totalCount > 0 ? round(PatientSurvey::avg('rating_waiting_time'), 2) : 5.0;

        // NPS Calculation: % Promoters (9-10) - % Detractors (0-6)
        $promoters = PatientSurvey::where('nps_score', '>=', 9)->count();
        $detractors = PatientSurvey::where('nps_score', '<=', 6)->count();
        $nps = $totalCount > 0 ? round((($promoters - $detractors) / $totalCount) * 100) : 100;

        $stats = [
            'total' => $totalCount,
            'avg_overall' => $avgOverall,
            'avg_reception' => $avgReception,
            'avg_doctor' => $avgDoctor,
            'avg_facilities' => $avgFacilities,
            'avg_waiting' => $avgWaiting,
            'nps' => $nps,
            'pending_followups' => PatientSurvey::where('requires_followup', true)->where('followup_status', PatientSurvey::STATUS_PENDING)->count(),
        ];

        return view('receptionist.surveys.index', compact('surveys', 'stats'));
    }

    public function resolveFollowup(Request $request, PatientSurvey $survey): RedirectResponse
    {
        $validated = $request->validate([
            'followup_notes' => 'required|string|max:1000',
        ]);

        $this->surveyService->resolveFollowup($survey, $validated['followup_notes'], Auth::id());

        return back()->with('success', "Đã ghi nhận kết quả xử lý hỗ trợ người bệnh cho mã khảo sát {$survey->survey_code}!");
    }
}
