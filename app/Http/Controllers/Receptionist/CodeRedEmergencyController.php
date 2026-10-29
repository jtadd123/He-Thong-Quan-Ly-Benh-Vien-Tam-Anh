<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\EmergencyCodeRed;
use App\Models\PatientProfile;
use App\Services\CodeRedEmergencyService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CodeRedEmergencyController extends Controller
{
    public function __construct(
        protected CodeRedEmergencyService $codeRedService
    ) {}

    public function index(Request $request): View
    {
        $query = EmergencyCodeRed::with(['patientProfile', 'activatedBy'])->latest('activated_at');

        if ($request->filled('type')) {
            $query->where('emergency_type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $codeReds = $query->paginate(15)->withQueryString();

        // Các ca báo động đỏ đang còn hiệu lực khẩn cấp
        $activeAlerts = EmergencyCodeRed::whereIn('status', [
            EmergencyCodeRed::STATUS_EN_ROUTE,
            EmergencyCodeRed::STATUS_ARRIVED,
            EmergencyCodeRed::STATUS_IN_INTERVENTION,
        ])->orderBy('activated_at', 'desc')->get();

        $stats = [
            'active_count' => $activeAlerts->count(),
            'today_total' => EmergencyCodeRed::whereDate('activated_at', Carbon::today())->count(),
            'stroke_count' => EmergencyCodeRed::where('emergency_type', EmergencyCodeRed::TYPE_STROKE)->count(),
            'stemi_count' => EmergencyCodeRed::where('emergency_type', EmergencyCodeRed::TYPE_STEMI)->count(),
        ];

        $patients = PatientProfile::latest()->take(30)->get();

        return view('receptionist.code_red.index', compact('codeReds', 'activeAlerts', 'stats', 'patients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_name' => 'required|string|max:150',
            'estimated_age' => 'nullable|string|max:50',
            'gender' => 'required|in:male,female,unknown',
            'patient_profile_id' => 'nullable|exists:patient_profiles,id',
            'ambulance_unit' => 'required|string|max:100',
            'paramedic_in_charge' => 'nullable|string|max:100',
            'paramedic_phone' => 'nullable|string|max:20',
            'eta_minutes' => 'required|integer|min:1|max:120',
            'emergency_type' => 'required|in:stroke,stemi,trauma,cardiac_arrest,anaphylaxis',
            'clinical_condition' => 'required|string|max:1000',
            'blood_pressure' => 'nullable|string|max:50',
            'pulse' => 'nullable|string|max:50',
            'spo2' => 'nullable|string|max:50',
            'glasgow' => 'nullable|string|max:50',
        ]);

        $codeRed = $this->codeRedService->activateCodeRed($validated, Auth::id());

        return redirect()->route('reception.code-red.index')
            ->with('success', "ĐÃ PHÁT LỆNH BÁO ĐỘNG ĐỎ TOÀN VIỆN ({$codeRed->code_red_number}) - TOÀN BỘ KÍP TRỰC ĐÃ ĐƯỢC HUY ĐỘNG!");
    }

    public function markArrived(EmergencyCodeRed $codeRed): RedirectResponse
    {
        $this->codeRedService->markArrived($codeRed);
        return back()->with('success', "Đã ghi nhận xe cấp cứu cập sảnh cấp cứu (Door Time) cho ca {$codeRed->code_red_number}!");
    }

    public function markIntervention(EmergencyCodeRed $codeRed): RedirectResponse
    {
        $this->codeRedService->startIntervention($codeRed);
        return back()->with('success', "Đã bắt đầu can thiệp khẩn cấp (Cathlab/OR/CT) cho ca {$codeRed->code_red_number}!");
    }

    public function resolve(Request $request, EmergencyCodeRed $codeRed): RedirectResponse
    {
        $notes = $request->input('outcome_notes', 'Đã can thiệp thành công, sinh hiệu qua cơn nguy kịch, chuyển về ICU theo dõi.');
        $this->codeRedService->resolveCodeRed($codeRed, $notes);

        return back()->with('success', "Đã hoàn tất xử trí thành công ca Báo Động Đỏ {$codeRed->code_red_number}!");
    }
}
