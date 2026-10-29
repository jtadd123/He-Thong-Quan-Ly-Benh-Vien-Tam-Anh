<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\HemodialysisSession;
use App\Models\PatientProfile;
use Illuminate\Http\Request;

class HemodialysisController extends Controller
{
    public function index(Request $request)
    {
        $query = HemodialysisSession::with('patientProfile')->latest('session_date');

        if ($request->filled('shift')) {
            $query->where('shift', $request->shift);
        }

        $sessions = $query->paginate(15);
        $patients = PatientProfile::orderBy('full_name')->limit(50)->get();

        $stats = [
            'total_sessions' => HemodialysisSession::count(),
            'ongoing_now' => HemodialysisSession::where('status', 'ongoing')->count(),
            'completed' => HemodialysisSession::where('status', 'completed')->count(),
        ];

        return view('receptionist.hemodialysis.index', compact('sessions', 'patients', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_profile_id' => 'required|exists:patient_profiles,id',
            'session_date' => 'required|date',
            'shift' => 'required|in:morning,afternoon,evening',
            'machine_number' => 'required|string|max:30',
            'dry_weight_kg' => 'required|numeric|min:20|max:200',
            'pre_weight_kg' => 'required|numeric|min:20|max:200',
            'dialyzer_barcode' => 'required|string|max:50',
            'dialyzer_reuse_count' => 'required|integer|min:1|max:6',
            'nurse_in_charge' => 'required|string|max:100',
        ]);

        // Tính mục tiêu rút dịch (Ultrafiltration UF Target ml): Cân nặng trước - Cân nặng khô
        $preWeight = (float) $validated['pre_weight_kg'];
        $dryWeight = (float) $validated['dry_weight_kg'];
        $weightGainKg = max(0, $preWeight - $dryWeight);
        $ufTargetMl = (int) round($weightGainKg * 1000);

        HemodialysisSession::create([
            'patient_profile_id' => $validated['patient_profile_id'],
            'session_date' => $validated['session_date'],
            'shift' => $validated['shift'],
            'machine_number' => $validated['machine_number'],
            'dry_weight_kg' => $dryWeight,
            'pre_weight_kg' => $preWeight,
            'post_weight_kg' => null,
            'uf_target_ml' => $ufTargetMl,
            'uf_actual_ml' => null,
            'dialyzer_barcode' => $validated['dialyzer_barcode'],
            'dialyzer_reuse_count' => $validated['dialyzer_reuse_count'],
            'status' => 'ongoing',
            'nurse_in_charge' => $validated['nurse_in_charge'],
        ]);

        return redirect()->route('reception.hemodialysis.index')->with('success', "Đã kết nối máy lọc máu thận nhân tạo! Mục tiêu rút dịch UF: {$ufTargetMl} ml.");
    }

    public function complete(Request $request, HemodialysisSession $session)
    {
        $validated = $request->validate([
            'post_weight_kg' => 'required|numeric|min:20|max:200',
            'uf_actual_ml' => 'required|integer|min:0',
        ]);

        $session->update([
            'post_weight_kg' => $validated['post_weight_kg'],
            'uf_actual_ml' => $validated['uf_actual_ml'],
            'status' => 'completed',
        ]);

        return redirect()->route('reception.hemodialysis.index')->with('success', 'Đã kết thúc ca lọc máu thận nhân tạo và lưu biên bản theo dõi thành công!');
    }
}
