<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\DietRegimen;
use App\Models\Doctor;
use App\Models\NutritionAssessment;
use App\Models\PatientProfile;
use App\Services\ClinicalNutritionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ClinicalNutritionController extends Controller
{
    public function __construct(
        protected ClinicalNutritionService $nutritionService
    ) {}

    public function index(Request $request): View
    {
        $query = NutritionAssessment::with(['patientProfile', 'dietRegimen', 'doctor.user', 'consultant'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('assessment_code', 'like', "%{$search}%")
                  ->orWhereHas('patientProfile', function ($pq) use ($search) {
                      $pq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('patient_code', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $assessments = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => NutritionAssessment::count(),
            'diabetes_diet' => NutritionAssessment::whereHas('dietRegimen', function ($q) {
                $q->where('code', 'DIET-DIABETES');
            })->count(),
            'dash_diet' => NutritionAssessment::whereHas('dietRegimen', function ($q) {
                $q->where('code', 'DIET-DASH-HYPERTENSION');
            })->count(),
            'gout_diet' => NutritionAssessment::whereHas('dietRegimen', function ($q) {
                $q->where('code', 'DIET-GOUT');
            })->count(),
            'regimens_count' => DietRegimen::count(),
        ];

        $regimens = DietRegimen::all();

        return view('receptionist.nutrition.index', compact('assessments', 'stats', 'regimens'));
    }

    public function create(): View
    {
        $patients = PatientProfile::latest()->take(30)->get();
        $doctors = Doctor::with('user')->get();
        $regimens = DietRegimen::all();

        return view('receptionist.nutrition.create', compact('patients', 'doctors', 'regimens'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_profile_id' => 'required|exists:patient_profiles,id',
            'height_cm' => 'required|numeric|min:50|max:250',
            'weight_kg' => 'required|numeric|min:3|max:300',
            'diet_regimen_id' => 'nullable|exists:diet_regimens,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'custom_instructions' => 'nullable|string|max:1000',
        ]);

        $assessment = $this->nutritionService->createAssessment($validated, Auth::id());

        return redirect()->route('reception.nutrition.show', $assessment)
            ->with('success', "Đã hoàn tất đánh giá dinh dưỡng {$assessment->assessment_code} (BMI: {$assessment->bmi} - {$assessment->bmi_category})!");
    }

    public function show(NutritionAssessment $assessment): View
    {
        $assessment->load(['patientProfile', 'dietRegimen', 'doctor.user', 'consultant']);
        return view('receptionist.nutrition.show', compact('assessment'));
    }

    public function printPlan(NutritionAssessment $assessment): View
    {
        $assessment->load(['patientProfile', 'dietRegimen', 'doctor.user', 'consultant']);
        return view('receptionist.nutrition.print_plan', compact('assessment'));
    }
}
