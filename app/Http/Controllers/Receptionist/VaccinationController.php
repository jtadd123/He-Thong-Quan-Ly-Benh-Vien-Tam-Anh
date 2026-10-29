<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\PatientProfile;
use App\Models\VaccinationRecord;
use App\Models\Vaccine;
use App\Services\VaccinationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VaccinationController extends Controller
{
    public function __construct(
        protected VaccinationService $vaccinationService
    ) {}

    public function index(Request $request): View
    {
        $query = VaccinationRecord::with(['patientProfile', 'vaccine', 'administeredBy'])->latest('administered_at');

        if ($request->filled('status')) {
            $query->where('observation_status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('record_code', 'like', "%{$search}%")
                  ->orWhere('batch_number', 'like', "%{$search}%")
                  ->orWhereHas('patientProfile', function ($pq) use ($search) {
                      $pq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('patient_code', 'like', "%{$search}%");
                  });
            });
        }

        $records = $query->paginate(15)->withQueryString();

        // Danh sách các ca đang trong phòng theo dõi 30 phút
        $observingRecords = VaccinationRecord::with(['patientProfile', 'vaccine'])
            ->where('observation_status', VaccinationRecord::STATUS_OBSERVING)
            ->whereDate('administered_at', Carbon::today())
            ->orderBy('administered_at', 'asc')
            ->get();

        $stats = [
            'today_total' => VaccinationRecord::whereDate('administered_at', Carbon::today())->count(),
            'observing' => VaccinationRecord::where('observation_status', VaccinationRecord::STATUS_OBSERVING)->count(),
            'cleared_today' => VaccinationRecord::where('observation_status', VaccinationRecord::STATUS_CLEARED)
                ->whereDate('administered_at', Carbon::today())
                ->count(),
            'adverse_count' => VaccinationRecord::where('observation_status', VaccinationRecord::STATUS_ADVERSE)->count(),
        ];

        return view('receptionist.vaccinations.index', compact('records', 'observingRecords', 'stats'));
    }

    public function create(Request $request): View
    {
        $patient = null;
        if ($request->filled('patient_id')) {
            $patient = PatientProfile::find($request->patient_id);
        }

        $patients = PatientProfile::latest()->take(30)->get();
        $vaccines = Vaccine::where('is_active', true)->get();

        return view('receptionist.vaccinations.create', compact('patient', 'patients', 'vaccines'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_profile_id' => 'required|exists:patient_profiles,id',
            'vaccine_id' => 'required|exists:vaccines,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'dose_number' => 'required|integer|min:1|max:10',
            'batch_number' => 'required|string|max:50',
            'batch_expiry_date' => 'nullable|date',
            'injection_site' => 'required|string|max:100',
            'body_temperature' => 'required|numeric|min:35.0|max:42.0',
            'screening_notes' => 'nullable|string|max:500',
        ]);

        $record = $this->vaccinationService->administerVaccine($validated, Auth::id());

        return redirect()->route('reception.vaccinations.show', $record)
            ->with('success', "Đã ghi nhận tiêm thành công mã {$record->record_code}. Bệnh nhân đã được chuyển vào phòng theo dõi 30 phút!");
    }

    public function show(VaccinationRecord $record): View
    {
        $record->load(['patientProfile', 'vaccine', 'administeredBy']);
        return view('receptionist.vaccinations.show', compact('record'));
    }

    public function clearObservation(Request $request, VaccinationRecord $record): RedirectResponse
    {
        $notes = $request->input('notes', 'Vết tiêm bình thường, không sốt, không nổi ban, sinh hiệu ổn định.');
        $this->vaccinationService->clearObservation($record, $notes);

        return back()->with('success', "Đã hoàn tất theo dõi 30 phút an toàn cho bệnh nhân {$record->patientProfile->full_name}. Người bệnh có thể ra về!");
    }

    public function printCertificate(VaccinationRecord $record): View
    {
        $record->load(['patientProfile', 'vaccine', 'administeredBy']);
        
        // Lấy toàn bộ lịch sử tiêm chủng của bệnh nhân này
        $allRecords = VaccinationRecord::with('vaccine')
            ->where('patient_profile_id', $record->patient_profile_id)
            ->orderBy('administered_at', 'asc')
            ->get();

        return view('receptionist.vaccinations.certificate', compact('record', 'allRecords'));
    }
}
