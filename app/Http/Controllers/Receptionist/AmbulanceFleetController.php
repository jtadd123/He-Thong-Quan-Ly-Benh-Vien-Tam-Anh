<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Ambulance;
use App\Models\AmbulanceMission;
use App\Models\ClinicalAuditLog;
use App\Models\PatientProfile;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AmbulanceFleetController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $ambulances = Ambulance::withCount(['missions' => function ($q) {
            $q->whereIn('status', ['dispatched', 'en_route', 'on_scene', 'transporting']);
        }])->get();

        // Seed 3 xe cứu thương mặc định nếu chưa có
        if ($ambulances->isEmpty()) {
            Ambulance::create([
                'license_plate' => '29A-115.88',
                'ambulance_type' => 'mobile_icu',
                'driver_name' => 'Nguyễn Văn Mạnh',
                'driver_phone' => '0912115115',
                'status' => 'available',
                'current_location' => 'Sảnh Cấp Cứu A - BV Tâm Anh',
            ]);
            Ambulance::create([
                'license_plate' => '29B-999.11',
                'ambulance_type' => 'mobile_icu',
                'driver_name' => 'Trần Quốc Bảo',
                'driver_phone' => '0988115115',
                'status' => 'available',
                'current_location' => 'Sảnh Cấp Cứu B - BV Tâm Anh',
            ]);
            Ambulance::create([
                'license_plate' => '30E-222.33',
                'ambulance_type' => 'standard',
                'driver_name' => 'Lê Hữu Đạt',
                'driver_phone' => '0933115115',
                'status' => 'available',
                'current_location' => 'Bãi đỗ xe cứu thương trung tâm',
            ]);
            $ambulances = Ambulance::all();
        }

        $query = AmbulanceMission::with(['ambulance', 'patientProfile', 'dispatchedBy'])
            ->latest('id');

        if ($status) {
            $query->where('status', $status);
        }

        $missions = $query->paginate(15)->withQueryString();
        $patients = PatientProfile::latest('id')->limit(50)->get();

        $stats = [
            'total_ambulances' => $ambulances->count(),
            'available_ambulances' => $ambulances->where('status', 'available')->count(),
            'active_missions' => AmbulanceMission::whereIn('status', ['dispatched', 'en_route', 'on_scene', 'transporting'])->count(),
            'completed_missions' => AmbulanceMission::where('status', 'completed')->count(),
        ];

        return view('receptionist.ambulance_fleet.index', compact('ambulances', 'missions', 'patients', 'stats', 'status'));
    }

    public function storeMission(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ambulance_id' => ['required', 'exists:ambulances,id'],
            'patient_profile_id' => ['nullable', 'exists:patient_profiles,id'],
            'caller_name' => ['required', 'string', 'max:100'],
            'caller_phone' => ['required', 'string', 'max:20'],
            'pickup_address' => ['required', 'string', 'max:255'],
            'destination_address' => ['required', 'string', 'max:255'],
            'mission_type' => ['required', 'in:home_pickup,transfer_out,repatriation'],
            'distance_km' => ['required', 'numeric', 'min:1'],
            'doctor_in_charge' => ['nullable', 'string', 'max:100'],
            'nurse_in_charge' => ['nullable', 'string', 'max:100'],
        ]);

        $ambulance = Ambulance::findOrFail($validated['ambulance_id']);

        // Giá cước chuẩn: 500.000 VNĐ mở cửa + 25.000 VNĐ/km (Xe Mobile ICU thêm phụ phí 500.000 VNĐ)
        $baseRate = $ambulance->ambulance_type === 'mobile_icu' ? 1000000 : 500000;
        $kmRate = $validated['distance_km'] * 25000;
        $totalCost = $baseRate + $kmRate;

        $missionCode = AmbulanceMission::generateMissionCode();

        $mission = AmbulanceMission::create(array_merge($validated, [
            'mission_code' => $missionCode,
            'total_cost' => $totalCost,
            'status' => 'dispatched',
            'dispatched_at' => Carbon::now(),
            'dispatched_by_user_id' => Auth::id(),
        ]));

        $ambulance->update(['status' => 'dispatched']);

        if (!empty($validated['patient_profile_id'])) {
            $patient = PatientProfile::find($validated['patient_profile_id']);
            if ($patient) {
                ClinicalAuditLog::record(
                    $patient,
                    'ambulance_dispatched',
                    "Điều động xe cấp cứu {$ambulance->license_plate} (Mã ca: {$mission->mission_code}) đón tại {$mission->pickup_address}"
                );
            }
        }

        return back()->with('success', "Đã điều phối xe cấp cứu {$ambulance->license_plate} xuất bến khẩn cấp! Mã chuyến: {$mission->mission_code}");
    }

    public function updateStatus(Request $request, AmbulanceMission $mission): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:en_route,on_scene,transporting,completed,cancelled'],
        ]);

        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'completed') {
            $updateData['completed_at'] = Carbon::now();
            $mission->ambulance->update(['status' => 'available']);
        } elseif ($validated['status'] === 'cancelled') {
            $mission->ambulance->update(['status' => 'available']);
        }

        $mission->update($updateData);

        return back()->with('success', "Cập nhật trạng thái lộ trình điều phối xe cấp cứu thành công!");
    }
}
