<?php

namespace App\Http\Controllers;

use App\Models\OperatingRoom;
use App\Models\SurgicalCase;
use App\Models\SurgicalSpongeCount;
use App\Models\PacuAldreteScore;
use App\Models\PatientProfile;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OperatingRoomSuiteController extends Controller
{
    /**
     * Dashboard Quản lý Phòng mổ thông minh & PACU
     */
    public function index(Request $request)
    {
        $rooms = OperatingRoom::withCount(['surgicalCases' => function ($q) {
            $q->whereDate('scheduled_start', Carbon::today())
              ->whereNotIn('phase', ['discharged', 'cancelled']);
        }])->get();

        // Tự động seed vài phòng mổ nếu chưa có
        if ($rooms->isEmpty()) {
            OperatingRoom::create([
                'room_number' => 'OR-01',
                'room_name' => 'Phòng Mổ Hybrid Can Thiệp Tim Mạch & Mạch Máu',
                'zone' => 'Khu vô trùng áp lực dương Tầng 3',
                'cleanliness_class' => 'ISO_5',
                'status' => 'available',
                'has_laminar_flow' => true,
            ]);
            OperatingRoom::create([
                'room_number' => 'OR-02',
                'room_name' => 'Phòng Mổ Nội Soi Vi Phẫu Thần Kinh - Cột Sống',
                'zone' => 'Khu vô trùng Tầng 3',
                'cleanliness_class' => 'ISO_5',
                'status' => 'in_use',
                'has_laminar_flow' => true,
            ]);
            OperatingRoom::create([
                'room_number' => 'OR-03',
                'room_name' => 'Phòng Mổ Chấn Thương Chỉnh Hình & Ghép Khớp Robot',
                'zone' => 'Khu vô trùng Tầng 3',
                'cleanliness_class' => 'ISO_6',
                'status' => 'available',
                'has_laminar_flow' => true,
            ]);
            $rooms = OperatingRoom::all();
        }

        $query = SurgicalCase::with(['patient', 'room', 'leadSurgeon', 'anesthesiologist', 'spongeCounts', 'aldreteScores'])
            ->orderBy('scheduled_start', 'asc');

        if ($request->filled('phase')) {
            $query->where('phase', $request->phase);
        }

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        $cases = $query->paginate(15);
        $patients = PatientProfile::orderBy('full_name')->limit(50)->get();
        $doctors = Doctor::with('user')->get();

        return view('reception.operating_room.index', compact('rooms', 'cases', 'patients', 'doctors'));
    }

    /**
     * Đăng ký ca phẫu thuật mới
     */
    public function storeCase(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'room_id' => 'required|exists:operating_rooms,id',
            'lead_surgeon_id' => 'required|exists:doctors,id',
            'anesthesiologist_id' => 'nullable|exists:doctors,id',
            'surgical_procedure' => 'required|string|max:255',
            'icd10_code' => 'nullable|string|max:20',
            'priority' => 'required|in:elective,urgent,emergency',
            'scheduled_start' => 'required|date',
            'surgical_notes' => 'nullable|string',
        ]);

        $code = 'SURG-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        $case = SurgicalCase::create([
            'case_code' => $code,
            'patient_id' => $validated['patient_id'],
            'room_id' => $validated['room_id'],
            'lead_surgeon_id' => $validated['lead_surgeon_id'],
            'anesthesiologist_id' => $validated['anesthesiologist_id'] ?? null,
            'surgical_procedure' => $validated['surgical_procedure'],
            'icd10_code' => $validated['icd10_code'] ?? null,
            'priority' => $validated['priority'],
            'phase' => 'scheduled',
            'scheduled_start' => $validated['scheduled_start'],
            'surgical_notes' => $validated['surgical_notes'] ?? null,
        ]);

        // Đổi trạng thái phòng mổ nếu là ca cấp cứu
        if ($validated['priority'] === 'emergency') {
            OperatingRoom::where('id', $validated['room_id'])->update(['status' => 'in_use']);
        }

        return redirect()->route('reception.operating-room.index')
            ->with('success', "Đã lên lịch ca phẫu thuật {$code} thành công!");
    }

    /**
     * Cập nhật bước tiến trình ca mổ (Surgical Phase Transition)
     */
    public function updatePhase(Request $request, $id)
    {
        $case = SurgicalCase::findOrFail($id);

        $validated = $request->validate([
            'phase' => 'required|in:scheduled,pre_op,anesthesia,surgery,hemostasis,pacu,discharged,cancelled',
        ]);

        $now = Carbon::now();
        $updates = ['phase' => $validated['phase']];

        if ($validated['phase'] === 'surgery' && !$case->actual_start) {
            $updates['actual_start'] = $now;
            if ($case->room_id) {
                OperatingRoom::where('id', $case->room_id)->update(['status' => 'in_use']);
            }
        }

        if ($validated['phase'] === 'pacu') {
            $updates['actual_end'] = $now;
            if ($case->room_id) {
                OperatingRoom::where('id', $case->room_id)->update(['status' => 'cleaning']);
            }
        }

        if ($validated['phase'] === 'discharged') {
            if ($case->room_id) {
                OperatingRoom::where('id', $case->room_id)->update(['status' => 'available']);
            }
        }

        $case->update($updates);

        return redirect()->back()->with('success', "Ca mổ {$case->case_code} đã chuyển sang giai đoạn: " . strtoupper($validated['phase']));
    }

    /**
     * Ghi nhận kiểm đếm gạc và dụng cụ mổ (Sponge & Instrument Count Protocol)
     */
    public function recordSpongeCount(Request $request, $id)
    {
        $case = SurgicalCase::findOrFail($id);

        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'initial_count' => 'required|integer|min:0',
            'added_count' => 'required|integer|min:0',
            'before_closure_count' => 'required|integer|min:0',
            'final_count' => 'required|integer|min:0',
            'scrub_nurse_name' => 'required|string|max:100',
            'circulating_nurse_name' => 'required|string|max:100',
            'discrepancy_reason' => 'nullable|string',
        ]);

        // Logic an toàn JCI: Số lượng trước mở + thêm vào PHẢI BẰNG số lượng trước đóng và kết thúc ca
        $totalInput = $validated['initial_count'] + $validated['added_count'];
        $isMatched = ($totalInput === $validated['before_closure_count']) && ($validated['before_closure_count'] === $validated['final_count']);

        SurgicalSpongeCount::create([
            'surgical_case_id' => $case->id,
            'item_name' => $validated['item_name'],
            'initial_count' => $validated['initial_count'],
            'added_count' => $validated['added_count'],
            'before_closure_count' => $validated['before_closure_count'],
            'final_count' => $validated['final_count'],
            'is_matched' => $isMatched,
            'scrub_nurse_name' => $validated['scrub_nurse_name'],
            'circulating_nurse_name' => $validated['circulating_nurse_name'],
            'discrepancy_reason' => $isMatched ? null : ($validated['discrepancy_reason'] ?? 'CẢNH BÁO: Lệch số lượng gạc phẫu thuật!'),
        ]);

        if (!$isMatched) {
            return redirect()->back()->with('error', "CẢNH BÁO AN TOÀN PHẪU THUẬT: Số lượng {$validated['item_name']} không khớp! Cần chụp X-quang kiểm tra trước khi đóng vết mổ.");
        }

        return redirect()->back()->with('success', "Đã xác nhận kiểm đếm chính xác 100% cho: {$validated['item_name']}");
    }

    /**
     * Chấm điểm hồi tỉnh PACU Aldrete
     */
    public function recordAldreteScore(Request $request, $id)
    {
        $case = SurgicalCase::findOrFail($id);

        $validated = $request->validate([
            'respiration_score' => 'required|integer|between:0,2',
            'circulation_score' => 'required|integer|between:0,2',
            'consciousness_score' => 'required|integer|between:0,2',
            'activity_score' => 'required|integer|between:0,2',
            'color_score' => 'required|integer|between:0,2',
            'evaluated_by' => 'required|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $total = $validated['respiration_score'] + $validated['circulation_score'] + $validated['consciousness_score'] + $validated['activity_score'] + $validated['color_score'];
        $isEligible = ($total >= 9);

        PacuAldreteScore::create([
            'surgical_case_id' => $case->id,
            'respiration_score' => $validated['respiration_score'],
            'circulation_score' => $validated['circulation_score'],
            'consciousness_score' => $validated['consciousness_score'],
            'activity_score' => $validated['activity_score'],
            'color_score' => $validated['color_score'],
            'total_score' => $total,
            'is_eligible_for_discharge' => $isEligible,
            'evaluated_by' => $validated['evaluated_by'],
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($isEligible) {
            return redirect()->back()->with('success', "Điểm Aldrete: {$total}/10. Bệnh nhân ĐỦ ĐIỀU KIỆN an toàn xuất phòng hồi tỉnh PACU về khoa nội trú.");
        }

        return redirect()->back()->with('warning', "Điểm Aldrete: {$total}/10 (< 9 điểm). Cần tiếp tục theo dõi hồi tỉnh tại PACU.");
    }

    /**
     * Màn hình sảnh chờ theo dõi ca mổ cho thân nhân (Mã hóa bảo mật danh tính)
     */
    public function publicWaitingBoard()
    {
        $todayCases = SurgicalCase::with(['room', 'leadSurgeon', 'patient'])
            ->whereDate('scheduled_start', Carbon::today())
            ->whereNotIn('phase', ['discharged', 'cancelled'])
            ->orderBy('scheduled_start', 'asc')
            ->get();

        return view('reception.operating_room.public_waiting_board', compact('todayCases'));
    }
}
