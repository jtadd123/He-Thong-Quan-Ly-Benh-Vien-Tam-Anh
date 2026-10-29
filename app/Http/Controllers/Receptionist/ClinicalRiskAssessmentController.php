<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\ClinicalRiskAssessment;
use App\Models\PatientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClinicalRiskAssessmentController extends Controller
{
    public function index(Request $request)
    {
        $query = ClinicalRiskAssessment::with('patientProfile')->latest();

        if ($request->filled('assessment_type')) {
            $query->where('assessment_type', $request->assessment_type);
        }

        $assessments = $query->paginate(15);
        $patients = PatientProfile::orderBy('full_name')->limit(50)->get();

        $stats = [
            'total' => ClinicalRiskAssessment::count(),
            'morse_high_risk' => ClinicalRiskAssessment::where('assessment_type', 'morse_fall')->whereIn('risk_category', ['high', 'very_high'])->count(),
            'braden_high_risk' => ClinicalRiskAssessment::where('assessment_type', 'braden_scale')->whereIn('risk_category', ['high', 'very_high'])->count(),
        ];

        return view('receptionist.risk_assessments.index', compact('assessments', 'patients', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_profile_id' => 'required|exists:patient_profiles,id',
            'assessment_type' => 'required|in:braden_scale,morse_fall',
            'score' => 'required|integer|min:0|max:150',
            'details' => 'required|array',
            'preventive_actions' => 'nullable|array',
        ]);

        $score = (int) $validated['score'];
        $type = $validated['assessment_type'];

        if ($type === 'morse_fall') {
            if ($score >= 45) {
                $category = 'high';
            } elseif ($score >= 25) {
                $category = 'moderate';
            } else {
                $category = 'low';
            }
        } else { // braden_scale
            if ($score <= 9) {
                $category = 'very_high';
            } elseif ($score <= 12) {
                $category = 'high';
            } elseif ($score <= 14) {
                $category = 'moderate';
            } else {
                $category = 'low';
            }
        }

        ClinicalRiskAssessment::create([
            'patient_profile_id' => $validated['patient_profile_id'],
            'assessment_type' => $type,
            'score' => $score,
            'risk_category' => $category,
            'details' => $validated['details'],
            'preventive_actions' => $validated['preventive_actions'] ?? [],
            'assessed_by' => Auth::user()->name ?? 'Điều dưỡng khoa',
            'assessed_at' => now(),
        ]);

        return redirect()->route('reception.risk-assessments.index')->with('success', 'Đã lưu đánh giá nguy cơ an toàn người bệnh thành công!');
    }
}
