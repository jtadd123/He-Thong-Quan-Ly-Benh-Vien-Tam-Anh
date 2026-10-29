<?php

namespace App\Http\Controllers;

use App\Models\TherapeuticDrugMonitoring;
use App\Models\ParenteralNutrition;
use App\Models\AdrAdverseDrugReport;
use App\Models\PatientProfile;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdvancedClinicalPharmacyController extends Controller
{
    /**
     * Dashboard Dược Lâm Sàng Chuyên Sâu & TDM
     */
    public function index(Request $request)
    {
        $tdmLogs = TherapeuticDrugMonitoring::with('patient')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'tdm_page');

        $tpnLogs = ParenteralNutrition::with('patient')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'tpn_page');

        $adrReports = AdrAdverseDrugReport::with('patient')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'adr_page');

        $patients = PatientProfile::orderBy('full_name')->limit(50)->get();

        return view('reception.clinical_pharmacy.index', compact(
            'tdmLogs',
            'tpnLogs',
            'adrReports',
            'patients'
        ));
    }

    /**
     * Ghi nhận đo nồng độ thuốc trong máu (TDM)
     */
    public function storeTdm(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'medication_name' => 'required|string|max:100',
            'trough_concentration_cmin' => 'required|numeric|min:0',
            'peak_concentration_cmax' => 'nullable|numeric|min:0',
            'creatinine_clearance_crcl' => 'required|numeric|min:0',
            'target_range' => 'required|string|max:100',
            'dose_adjustment_recommendation' => 'required|string',
            'pharmacist_evaluator' => 'required|string|max:100',
        ]);

        // Logic kiểm tra khoảng trị liệu Vancomycin (15 - 20 mg/L)
        $cmin = (float) $validated['trough_concentration_cmin'];
        $isWithin = ($cmin >= 10.0 && $cmin <= 20.0);

        TherapeuticDrugMonitoring::create([
            'patient_id' => $validated['patient_id'],
            'medication_name' => $validated['medication_name'],
            'trough_concentration_cmin' => $validated['trough_concentration_cmin'],
            'peak_concentration_cmax' => $validated['peak_concentration_cmax'] ?? null,
            'creatinine_clearance_crcl' => $validated['creatinine_clearance_crcl'],
            'target_range' => $validated['target_range'],
            'is_within_target' => $isWithin,
            'dose_adjustment_recommendation' => $validated['dose_adjustment_recommendation'],
            'pharmacist_evaluator' => $validated['pharmacist_evaluator'],
        ]);

        $statusText = $isWithin ? 'TRONG KHOẢNG MỤC TIÊU AN TOÀN' : 'NGOÀI KHOẢNG TRỊ LIỆU (CẦN CHỈNH LIỀU)';

        return redirect()->route('reception.clinical-pharmacy.index')
            ->with('success', "Đã lưu kết quả TDM {$validated['medication_name']} (Cmin = {$cmin} mg/L). Đánh giá: {$statusText}.");
    }

    /**
     * Tính toán dinh dưỡng tĩnh mạch toàn phần (TPN Calculator)
     */
    public function storeTpn(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'total_calories_kcal' => 'required|integer|min:500|max:4000',
            'glucose_grams' => 'required|numeric|min:50',
            'amino_acids_grams' => 'required|numeric|min:20',
            'lipids_grams' => 'required|numeric|min:10',
            'infusion_route' => 'required|in:CENTRAL_LINE,PERIPHERAL',
            'refeeding_risk' => 'required|boolean',
            'clinical_dietitian' => 'required|string|max:100',
        ]);

        ParenteralNutrition::create($validated);

        return redirect()->route('reception.clinical-pharmacy.index')
            ->with('success', "Đã lập công thức túi dinh dưỡng tĩnh mạch TPN ({$validated['total_calories_kcal']} kcal) chuẩn hóa vô trùng.");
    }

    /**
     * Báo cáo phản ứng có hại của thuốc (ADR Report)
     */
    public function storeAdr(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'suspected_medication' => 'required|string|max:255',
            'reaction_description' => 'required|string',
            'severity_level' => 'required|in:MILD,MODERATE,SEVERE_LIFE_THREATENING,FATAL',
            'causality_naranjo_score' => 'required|integer|between:0,13',
            'reporter_name' => 'required|string|max:100',
        ]);

        $score = $validated['causality_naranjo_score'];
        $category = 'POSSIBLE';
        if ($score >= 9) $category = 'DEFINITE';
        elseif ($score >= 5) $category = 'PROBABLE';
        elseif ($score >= 1) $category = 'POSSIBLE';
        else $category = 'DOUBTFUL';

        AdrAdverseDrugReport::create([
            'patient_id' => $validated['patient_id'],
            'suspected_medication' => $validated['suspected_medication'],
            'reaction_description' => $validated['reaction_description'],
            'severity_level' => $validated['severity_level'],
            'causality_naranjo_score' => $score,
            'causality_category' => $category,
            'outcome' => 'Đã ngừng thuốc và điều trị triệu chứng ổn định.',
            'reporter_name' => $validated['reporter_name'],
        ]);

        return redirect()->route('reception.clinical-pharmacy.index')
            ->with('success', "Đã lập báo cáo ADR gửi Trung tâm DI & ADR Quốc gia (Điểm Naranjo: {$score} - {$category}).");
    }
}
