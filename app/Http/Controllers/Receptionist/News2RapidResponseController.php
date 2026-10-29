<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\News2VitalAlert;
use App\Models\PatientProfile;
use App\Models\RrtActivation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class News2RapidResponseController extends Controller
{
    public function index(Request $request)
    {
        $query = News2VitalAlert::with(['patientProfile', 'rrtActivation'])->latest();

        if ($request->filled('risk_level')) {
            $query->where('risk_level', $request->risk_level);
        }

        $alerts = $query->paginate(15);
        $patients = PatientProfile::orderBy('full_name')->limit(50)->get();
        $rrtDispatches = RrtActivation::with('patientProfile')->latest()->limit(10)->get();

        $stats = [
            'total' => News2VitalAlert::count(),
            'high_critical' => News2VitalAlert::where('risk_level', 'high_critical')->count(),
            'medium' => News2VitalAlert::where('risk_level', 'medium')->count(),
            'rrt_active' => RrtActivation::whereIn('status', ['dispatched', 'arrived'])->count(),
        ];

        return view('receptionist.news2_rrt.index', compact('alerts', 'patients', 'rrtDispatches', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_profile_id' => 'required|exists:patient_profiles,id',
            'respiratory_rate' => 'required|integer|min:4|max:60',
            'oxygen_saturation' => 'required|integer|min:50|max:100',
            'supplemental_oxygen' => 'nullable|boolean',
            'systolic_bp' => 'required|integer|min:40|max:280',
            'pulse_rate' => 'required|integer|min:30|max:220',
            'consciousness_level' => 'required|string|in:alert,confusion,voice,pain,unresponsive',
            'temperature' => 'required|numeric|min:30|max:45',
            'clinician_notes' => 'nullable|string',
            'ward_location' => 'nullable|string',
        ]);

        $scoreResult = News2VitalAlert::calculateScore(
            (int) $validated['respiratory_rate'],
            (int) $validated['oxygen_saturation'],
            (bool) ($validated['supplemental_oxygen'] ?? false),
            (int) $validated['systolic_bp'],
            (int) $validated['pulse_rate'],
            $validated['consciousness_level'],
            (float) $validated['temperature']
        );

        $alert = News2VitalAlert::create([
            'patient_profile_id' => $validated['patient_profile_id'],
            'respiratory_rate' => $validated['respiratory_rate'],
            'oxygen_saturation' => $validated['oxygen_saturation'],
            'supplemental_oxygen' => (bool) ($validated['supplemental_oxygen'] ?? false),
            'systolic_bp' => $validated['systolic_bp'],
            'pulse_rate' => $validated['pulse_rate'],
            'consciousness_level' => $validated['consciousness_level'],
            'temperature' => $validated['temperature'],
            'news2_score' => $scoreResult['score'],
            'risk_level' => $scoreResult['risk_level'],
            'rrt_triggered' => $scoreResult['trigger_rrt'],
            'clinician_notes' => $validated['clinician_notes'] ?? null,
            'recorded_by' => Auth::user()->name ?? 'Điều dưỡng trực',
        ]);

        if ($scoreResult['trigger_rrt']) {
            RrtActivation::create([
                'news2_alert_id' => $alert->id,
                'patient_profile_id' => $alert->patient_profile_id,
                'activation_code' => RrtActivation::generateCode(),
                'ward_location' => $validated['ward_location'] ?? 'Khoa Khám Bệnh - Buồng Cấp Cứu Sảnh',
                'status' => 'dispatched',
                'lead_doctor' => 'BS. Trưởng Kíp Hồi Sức Tích Cực',
                'icu_nurse' => 'ĐD. Hồi Sức Cấp Cứu',
                'response_time_minutes' => 3,
                'intervention_summary' => 'Kích hoạt còi báo động RRT tự động do NEWS2 >= 7 (Nguy cơ suy hô hấp/ngừng tuần hoàn).',
            ]);
        }

        return redirect()->route('reception.news2-rrt.index')->with('success', "Đã ghi nhận sinh hiệu NEWS2 thành công! Điểm: {$scoreResult['score']} ({$scoreResult['risk_level']})");
    }
}
