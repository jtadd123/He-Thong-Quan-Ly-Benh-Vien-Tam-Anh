<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DayBed;
use App\Models\DayBedAdmission;
use App\Models\Doctor;
use App\Models\IvInfusion;
use App\Models\PatientProfile;
use App\Services\DayBedService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DayBedController extends Controller
{
    public function __construct(
        protected DayBedService $dayBedService
    ) {}

    public function index(Request $request): View
    {
        $roomQuery = DayBed::query();

        if ($request->filled('room')) {
            $roomQuery->where('room_name', $request->room);
        }

        if ($request->filled('type')) {
            $roomQuery->where('bed_type', $request->type);
        }

        if ($request->filled('status')) {
            $roomQuery->where('status', $request->status);
        }

        $beds = $roomQuery->with(['currentAdmission.patientProfile', 'currentAdmission.ivInfusions'])->orderBy('room_name')->orderBy('bed_code')->get();

        // Thống kê công suất giường
        $stats = [
            'total' => DayBed::count(),
            'available' => DayBed::where('status', DayBed::STATUS_AVAILABLE)->count(),
            'occupied' => DayBed::where('status', DayBed::STATUS_OCCUPIED)->count(),
            'cleaning' => DayBed::where('status', DayBed::STATUS_CLEANING)->count(),
            'overstay' => DayBedAdmission::where('status', DayBedAdmission::STATUS_ACTIVE)
                ->where('admitted_at', '<=', Carbon::now()->subHours(4))
                ->count(),
        ];

        $rooms = DayBed::select('room_name')->distinct()->pluck('room_name');
        $doctors = Doctor::with('user')->get();
        $patients = PatientProfile::latest()->take(30)->get();
        $appointments = Appointment::whereDate('appointment_date', today())->with('patientProfile')->get();

        return view('receptionist.day_beds.index', compact('beds', 'stats', 'rooms', 'doctors', 'patients', 'appointments'));
    }

    public function storeAdmission(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'day_bed_id' => 'required|exists:day_beds,id',
            'patient_profile_id' => 'required|exists:patient_profiles,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'attending_doctor_id' => 'nullable|exists:doctors,id',
            'attending_nurse_name' => 'nullable|string|max:100',
            'admission_reason' => 'required|string|max:1000',
            'stay_hours' => 'nullable|integer|min:1|max:24',
            'fluid_name' => 'nullable|string|max:150',
            'volume_ml' => 'nullable|integer|min:50|max:2000',
            'drip_rate_drops_per_min' => 'nullable|integer|min:10|max:120',
            'infusion_notes' => 'nullable|string|max:500',
        ]);

        try {
            $admission = $this->dayBedService->admitPatient($validated);
            return redirect()->route('reception.day-beds.index')
                ->with('success', "Đã tiếp nhận bệnh nhân {$admission->patientProfile->full_name} vào giường {$admission->dayBed->bed_code} thành công!");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function showAdmission(DayBedAdmission $admission): View
    {
        $admission->load(['dayBed', 'patientProfile.vitals', 'appointment', 'attendingDoctor.user', 'ivInfusions']);
        return view('receptionist.day_beds.show', compact('admission'));
    }

    public function storeInfusion(Request $request, DayBedAdmission $admission): RedirectResponse
    {
        $validated = $request->validate([
            'fluid_name' => 'required|string|max:150',
            'volume_ml' => 'required|integer|min:50|max:2000',
            'drip_rate_drops_per_min' => 'required|integer|min:10|max:120',
            'nurse_name' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $this->dayBedService->addIvInfusion($admission, $validated);

        return back()->with('success', "Đã thêm chỉ định truyền {$validated['fluid_name']} ({$validated['volume_ml']}ml) vào bảng theo dõi!");
    }

    public function completeInfusion(IvInfusion $infusion): RedirectResponse
    {
        $infusion->update(['status' => IvInfusion::STATUS_COMPLETED]);
        return back()->with('success', "Đã xác nhận hoàn tất chai dịch truyền {$infusion->fluid_name}!");
    }

    public function discharge(Request $request, DayBedAdmission $admission): RedirectResponse
    {
        $dischargeNotes = $request->input('discharge_notes', 'Tình trạng sinh hiệu ổn định, cho về ngoại trú');
        $this->dayBedService->dischargePatient($admission, $dischargeNotes);

        return redirect()->route('reception.day-beds.index')
            ->with('success', "Đã xuất giường cho bệnh nhân {$admission->patientProfile->full_name}. Giường {$admission->dayBed->bed_code} được chuyển sang trạng thái chờ khử khuẩn!");
    }

    public function finishCleaning(DayBed $bed): RedirectResponse
    {
        $this->dayBedService->finishCleaning($bed);
        return back()->with('success', "Đã xác nhận khử trùng sạch sẽ giường {$bed->bed_code}. Giường đã sẵn sàng tiếp nhận ca mới!");
    }
}
