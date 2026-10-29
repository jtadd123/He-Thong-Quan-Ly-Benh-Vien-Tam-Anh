<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ClinicalConsultation;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\PatientProfile;
use App\Models\Specialty;
use App\Services\ClinicalConsultationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ClinicalConsultationController extends Controller
{
    public function __construct(
        protected ClinicalConsultationService $consultationService
    ) {}

    public function index(Request $request): View
    {
        $query = ClinicalConsultation::with([
            'patientProfile',
            'requestingUser',
            'requestingSpecialty',
            'targetSpecialty',
            'targetDoctor.user',
            'consultantDoctor.user',
        ]);

        if ($request->filled('urgency')) {
            $query->where('urgency_level', $request->urgency);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('specialty')) {
            $query->where('target_specialty_id', $request->specialty);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('consultation_code', 'like', "%{$search}%")
                  ->orWhereHas('patientProfile', function ($pq) use ($search) {
                      $pq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('patient_code', 'like', "%{$search}%");
                  });
            });
        }

        $consultations = $query->orderByRaw("FIELD(urgency_level, 'stat', 'urgent', 'routine')")
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => ClinicalConsultation::count(),
            'stat' => ClinicalConsultation::where('urgency_level', ClinicalConsultation::URGENCY_STAT)->where('status', ClinicalConsultation::STATUS_REQUESTED)->count(),
            'pending' => ClinicalConsultation::where('status', ClinicalConsultation::STATUS_REQUESTED)->count(),
            'completed' => ClinicalConsultation::where('status', ClinicalConsultation::STATUS_COMPLETED)->count(),
        ];

        $specialties = Specialty::where('is_active', true)->get();

        return view('consultations.index', compact('consultations', 'stats', 'specialties'));
    }

    public function create(Request $request): View
    {
        $patient = null;
        $appointment = null;
        $medicalRecord = null;

        if ($request->filled('appointment_id')) {
            $appointment = Appointment::with(['patientProfile.latestVital', 'doctor.specialty'])->findOrFail($request->appointment_id);
            $patient = $appointment->patientProfile;
            $medicalRecord = $appointment->medicalRecord;
        } elseif ($request->filled('patient_id')) {
            $patient = PatientProfile::with('latestVital')->findOrFail($request->patient_id);
        }

        $patients = PatientProfile::latest()->take(30)->get();
        $specialties = Specialty::where('is_active', true)->get();
        $doctors = Doctor::with('user')->get();

        return view('consultations.create', compact('patient', 'appointment', 'medicalRecord', 'patients', 'specialties', 'doctors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_profile_id' => 'required|exists:patient_profiles,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'medical_record_id' => 'nullable|exists:medical_records,id',
            'target_specialty_id' => 'required|exists:specialties,id',
            'target_doctor_id' => 'nullable|exists:doctors,id',
            'requesting_specialty_id' => 'nullable|exists:specialties,id',
            'urgency_level' => 'required|in:stat,urgent,routine',
            'reason' => 'required|string|max:1000',
            'clinical_summary' => 'nullable|string|max:2000',
            'proposed_questions' => 'required|string|max:1000',
        ]);

        $consultation = $this->consultationService->requestConsultation($validated, Auth::id());

        return redirect()->route('consultations.show', $consultation)
            ->with('success', "Đã tạo phiếu yêu cầu hội chẩn {$consultation->consultation_code} thành công!");
    }

    public function show(ClinicalConsultation $consultation): View
    {
        $consultation->load([
            'patientProfile.latestVital',
            'appointment.specialty',
            'appointment.doctor.user',
            'medicalRecord.prescriptions',
            'requestingUser',
            'requestingSpecialty',
            'targetSpecialty',
            'targetDoctor.user',
            'consultantDoctor.user',
        ]);

        return view('consultations.show', compact('consultation'));
    }

    public function respond(Request $request, ClinicalConsultation $consultation): RedirectResponse
    {
        $validated = $request->validate([
            'consultation_result' => 'required|string|max:2000',
            'treatment_recommendations' => 'nullable|string|max:2000',
        ]);

        // Xác định bác sĩ hội chẩn
        $consultantDoctorId = null;
        if (Auth::user()->isDoctor()) {
            $doctor = Doctor::where('user_id', Auth::id())->first();
            $consultantDoctorId = $doctor?->id;
        }

        $this->consultationService->respondConsultation($consultation, $validated, $consultantDoctorId);

        return back()->with('success', "Đã ghi nhận kết luận hội chẩn chuyên khoa cho mã {$consultation->consultation_code}!");
    }

    public function print(ClinicalConsultation $consultation): View
    {
        $consultation->load([
            'patientProfile.latestVital',
            'requestingUser',
            'requestingSpecialty',
            'targetSpecialty',
            'targetDoctor.user',
            'consultantDoctor.user',
        ]);

        return view('consultations.print', compact('consultation'));
    }
}
