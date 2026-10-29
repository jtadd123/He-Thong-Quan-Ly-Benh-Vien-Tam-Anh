<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\BloodTransfusion;
use App\Models\BloodUnit;
use App\Models\Doctor;
use App\Models\PatientProfile;
use App\Models\User;
use App\Services\BloodTransfusionService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BloodTransfusionController extends Controller
{
    public function __construct(
        protected BloodTransfusionService $transfusionService
    ) {}

    public function index(Request $request): View
    {
        $query = BloodTransfusion::with(['patientProfile', 'bloodUnit', 'doctor.user', 'nurse1', 'nurse2'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transfusions = $query->paginate(15)->withQueryString();

        $bloodStock = [
            'total_units' => BloodUnit::where('status', BloodUnit::STATUS_IN_STOCK)->count(),
            'o_units' => BloodUnit::where('status', BloodUnit::STATUS_IN_STOCK)->where('blood_type_abo', 'O')->count(),
            'a_units' => BloodUnit::where('status', BloodUnit::STATUS_IN_STOCK)->where('blood_type_abo', 'A')->count(),
            'b_units' => BloodUnit::where('status', BloodUnit::STATUS_IN_STOCK)->where('blood_type_abo', 'B')->count(),
            'ab_units' => BloodUnit::where('status', BloodUnit::STATUS_IN_STOCK)->where('blood_type_abo', 'AB')->count(),
            'rh_neg' => BloodUnit::where('status', BloodUnit::STATUS_IN_STOCK)->where('blood_type_rh', 'negative')->count(),
        ];

        return view('receptionist.blood_transfusions.index', compact('transfusions', 'bloodStock'));
    }

    public function create(): View
    {
        $availableUnits = BloodUnit::where('status', BloodUnit::STATUS_IN_STOCK)->orderBy('expiry_date')->get();
        $patients = PatientProfile::latest()->take(30)->get();
        $nurses = User::whereIn('role', [User::ROLE_RECEPTIONIST, User::ROLE_ADMIN])->get();
        $doctors = Doctor::with('user')->get();

        return view('receptionist.blood_transfusions.create', compact('availableUnits', 'patients', 'nurses', 'doctors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_profile_id' => 'required|exists:patient_profiles,id',
            'blood_unit_id' => 'required|exists:blood_units,id',
            'indication' => 'required|string|max:255',
            'patient_abo' => 'required|in:A,B,AB,O',
            'patient_rh' => 'required|in:positive,negative',
            'nurse_user_id_1' => 'required|exists:users,id',
            'nurse_user_id_2' => 'required|exists:users,id|different:nurse_user_id_1',
            'doctor_id' => 'nullable|exists:doctors,id',
            'blood_pressure' => 'nullable|string',
            'heart_rate' => 'nullable|string',
            'temperature' => 'nullable|string',
        ]);

        $vitals = [
            'blood_pressure' => $request->input('blood_pressure', '120/80'),
            'heart_rate' => $request->input('heart_rate', '80'),
            'temperature' => $request->input('temperature', '36.8'),
        ];
        $validated['vitals_baseline'] = $vitals;

        try {
            $transfusion = $this->transfusionService->prepareTransfusion($validated);
            return redirect()->route('reception.blood-transfusions.show', $transfusion)
                ->with('success', "Đã xác nhận kiểm tra an toàn và chuẩn bị ca truyền máu {$transfusion->transfusion_code} thành công!");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(BloodTransfusion $transfusion): View
    {
        $transfusion->load(['patientProfile', 'bloodUnit', 'doctor.user', 'nurse1', 'nurse2']);
        return view('receptionist.blood_transfusions.show', compact('transfusion'));
    }

    public function start(BloodTransfusion $transfusion): RedirectResponse
    {
        $this->transfusionService->startTransfusion($transfusion);
        return back()->with('success', "Đã bắt đầu truyền máu cho người bệnh {$transfusion->patientProfile->full_name}. Vui lòng theo dõi sát sinh hiệu trong 15 phút đầu!");
    }

    public function recordVitals(Request $request, BloodTransfusion $transfusion): RedirectResponse
    {
        $validated = $request->validate([
            'bp_15' => 'required|string',
            'hr_15' => 'required|string',
            'temp_15' => 'required|string',
        ]);

        $vitals = [
            'blood_pressure' => $validated['bp_15'],
            'heart_rate' => $validated['hr_15'],
            'temperature' => $validated['temp_15'],
            'noted_at' => now()->format('H:i d/m/Y'),
        ];

        $this->transfusionService->record15MinVitals($transfusion, $vitals);
        return back()->with('success', "Đã ghi nhận sinh hiệu phút 15 cho ca truyền máu {$transfusion->transfusion_code}!");
    }

    public function complete(BloodTransfusion $transfusion): RedirectResponse
    {
        $this->transfusionService->completeTransfusion($transfusion);
        return back()->with('success', "Đã hoàn tất ca truyền máu an toàn cho người bệnh {$transfusion->patientProfile->full_name}!");
    }

    public function reaction(Request $request, BloodTransfusion $transfusion): RedirectResponse
    {
        $validated = $request->validate([
            'reaction_details' => 'required|string|max:1000',
        ]);

        $this->transfusionService->reportReaction($transfusion, $validated['reaction_details']);
        return back()->with('warning', "Đã đình chỉ ca truyền máu và ghi nhận biên bản phản ứng tai biến truyền máu!");
    }
}
