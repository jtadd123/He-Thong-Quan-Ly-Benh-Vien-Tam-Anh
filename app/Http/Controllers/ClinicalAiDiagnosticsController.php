<?php

namespace App\Http\Controllers;

use App\Models\PacsAiAnalysis;
use App\Models\SepsisQsofaAlert;
use App\Models\PharmacogenomicProfile;
use App\Models\TransfusionReactionReport;
use App\Models\BloodUnit;
use App\Models\PatientProfile;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ClinicalAiDiagnosticsController extends Controller
{
    /**
     * Dashboard Trung Tâm AI Lâm Sàng & An Toàn Sinh Học
     */
    public function index(Request $request)
    {
        $pacsAnalyses = PacsAiAnalysis::with('patient')
            ->orderBy('is_prioritized', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'pacs_page');

        $sepsisAlerts = SepsisQsofaAlert::with('patient')
            ->orderBy('qsofa_score', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'sepsis_page');

        $genomicProfiles = PharmacogenomicProfile::with('patient')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'pgx_page');

        $reactionReports = TransfusionReactionReport::with(['patient', 'bloodUnit'])
            ->orderBy('onset_time', 'desc')
            ->paginate(10, ['*'], 'transfusion_page');

        $patients = PatientProfile::orderBy('full_name')->limit(50)->get();
        $bloodUnits = BloodUnit::whereIn('status', ['in_stock', 'reserved', 'transfusing'])->get();

        return view('reception.clinical_ai.index', compact(
            'pacsAnalyses',
            'sepsisAlerts',
            'genomicProfiles',
            'reactionReports',
            'patients',
            'bloodUnits'
        ));
    }

    /**
     * Lưu kết quả AI phân tích hình ảnh PACS (Computer Vision Triage)
     */
    public function storePacsAnalysis(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'modality' => 'required|string|in:X-RAY,CT,MRI,ULTRASOUND',
            'body_part' => 'required|string|max:100',
            'finding_detected' => 'required|string|max:255',
            'confidence_score' => 'required|numeric|between:0,100',
            'triage_urgency' => 'required|in:CRITICAL,URGENT,ROUTINE',
        ]);

        $isCritical = ($validated['triage_urgency'] === 'CRITICAL' || $validated['confidence_score'] >= 90.0);
        $code = 'AI-PACS-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        PacsAiAnalysis::create([
            'analysis_code' => $code,
            'patient_id' => $validated['patient_id'],
            'modality' => $validated['modality'],
            'body_part' => $validated['body_part'],
            'finding_detected' => $validated['finding_detected'],
            'confidence_score' => $validated['confidence_score'],
            'triage_urgency' => $validated['triage_urgency'],
            'is_prioritized' => $isCritical,
            'ai_model_version' => 'Tamanh-VisionMed v5.0 (ResNet-152 + Transformer)',
        ]);

        $msg = "Đã lưu kết quả AI phân tích {$code}.";
        if ($isCritical) {
            $msg .= " Đã tự động kích hoạt cờ ƯU TIÊN ĐỌC PHIM KHẨN CẤP cho Bác sĩ Chẩn đoán hình ảnh!";
        }

        return redirect()->route('reception.clinical-ai.index')->with('success', $msg);
    }

    /**
     * Đánh giá nguy cơ sốc nhiễm khuẩn qSOFA
     */
    public function recordSepsisQsofa(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'respiratory_rate' => 'required|integer|min:0|max:100',
            'glasgow_coma_scale' => 'required|integer|between:3,15',
            'systolic_bp' => 'required|integer|min:0|max:300',
            'assessed_by' => 'required|string|max:100',
        ]);

        $score = SepsisQsofaAlert::calculateScore(
            $validated['respiratory_rate'],
            $validated['glasgow_coma_scale'],
            $validated['systolic_bp']
        );

        $riskLevel = 'LOW';
        $icuNotified = false;

        if ($score === 1) {
            $riskLevel = 'MODERATE';
        } elseif ($score >= 2) {
            $riskLevel = 'HIGH_RISK_SHOCK';
            $icuNotified = true; // Kích hoạt chuông báo ICU
        }

        SepsisQsofaAlert::create([
            'patient_id' => $validated['patient_id'],
            'respiratory_rate' => $validated['respiratory_rate'],
            'glasgow_coma_scale' => $validated['glasgow_coma_scale'],
            'systolic_bp' => $validated['systolic_bp'],
            'qsofa_score' => $score,
            'risk_level' => $riskLevel,
            'icu_notified' => $icuNotified,
            'assessed_by' => $validated['assessed_by'],
        ]);

        if ($score >= 2) {
            return redirect()->route('reception.clinical-ai.index')
                ->with('error', "CẢNH BÁO NGUY CƠ SỐC NHIỄM KHUẨN (qSOFA = {$score}/3): Đã gửi cảnh báo khẩn cấp đến Đội Hồi sức tích cực ICU!");
        }

        return redirect()->route('reception.clinical-ai.index')
            ->with('success', "Đã ghi nhận điểm qSOFA: {$score}/3. Tình trạng bệnh nhân ổn định.");
    }

    /**
     * Lưu hồ sơ gen dược lý (Pharmacogenomics - PGx)
     */
    public function storePharmacogenomic(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'gene_name' => 'required|string|max:50',
            'genotype' => 'required|string|max:50',
            'phenotype' => 'required|string|max:100',
            'contraindicated_medication' => 'required|string|max:255',
            'recommended_alternative' => 'required|string|max:255',
            'clinical_guideline_source' => 'nullable|string|max:255',
        ]);

        PharmacogenomicProfile::create($validated);

        return redirect()->route('reception.clinical-ai.index')
            ->with('success', "Đã cập nhật hồ sơ gen dược lý {$validated['gene_name']} cho người bệnh. Thuốc chống chỉ định: {$validated['contraindicated_medication']}.");
    }

    /**
     * Báo cáo tai biến truyền máu khẩn cấp (Hemovigilance Report)
     */
    public function reportTransfusionReaction(Request $request)
    {
        $validated = $request->validate([
            'blood_unit_id' => 'required|exists:blood_units,id',
            'patient_id' => 'required|exists:patient_profiles,id',
            'reaction_type' => 'required|in:FEBRILE,ALLERGIC_ANAPHYLACTIC,ACUTE_HEMOLYTIC,TRALI,TACO,SEPTIC',
            'symptoms' => 'required|string',
            'reporting_physician' => 'required|string|max:100',
            'outcome' => 'nullable|string',
        ]);

        // Cập nhật trạng thái túi máu sang discarded hoặc reserved để điều tra
        BloodUnit::where('id', $validated['blood_unit_id'])->update(['status' => 'discarded']);

        TransfusionReactionReport::create([
            'blood_unit_id' => $validated['blood_unit_id'],
            'patient_id' => $validated['patient_id'],
            'onset_time' => Carbon::now(),
            'reaction_type' => $validated['reaction_type'],
            'symptoms' => $validated['symptoms'],
            'immediate_transfusion_stopped' => true,
            'blood_unit_quarantined' => true,
            'reporting_physician' => $validated['reporting_physician'],
            'outcome' => $validated['outcome'] ?? 'Đã xử trí truyền Antihistamine/Corticoid và niêm phong túi máu kiểm nghiệm lại.',
        ]);

        return redirect()->route('reception.clinical-ai.index')
            ->with('error', "ĐÃ DỪNG TRUYỀN MÁU KHẨN CẤP & NIÊM PHONG TÚI MÁU! Đã kích hoạt phác đồ xử trí phản ứng truyền máu cấp theo Thông tư 26/2013/TT-BYT.");
    }
}
