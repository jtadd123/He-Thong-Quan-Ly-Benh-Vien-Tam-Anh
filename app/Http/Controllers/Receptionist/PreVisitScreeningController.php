<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\PreVisitScreening;
use Illuminate\Http\Request;

class PreVisitScreeningController extends Controller
{
    public function index(Request $request)
    {
        $query = PreVisitScreening::latest();

        if ($request->filled('risk_stratification')) {
            $query->where('risk_stratification', $request->risk_stratification);
        }

        $screenings = $query->paginate(15);

        $stats = [
            'total' => PreVisitScreening::count(),
            'urgent_red_flags' => PreVisitScreening::where('risk_stratification', 'urgent_red_flag')->count(),
            'synced' => PreVisitScreening::where('synced_to_emr', true)->count(),
        ];

        return view('receptionist.pre_visit.index', compact('screenings', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'appointment_code' => 'required|string|max:30',
            'patient_name' => 'required|string|max:100',
            'patient_phone' => 'required|string|max:20',
            'symptoms_declared' => 'required|array|min:1',
            'home_medications' => 'nullable|string',
            'known_allergies' => 'nullable|string',
            'pregnancy_or_breastfeeding' => 'nullable|boolean',
            'family_medical_history' => 'nullable|string',
        ]);

        $symptoms = $validated['symptoms_declared'];
        $hasRedFlag = in_array('dau_nguc_du_doi', $symptoms) || in_array('kho_tho_cap', $symptoms) || in_array('yeu_liet_nua_nguoi', $symptoms);

        $risk = $hasRedFlag ? 'urgent_red_flag' : (count($symptoms) > 3 ? 'priority' : 'routine');

        PreVisitScreening::create([
            'appointment_code' => $validated['appointment_code'],
            'patient_name' => $validated['patient_name'],
            'patient_phone' => $validated['patient_phone'],
            'symptoms_declared' => $symptoms,
            'home_medications' => $validated['home_medications'] ?? null,
            'known_allergies' => $validated['known_allergies'] ?? null,
            'pregnancy_or_breastfeeding' => (bool) ($validated['pregnancy_or_breastfeeding'] ?? false),
            'family_medical_history' => $validated['family_medical_history'] ?? null,
            'risk_stratification' => $risk,
            'synced_to_emr' => true,
        ]);

        return redirect()->route('reception.pre-visit.index')->with(
            $hasRedFlag ? 'warning' : 'success',
            $hasRedFlag ? "CẢNH BÁO ĐỎ: Bệnh nhân có dấu hiệu cấp cứu! Đã tự động gắn cờ ưu tiên khám khẩn." : "Đã đồng bộ phiếu tự khai tiền sử bệnh thành công vào hồ sơ tiếp nhận."
        );
    }
}
