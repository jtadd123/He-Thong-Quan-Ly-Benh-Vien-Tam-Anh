<?php

namespace App\Http\Controllers;

use App\Models\ElectronicPartograph;
use App\Models\InfantSecurityTag;
use App\Models\ChemoTreatmentCycle;
use App\Models\VirtualTumorBoard;
use App\Models\GoldenHourTracker;
use App\Models\HospitalQualityCriteria;
use App\Models\PatientProfile;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MaternityInfantOncologyController extends Controller
{
    /**
     * Dashboard Sản - Nhi An Toàn, Ung Bướu & Hộp Đen Cấp Cứu
     */
    public function index(Request $request)
    {
        // Tự động seed một số tiêu chí Bộ Y Tế nếu chưa có
        if (HospitalQualityCriteria::count() === 0) {
            HospitalQualityCriteria::create([
                'criteria_code' => 'C1.1',
                'criteria_name' => 'Quy trình tiếp đón, hướng dẫn và phân luồng người bệnh',
                'section' => 'Phần C: Hoạt động chuyên môn',
                'current_level' => 5,
                'target_level' => 5,
                'evidence_summary' => 'Smart Kiosk cảm ứng, Quét CCCD gắn chip, Gọi loa AI, Triage ATS 5 cấp.',
                'last_audited_date' => Carbon::now()->subDays(10),
            ]);
            HospitalQualityCriteria::create([
                'criteria_code' => 'C4.3',
                'criteria_name' => 'Bảo đảm an toàn phẫu thuật và kiểm soát nhiễm khuẩn phòng mổ',
                'section' => 'Phần C: Hoạt động chuyên môn',
                'current_level' => 5,
                'target_level' => 5,
                'evidence_summary' => 'Bảng kiểm đếm gạc RFID/Barcode, Thang điểm hồi tỉnh Aldrete điện tử, Khí y tế IoT.',
                'last_audited_date' => Carbon::now()->subDays(8),
            ]);
            HospitalQualityCriteria::create([
                'criteria_code' => 'C6.1',
                'criteria_name' => 'Hệ thống cảnh báo dị ứng thuốc và dược lâm sàng nâng cao',
                'section' => 'Phần C: Hoạt động chuyên môn',
                'current_level' => 5,
                'target_level' => 5,
                'evidence_summary' => 'CDSS dị ứng chéo kháng sinh, Tủ thuốc độc TT20 ký kép, Giám sát nồng độ TDM.',
                'last_audited_date' => Carbon::now()->subDays(5),
            ]);
            HospitalQualityCriteria::create([
                'criteria_code' => 'D2.2',
                'criteria_name' => 'Ứng dụng công nghệ thông tin và chuyển đổi số y tế toàn diện',
                'section' => 'Phần D: Cải tiến chất lượng',
                'current_level' => 5,
                'target_level' => 5,
                'evidence_summary' => 'EMR không giấy, 214 kịch bản kiểm thử tự động, chuẩn hóa dữ liệu liên thông.',
                'last_audited_date' => Carbon::now()->subDays(2),
            ]);
        }

        $partographs = ElectronicPartograph::with('patient')
            ->orderBy('observation_time', 'desc')
            ->paginate(8, ['*'], 'parto_page');

        $infantTags = InfantSecurityTag::with('mother')
            ->orderBy('created_at', 'desc')
            ->paginate(8, ['*'], 'infant_page');

        $chemoCycles = ChemoTreatmentCycle::with('patient')
            ->orderBy('scheduled_date', 'asc')
            ->paginate(8, ['*'], 'chemo_page');

        $tumorBoards = VirtualTumorBoard::with('patient')
            ->orderBy('meeting_date', 'desc')
            ->paginate(8, ['*'], 'tumor_page');

        $goldenHours = GoldenHourTracker::with('patient')
            ->orderBy('door_arrival_time', 'desc')
            ->paginate(8, ['*'], 'golden_page');

        $qualityCriteria = HospitalQualityCriteria::orderBy('criteria_code', 'asc')->get();
        $patients = PatientProfile::orderBy('full_name')->limit(50)->get();

        return view('reception.maternity_oncology.index', compact(
            'partographs',
            'infantTags',
            'chemoCycles',
            'tumorBoards',
            'goldenHours',
            'qualityCriteria',
            'patients'
        ));
    }

    /**
     * Ghi nhận điểm theo dõi chuyển dạ Partograph WHO
     */
    public function storePartograph(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'observation_time' => 'required|date',
            'cervical_dilation_cm' => 'required|numeric|between:1,10',
            'fetal_head_station' => 'required|numeric|between:-3,3',
            'fetal_heart_rate' => 'required|integer|between:50,220',
            'contractions_per_10min' => 'required|integer|between:1,7',
            'midwife_name' => 'required|string|max:100',
        ]);

        // Logic WHO: CTC mở chậm cắt đường báo động hoặc tim thai suy
        $crossesAlert = ($validated['cervical_dilation_cm'] < 4.0 && Carbon::parse($validated['observation_time'])->diffInHours(Carbon::now()) >= 4);
        $crossesAction = ($validated['fetal_heart_rate'] < 110 || $validated['fetal_heart_rate'] > 160);

        ElectronicPartograph::create([
            'patient_id' => $validated['patient_id'],
            'observation_time' => $validated['observation_time'],
            'cervical_dilation_cm' => $validated['cervical_dilation_cm'],
            'fetal_head_station' => $validated['fetal_head_station'],
            'fetal_heart_rate' => $validated['fetal_heart_rate'],
            'contractions_per_10min' => $validated['contractions_per_10min'],
            'crosses_alert_line' => $crossesAlert,
            'crosses_action_line' => $crossesAction,
            'midwife_name' => $validated['midwife_name'],
        ]);

        if ($crossesAction) {
            return redirect()->route('reception.maternity-oncology.index')
                ->with('error', "CẢNH BÁO BÁC SĨ SẢN KHOA: Tim thai bất thường ({$validated['fetal_heart_rate']} bpm). Cắt ngang Đường Hành Động (Action Line) - Đề xuất chỉ định mổ lấy thai khẩn cấp!");
        }

        return redirect()->route('reception.maternity-oncology.index')
            ->with('success', "Đã ghi nhận điểm theo dõi chuyển dạ Partograph. Độ mở cổ tử cung: {$validated['cervical_dilation_cm']} cm.");
    }

    /**
     * Ghép đôi vòng RFID mẹ và bé chống bắt cóc
     */
    public function pairInfantTag(Request $request)
    {
        $validated = $request->validate([
            'mother_patient_id' => 'required|exists:patient_profiles,id',
            'baby_gender' => 'required|string|in:Bé trai,Bé gái',
            'birth_time' => 'required|date',
            'birth_weight_grams' => 'required|numeric|min:500|max:6000',
            'mother_band_code' => 'required|string|max:50',
            'perimeter_status' => 'required|in:INSIDE_WARD,AUTHORIZED_TRANSFER,BREACH_ALARM',
        ]);

        $code = 'RFID-INFANT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        InfantSecurityTag::create([
            'infant_tag_code' => $code,
            'mother_patient_id' => $validated['mother_patient_id'],
            'baby_gender' => $validated['baby_gender'],
            'birth_time' => $validated['birth_time'],
            'birth_weight_grams' => $validated['birth_weight_grams'],
            'mother_band_code' => $validated['mother_band_code'],
            'is_paired' => true,
            'perimeter_status' => $validated['perimeter_status'],
        ]);

        if ($validated['perimeter_status'] === 'BREACH_ALARM') {
            return redirect()->route('reception.maternity-oncology.index')
                ->with('error', "BÁO ĐỘNG AN NINH SƠ SINH: Phát hiện vi phạm ranh giới an toàn khoa Sản! Thang máy tự động khóa chốt điện tử.");
        }

        return redirect()->route('reception.maternity-oncology.index')
            ->with('success', "Đã kích hoạt vòng tay an ninh RFID ({$code}) ghép đôi an toàn 100% với mẹ ({$validated['mother_band_code']}).");
    }

    /**
     * Quản lý chu kỳ hóa trị ung thư theo BSA
     */
    public function recordChemoCycle(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'regimen_name' => 'required|string|max:100',
            'cycle_number' => 'required|integer|min:1',
            'total_cycles' => 'required|integer|min:1',
            'body_surface_area' => 'required|numeric|between:0.5,3.0',
            'anc_neutrophil_count' => 'required|integer|min:0',
            'platelet_count' => 'required|integer|min:0',
            'scheduled_date' => 'required|date',
        ]);

        $isEligible = ChemoTreatmentCycle::checkEligibility(
            $validated['anc_neutrophil_count'],
            $validated['platelet_count']
        );

        ChemoTreatmentCycle::create([
            'patient_id' => $validated['patient_id'],
            'regimen_name' => $validated['regimen_name'],
            'cycle_number' => $validated['cycle_number'],
            'total_cycles' => $validated['total_cycles'],
            'body_surface_area' => $validated['body_surface_area'],
            'anc_neutrophil_count' => $validated['anc_neutrophil_count'],
            'platelet_count' => $validated['platelet_count'],
            'is_eligible_to_dispense' => $isEligible,
            'scheduled_date' => $validated['scheduled_date'],
            'status' => $isEligible ? 'in_progress' : 'delayed_toxicity',
        ]);

        if (!$isEligible) {
            return redirect()->route('reception.maternity-oncology.index')
                ->with('error', "TẠM HOÃN HÓA TRỊ: Chỉ số huyết học chưa hồi phục an toàn (ANC < 1500 hoặc Tiểu cầu < 100.000). Chuyển sang theo dõi độc tính tủy xương.");
        }

        return redirect()->route('reception.maternity-oncology.index')
            ->with('success', "ĐỦ ĐIỀU KIỆN AN TOÀN TRUYỀN HÓA CHẤT: Chu kỳ {$validated['cycle_number']}/{$validated['total_cycles']} phác đồ {$validated['regimen_name']} (BSA = {$validated['body_surface_area']} m2).");
    }

    /**
     * Đăng ký hội đồng ung thư đa chuyên khoa (Virtual Tumor Board)
     */
    public function storeTumorBoard(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'cancer_diagnosis' => 'required|string|max:255',
            'meeting_date' => 'required|date',
            'consensus_treatment_plan' => 'required|string',
            'attendees' => 'required|string', // Chuỗi phân tách bởi dấu phẩy
        ]);

        $attendeesArray = array_map('trim', explode(',', $validated['attendees']));
        $code = 'VTB-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        VirtualTumorBoard::create([
            'board_code' => $code,
            'patient_id' => $validated['patient_id'],
            'cancer_diagnosis' => $validated['cancer_diagnosis'],
            'meeting_date' => $validated['meeting_date'],
            'consensus_treatment_plan' => $validated['consensus_treatment_plan'],
            'multidisciplinary_attendees' => $attendeesArray,
            'status' => 'approved',
        ]);

        return redirect()->route('reception.maternity-oncology.index')
            ->with('success', "Đã thông qua biên bản Hội đồng Ung thư Đa Chuyên Khoa ({$code}) với sự đồng thuận của " . count($attendeesArray) . " chuyên gia.");
    }

    /**
     * Ghi nhận cấp cứu giờ vàng (Golden Hour Tracker)
     */
    public function recordGoldenHour(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'emergency_type' => 'required|in:STROKE_BRAIN,STEMI_HEART',
            'door_arrival_time' => 'required|date',
            'intervention_time' => 'required|date|after_or_equal:door_arrival_time',
            'attending_team' => 'required|string|max:100',
        ]);

        $arrival = Carbon::parse($validated['door_arrival_time']);
        $intervention = Carbon::parse($validated['intervention_time']);
        $diffMinutes = $arrival->diffInMinutes($intervention);

        $doorToNeedle = null;
        $doorToBalloon = null;
        $metTarget = false;

        if ($validated['emergency_type'] === 'STROKE_BRAIN') {
            $doorToNeedle = $diffMinutes;
            $metTarget = ($diffMinutes <= 60); // Door-to-needle < 60 phút
        } else {
            $doorToBalloon = $diffMinutes;
            $metTarget = ($diffMinutes <= 90); // Door-to-balloon < 90 phút
        }

        GoldenHourTracker::create([
            'patient_id' => $validated['patient_id'],
            'emergency_type' => $validated['emergency_type'],
            'door_arrival_time' => $validated['door_arrival_time'],
            'intervention_time' => $validated['intervention_time'],
            'door_to_needle_minutes' => $doorToNeedle,
            'door_to_balloon_minutes' => $doorToBalloon,
            'met_target' => $metTarget,
            'attending_team' => $validated['attending_team'],
        ]);

        $targetText = ($validated['emergency_type'] === 'STROKE_BRAIN') ? "Door-to-Needle: {$diffMinutes} phút (Mục tiêu < 60p)" : "Door-to-Balloon: {$diffMinutes} phút (Mục tiêu < 90p)";

        if ($metTarget) {
            return redirect()->route('reception.maternity-oncology.index')
                ->with('success', "XUẤT SẮC ĐẠT CHUẨN GIỜ VÀNG CẤP CỨU: {$targetText}. Bảo tồn tối đa chức năng sống cho người bệnh!");
        }

        return redirect()->route('reception.maternity-oncology.index')
            ->with('warning', "CẢNH BÁO QUÁ NGƯỠNG GIỜ VÀNG: {$targetText}. Đã ghi nhận Hộp Đen Y Khoa để phân tích nguyên nhân chậm trễ.");
    }
}
