<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\EquipmentLoan;
use App\Models\MobilityEquipment;
use App\Models\PatientProfile;
use App\Services\MobilityEquipmentService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MobilityEquipmentController extends Controller
{
    public function __construct(
        protected MobilityEquipmentService $equipmentService
    ) {}

    public function index(Request $request): View
    {
        $equipments = MobilityEquipment::with(['loans' => function ($q) {
            $q->where('status', EquipmentLoan::STATUS_BORROWED);
        }])->get();

        $recentLoans = EquipmentLoan::with(['equipment', 'patientProfile', 'dispatchedBy', 'receivedBy'])
            ->latest()
            ->paginate(15);

        $stats = [
            'total' => MobilityEquipment::count(),
            'available' => MobilityEquipment::where('status', MobilityEquipment::STATUS_AVAILABLE)->count(),
            'in_use' => MobilityEquipment::where('status', MobilityEquipment::STATUS_IN_USE)->count(),
            'disinfecting' => MobilityEquipment::where('status', MobilityEquipment::STATUS_DISINFECTING)->count(),
            'wheelchairs' => MobilityEquipment::whereIn('device_type', [MobilityEquipment::TYPE_WHEELCHAIR, MobilityEquipment::TYPE_ELECTRIC_WHEELCHAIR])->count(),
        ];

        $patients = PatientProfile::latest()->take(30)->get();

        return view('receptionist.mobility_equipments.index', compact('equipments', 'recentLoans', 'stats', 'patients'));
    }

    public function borrow(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mobility_equipment_id' => 'required|exists:mobility_equipments,id',
            'borrower_name' => 'required|string|max:150',
            'borrower_phone' => 'required|string|max:20',
            'patient_profile_id' => 'nullable|exists:patient_profiles,id',
            'id_card_held' => 'nullable|string|max:50',
            'destination_department' => 'required|string|max:100',
        ]);

        try {
            $loan = $this->equipmentService->borrowEquipment(
                $validated['mobility_equipment_id'],
                $validated,
                Auth::id()
            );

            return back()->with('success', "Đã bàn giao {$loan->equipment->name} ({$loan->equipment->device_code}) cho người mượn {$loan->borrower_name}!");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function return(Request $request, EquipmentLoan $loan): RedirectResponse
    {
        $validated = $request->validate([
            'disinfection_notes' => 'nullable|string|max:255',
        ]);

        $this->equipmentService->returnEquipment(
            $loan,
            $validated['disinfection_notes'] ?? 'Bệnh nhân trả tại sảnh tiếp đón',
            Auth::id()
        );

        return back()->with('success', "Đã tiếp nhận trả thiết bị {$loan->equipment->device_code}. Thiết bị đã được đưa vào khu vực khử khuẩn vô trùng!");
    }

    public function completeDisinfection(MobilityEquipment $equipment): RedirectResponse
    {
        $this->equipmentService->completeDisinfection($equipment, Auth::id());

        return back()->with('success', "Đã ký nghiệm thu khử khuẩn bề mặt cho {$equipment->device_code}. Thiết bị đã sẵn sàng phục vụ người bệnh tiếp theo!");
    }
}
