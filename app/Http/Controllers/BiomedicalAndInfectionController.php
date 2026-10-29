<?php

namespace App\Http\Controllers;

use App\Models\BiomedicalEquipment;
use App\Models\BiomedicalRepairRequest;
use App\Models\HaiSurveillance;
use App\Models\AntimicrobialApproval;
use App\Models\PatientProfile;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BiomedicalAndInfectionController extends Controller
{
    /**
     * Dashboard Kỹ Thuật Y Sinh CMMS & Kiểm Soát Nhiễm Khuẩn (HAI - ASP)
     */
    public function index(Request $request)
    {
        // Seed thiết bị mẫu nếu chưa có
        if (BiomedicalEquipment::count() === 0) {
            BiomedicalEquipment::create([
                'equipment_code' => 'MED-VENT-01',
                'name' => 'Máy Thở Đa Năng Puritan Bennett 980 Plus',
                'model' => 'PB-980',
                'serial_number' => 'PB980-VN-2024-88',
                'manufacturer' => 'Medtronic (USA)',
                'risk_class' => 'CLASS_D',
                'department' => 'Khoa Hồi Sức Tích Cực (ICU)',
                'operational_status' => 'ready',
                'last_calibration_date' => Carbon::now()->subMonths(3),
                'next_calibration_due' => Carbon::now()->addMonths(9),
                'is_calibration_expired' => false,
            ]);
            BiomedicalEquipment::create([
                'equipment_code' => 'MED-DEFIB-02',
                'name' => 'Máy Phá Rung Tim Zoll R Series Kèm Pacer Ngoài',
                'model' => 'R-Series ALS',
                'serial_number' => 'ZOLL-2023-991',
                'manufacturer' => 'Zoll Medical',
                'risk_class' => 'CLASS_D',
                'department' => 'Khoa Cấp Cứu A&E',
                'operational_status' => 'ready',
                'last_calibration_date' => Carbon::now()->subMonths(5),
                'next_calibration_due' => Carbon::now()->addMonths(7),
                'is_calibration_expired' => false,
            ]);
            BiomedicalEquipment::create([
                'equipment_code' => 'MED-XRAY-03',
                'name' => 'Máy Chụp X-Quang Kỹ Thuật Số Di Động Tại Giường MobileDaRt',
                'model' => 'MobileDaRt Evolution MX8',
                'serial_number' => 'SHIM-2024-001',
                'manufacturer' => 'Shimadzu (Japan)',
                'risk_class' => 'CLASS_C',
                'department' => 'Chẩn Đoán Hình Ảnh',
                'operational_status' => 'in_use',
                'last_calibration_date' => Carbon::now()->subMonths(1),
                'next_calibration_due' => Carbon::now()->addMonths(11),
                'is_calibration_expired' => false,
            ]);
        }

        $equipments = BiomedicalEquipment::withCount('repairRequests')
            ->orderBy('risk_class', 'desc')
            ->paginate(10, ['*'], 'eq_page');

        $repairRequests = BiomedicalRepairRequest::with('equipment')
            ->orderByRaw("FIELD(urgency, 'CRITICAL_LIFE_SAVING', 'URGENT', 'STANDARD')")
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'repair_page');

        $haiInfections = HaiSurveillance::with('patient')
            ->orderBy('identified_date', 'desc')
            ->paginate(10, ['*'], 'hai_page');

        $antimicrobialApprovals = AntimicrobialApproval::with('patient')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'asp_page');

        $patients = PatientProfile::orderBy('full_name')->limit(50)->get();

        return view('reception.biomedical_infection.index', compact(
            'equipments',
            'repairRequests',
            'haiInfections',
            'antimicrobialApprovals',
            'patients'
        ));
    }

    /**
     * Thêm mới thiết bị y sinh theo Nghị định 98/2021/NĐ-CP
     */
    public function storeEquipment(Request $request)
    {
        $validated = $request->validate([
            'equipment_code' => 'required|string|max:50|unique:biomedical_equipments,equipment_code',
            'name' => 'required|string|max:255',
            'model' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'manufacturer' => 'nullable|string|max:100',
            'risk_class' => 'required|in:CLASS_A,CLASS_B,CLASS_C,CLASS_D',
            'department' => 'required|string|max:100',
            'last_calibration_date' => 'nullable|date',
            'next_calibration_due' => 'required|date',
        ]);

        $isExpired = Carbon::parse($validated['next_calibration_due'])->isPast();

        BiomedicalEquipment::create([
            'equipment_code' => $validated['equipment_code'],
            'name' => $validated['name'],
            'model' => $validated['model'] ?? null,
            'serial_number' => $validated['serial_number'] ?? null,
            'manufacturer' => $validated['manufacturer'] ?? null,
            'risk_class' => $validated['risk_class'],
            'department' => $validated['department'],
            'operational_status' => $isExpired ? 'out_of_service' : 'ready',
            'last_calibration_date' => $validated['last_calibration_date'] ?? null,
            'next_calibration_due' => $validated['next_calibration_due'],
            'is_calibration_expired' => $isExpired,
        ]);

        return redirect()->route('reception.biomedical-infection.index')
            ->with('success', "Đã thêm thiết bị y tế {$validated['name']} ({$validated['equipment_code']}) thành công!");
    }

    /**
     * Báo hỏng thiết bị y sinh SOS (Biomedical SOS Dispatch)
     */
    public function reportRepairSos(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:biomedical_equipments,id',
            'reporter_name' => 'required|string|max:100',
            'urgency' => 'required|in:CRITICAL_LIFE_SAVING,URGENT,STANDARD',
            'fault_description' => 'required|string',
        ]);

        // Đổi trạng thái máy sang under_repair
        BiomedicalEquipment::where('id', $validated['equipment_id'])->update(['operational_status' => 'under_repair']);

        BiomedicalRepairRequest::create([
            'equipment_id' => $validated['equipment_id'],
            'reporter_name' => $validated['reporter_name'],
            'urgency' => $validated['urgency'],
            'fault_description' => $validated['fault_description'],
            'status' => 'pending',
        ]);

        $slaMsg = match($validated['urgency']) {
            'CRITICAL_LIFE_SAVING' => 'SLA PHẢN ỨNG KHẨN CẤP: Kỹ sư y sinh có mặt trong vòng 5 PHÚT!',
            'URGENT' => 'SLA PHẢN ỨNG NHANH: Kỹ sư y sinh tiếp nhận trong 30 PHÚT.',
            default => 'Đã tiếp nhận yêu cầu sửa chữa trong ca trực.',
        };

        return redirect()->route('reception.biomedical-infection.index')
            ->with('success', "Đã phát lệnh báo hỏng thiết bị SOS. {$slaMsg}");
    }

    /**
     * Ghi nhận ca nhiễm khuẩn bệnh viện (HAI Surveillance)
     */
    public function recordHaiInfection(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'infection_type' => 'required|in:VAP,CLABSI,CAUTI,SSI',
            'pathogen_detected' => 'nullable|string|max:255',
            'department' => 'required|string|max:100',
            'containment_action' => 'required|in:CONTACT_ISOLATION,DROPLET_ISOLATION,AIRBORNE_ISOLATION,STANDARD',
        ]);

        HaiSurveillance::create([
            'patient_id' => $validated['patient_id'],
            'infection_type' => $validated['infection_type'],
            'pathogen_detected' => $validated['pathogen_detected'] ?? 'Đang chờ nuôi cấy định danh',
            'department' => $validated['department'],
            'identified_date' => Carbon::today(),
            'containment_action' => $validated['containment_action'],
            'is_resolved' => false,
        ]);

        return redirect()->route('reception.biomedical-infection.index')
            ->with('error', "CẢNH BÁO NHIỄM KHUẨN BỆNH VIỆN ({$validated['infection_type']}): Đã kích hoạt chế độ {$validated['containment_action']} phòng chống lây nhiễm chéo toàn khoa!");
    }

    /**
     * Phê duyệt kháng sinh dự trữ 3 tầng (Antimicrobial Stewardship Program - ASP)
     */
    public function approveAntimicrobial(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'antibiotic_name' => 'required|string|max:100',
            'tier_level' => 'required|in:TIER_1_UNRESTRICTED,TIER_2_RESTRICTED,TIER_3_RESERVE',
            'prescribing_doctor' => 'required|string|max:100',
            'clinical_indication' => 'required|string',
            'pharmacist_approver' => 'nullable|string|max:100',
            'microbiologist_approver' => 'nullable|string|max:100',
        ]);

        $isReserve = ($validated['tier_level'] === 'TIER_3_RESERVE');
        $approved = false;
        $now = null;

        // Nếu có đủ cả 2 người ký duyệt thì chuyển sang approved
        if (!empty($validated['pharmacist_approver']) && !empty($validated['microbiologist_approver'])) {
            $approved = true;
            $now = Carbon::now();
        }

        AntimicrobialApproval::create([
            'patient_id' => $validated['patient_id'],
            'antibiotic_name' => $validated['antibiotic_name'],
            'tier_level' => $validated['tier_level'],
            'prescribing_doctor' => $validated['prescribing_doctor'],
            'clinical_indication' => $validated['clinical_indication'],
            'approval_status' => $approved ? 'approved' : 'pending',
            'pharmacist_approver' => $validated['pharmacist_approver'] ?? null,
            'microbiologist_approver' => $validated['microbiologist_approver'] ?? null,
            'approved_at' => $now,
        ]);

        if ($approved) {
            return redirect()->route('reception.biomedical-infection.index')
                ->with('success', "Đã ký duyệt kép thành công kháng sinh tối ưu {$validated['antibiotic_name']} cho người bệnh.");
        }

        return redirect()->route('reception.biomedical-infection.index')
            ->with('warning', "Đơn thuốc kháng sinh dự trữ {$validated['antibiotic_name']} đang ở trạng thái CHỜ DUYỆT KÉP (Dược sĩ lâm sàng + Bác sĩ Vi sinh) trước khi kho xuất thuốc.");
    }
}
