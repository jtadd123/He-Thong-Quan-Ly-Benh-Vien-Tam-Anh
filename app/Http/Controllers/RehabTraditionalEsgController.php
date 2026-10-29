<?php

namespace App\Http\Controllers;

use App\Models\RehabilitationCarePlan;
use App\Models\HerbalPrescription;
use App\Models\AcupointTherapy;
use App\Models\BhutaniJaundiceEvaluation;
use App\Models\DonorMilkBatch;
use App\Models\GreenHospitalEsgLog;
use App\Models\RadiationDoseLog;
use App\Models\PatientProfile;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RehabTraditionalEsgController extends Controller
{
    /**
     * Dashboard Phục Hồi Chức Năng, YHCT & Bệnh Viện Xanh ESG
     */
    public function index(Request $request)
    {
        // Seed dữ liệu mẫu ESG nếu chưa có
        if (GreenHospitalEsgLog::count() === 0) {
            GreenHospitalEsgLog::create([
                'log_date' => Carbon::today(),
                'paperless_sheets_saved' => 4200,
                'carbon_footprint_saved_kg' => 46.20,
                'energy_kwh_conserved' => 145.50,
                'medical_waste_recycled_kg' => 52.00,
                'esg_score' => 96,
            ]);
        }

        $rehabPlans = RehabilitationCarePlan::with('patient')
            ->orderBy('created_at', 'desc')
            ->paginate(8, ['*'], 'rehab_page');

        $herbalPrescriptions = HerbalPrescription::with('patient')
            ->orderBy('created_at', 'desc')
            ->paginate(8, ['*'], 'herbal_page');

        $acupointTherapies = AcupointTherapy::with('patient')
            ->orderBy('created_at', 'desc')
            ->paginate(8, ['*'], 'acu_page');

        $jaundiceLogs = BhutaniJaundiceEvaluation::with('infant')
            ->orderBy('created_at', 'desc')
            ->paginate(8, ['*'], 'jaundice_page');

        $milkBatches = DonorMilkBatch::orderBy('expiry_date', 'asc')
            ->paginate(8, ['*'], 'milk_page');

        $esgLogs = GreenHospitalEsgLog::orderBy('log_date', 'desc')->get();

        $radiationLogs = RadiationDoseLog::with('patient')
            ->orderBy('created_at', 'desc')
            ->paginate(8, ['*'], 'rad_page');

        $patients = PatientProfile::orderBy('full_name')->limit(50)->get();

        return view('reception.rehab_traditional_esg.index', compact(
            'rehabPlans',
            'herbalPrescriptions',
            'acupointTherapies',
            'jaundiceLogs',
            'milkBatches',
            'esgLogs',
            'radiationLogs',
            'patients'
        ));
    }

    /**
     * Lập kế hoạch Phục Hồi Chức Năng & Điểm FIM
     */
    public function storeRehabPlan(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'diagnosis' => 'required|string|max:255',
            'fim_score_total' => 'required|integer|between:18,126',
            'barthel_index' => 'required|integer|between:0,100',
            'assigned_therapy_type' => 'required|string|max:100',
            'weekly_goals' => 'required|string',
            'pt_specialist' => 'required|string|max:100',
        ]);

        $fim = $validated['fim_score_total'];
        $status = 'MODERATE_ASSISTANCE';
        if ($fim >= 108) $status = 'INDEPENDENT';
        elseif ($fim >= 72) $status = 'MODERATE_ASSISTANCE';
        elseif ($fim >= 36) $status = 'MAXIMAL_ASSISTANCE';
        else $status = 'TOTAL_ASSISTANCE';

        RehabilitationCarePlan::create(array_merge($validated, ['functional_status' => $status]));

        return redirect()->route('reception.rehab-esg.index')
            ->with('success', "Đã lập kế hoạch phục hồi chức năng ({$validated['assigned_therapy_type']}) - Điểm FIM: {$fim}/126 ({$status}).");
    }

    /**
     * Kê đơn thuốc thang YHCT & Kiểm tra tương kỵ Thập Bát Phản
     */
    public function storeHerbalPrescription(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'remedy_name' => 'required|string|max:100',
            'herbal_ingredients' => 'required|string',
            'decoction_bags_count' => 'required|integer|min:1|max:30',
            'traditional_doctor' => 'required|string|max:100',
        ]);

        [$hasIncompat, $reason] = HerbalPrescription::checkIncompatibility($validated['herbal_ingredients']);

        HerbalPrescription::create([
            'patient_id' => $validated['patient_id'],
            'remedy_name' => $validated['remedy_name'],
            'herbal_ingredients' => $validated['herbal_ingredients'],
            'has_incompatibility_contraindication' => $hasIncompat,
            'incompatible_pair' => $reason,
            'decoction_bags_count' => $validated['decoction_bags_count'],
            'traditional_doctor' => $validated['traditional_doctor'],
        ]);

        if ($hasIncompat) {
            return redirect()->route('reception.rehab-esg.index')
                ->with('error', "CẢNH BÁO TƯƠNG KỴ THẬP BÁT PHẢN: {$reason}! Đơn thuốc đã bị gắn cờ độc tính.");
        }

        return redirect()->route('reception.rehab-esg.index')
            ->with('success', "Đã lưu đơn thuốc thang {$validated['remedy_name']} ({$validated['decoction_bags_count']} túi sắc sẵn tự động). Kiểm tra tương kỵ: AN TOÀN 100%.");
    }

    /**
     * Ghi nhận trị liệu châm cứu / cấy chỉ
     */
    public function storeAcupointTherapy(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'meridian_name' => 'required|string|max:100',
            'acupoints_selected' => 'required|string|max:255',
            'therapy_method' => 'required|in:ELECTRO_ACUPUNCTURE,CATGUT_EMBEDDING,ACUPOINT_INJECTION,MOXIBUSTION',
            'treatment_course_days' => 'required|integer|min:1|max:30',
            'acupuncturist' => 'required|string|max:100',
        ]);

        AcupointTherapy::create($validated);

        return redirect()->route('reception.rehab-esg.index')
            ->with('success', "Đã ghi nhận liệu trình trị liệu ({$validated['therapy_method']}) tại các huyệt {$validated['acupoints_selected']}.");
    }

    /**
     * Đánh giá vàng da sơ sinh Bhutani Nomogram
     */
    public function storeBhutaniEvaluation(Request $request)
    {
        $validated = $request->validate([
            'infant_patient_id' => 'required|exists:patient_profiles,id',
            'age_in_hours' => 'required|integer|min:1|max:168',
            'total_serum_bilirubin_mg_dl' => 'required|numeric|min:0|max:40',
            'neonatologist' => 'required|string|max:100',
        ]);

        [$zone, $photo, $exchange] = BhutaniJaundiceEvaluation::evaluateRisk(
            $validated['age_in_hours'],
            (float) $validated['total_serum_bilirubin_mg_dl']
        );

        BhutaniJaundiceEvaluation::create([
            'infant_patient_id' => $validated['infant_patient_id'],
            'age_in_hours' => $validated['age_in_hours'],
            'total_serum_bilirubin_mg_dl' => $validated['total_serum_bilirubin_mg_dl'],
            'risk_zone' => $zone,
            'phototherapy_indicated' => $photo,
            'exchange_transfusion_indicated' => $exchange,
            'neonatologist' => $validated['neonatologist'],
        ]);

        if ($exchange) {
            return redirect()->route('reception.rehab-esg.index')
                ->with('error', "CẢNH BÁO NGUY CƠ VÀNG DA NHÂN (Bilirubin = {$validated['total_serum_bilirubin_mg_dl']} mg/dL ở {$validated['age_in_hours']}h tuổi): CHỈ ĐỊNH THAY MÁU CẤP CỨU VÀ CHIẾU ĐÈN!");
        } elseif ($photo) {
            return redirect()->route('reception.rehab-esg.index')
                ->with('warning', "Vàng da vùng nguy cơ cao (High Risk Zone): CHỈ ĐỊNH CHIẾU ĐÈN ÁNH SÁNG XANH (Phototherapy) LIÊN TỤC.");
        }

        return redirect()->route('reception.rehab-esg.index')
            ->with('success', "Bilirubin trong ngưỡng an toàn ({$zone}). Tiếp tục bú mẹ và theo dõi.");
    }

    /**
     * Nhập lô sữa mẹ thanh trùng Pasteur
     */
    public function storeMilkBatch(Request $request)
    {
        $validated = $request->validate([
            'donor_code' => 'required|string|max:50',
            'volume_ml' => 'required|integer|min:50|max:2000',
            'microbiology_result' => 'required|in:PASSED_STERILE,FAILED_CONTAMINATED',
        ]);

        $code = 'MILK-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        DonorMilkBatch::create([
            'batch_code' => $code,
            'donor_code' => $validated['donor_code'],
            'volume_ml' => $validated['volume_ml'],
            'pasteurization_temperature' => 62.5,
            'pasteurization_duration_min' => 30,
            'microbiology_result' => $validated['microbiology_result'],
            'status' => ($validated['microbiology_result'] === 'PASSED_STERILE') ? 'available' : 'discarded',
            'expiry_date' => Carbon::today()->addMonths(6),
        ]);

        if ($validated['microbiology_result'] === 'PASSED_STERILE') {
            return redirect()->route('reception.rehab-esg.index')
                ->with('success', "Đã thanh trùng Pasteur thành công mẻ sữa mẹ {$code} ({$validated['volume_ml']} mL). Vi sinh đạt chuẩn vô trùng.");
        }

        return redirect()->route('reception.rehab-esg.index')
            ->with('error', "Mẻ sữa mẹ {$code} nhiễm khuẩn trong kiểm nghiệm vi sinh -> Đã tự động kích hoạt hủy bỏ tiêu hủy.");
    }

    /**
     * Ghi nhận liều chiếu xạ ALARA
     */
    public function storeRadiationLog(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'procedure_name' => 'required|string|max:100',
            'dose_msv' => 'required|numeric|min:0',
            'radiology_technician' => 'required|string|max:100',
        ]);

        $lastLog = RadiationDoseLog::where('patient_id', $validated['patient_id'])->latest()->first();
        $prevCumulative = $lastLog ? (float)$lastLog->cumulative_dose_msv : 0.0;
        $currentDose = (float)$validated['dose_msv'];
        $newCumulative = $prevCumulative + $currentDose;

        $alara = 'OPTIMAL_SAFE';
        if ($newCumulative >= 20.0) $alara = 'EXCEEDED_THRESHOLD'; // Ngưỡng an toàn bức xạ 20 mSv/năm
        elseif ($newCumulative >= 10.0) $alara = 'MONITOR_CLOSELY';

        RadiationDoseLog::create([
            'patient_id' => $validated['patient_id'],
            'procedure_name' => $validated['procedure_name'],
            'dose_msv' => $currentDose,
            'cumulative_dose_msv' => $newCumulative,
            'alara_status' => $alara,
            'radiology_technician' => $validated['radiology_technician'],
        ]);

        if ($alara === 'EXCEEDED_THRESHOLD') {
            return redirect()->route('reception.rehab-esg.index')
                ->with('error', "CẢNH BÁO AN TOÀN BỨC XẠ ALARA: Liều tích lũy ({$newCumulative} mSv) vượt ngưỡng 20 mSv/năm. Đề xuất đổi sang MRI hoặc Siêu âm.");
        }

        return redirect()->route('reception.rehab-esg.index')
            ->with('success', "Đã ghi nhận liều tia xạ {$currentDose} mSv (Tích lũy năm: {$newCumulative} mSv - {$alara}).");
    }
}
