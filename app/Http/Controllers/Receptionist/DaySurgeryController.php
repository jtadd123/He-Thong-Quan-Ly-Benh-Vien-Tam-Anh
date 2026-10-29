<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\ClinicalAuditLog;
use App\Models\DaySurgery;
use App\Models\Doctor;
use App\Models\PatientProfile;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DaySurgeryController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $query = DaySurgery::with(['patientProfile', 'surgeon.user', 'checkedBy'])
            ->latest('scheduled_at');

        if ($status) {
            $query->where('status', $status);
        }

        $surgeries = $query->paginate(15)->withQueryString();
        $patients = PatientProfile::latest('id')->limit(50)->get();
        $doctors = Doctor::with('user')->get();

        $stats = [
            'total_surgeries' => DaySurgery::count(),
            'scheduled' => DaySurgery::where('status', 'scheduled')->count(),
            'ready' => DaySurgery::where('status', 'pre_op_ready')->count(),
            'in_surgery' => DaySurgery::where('status', 'in_surgery')->count(),
            'recovery' => DaySurgery::where('status', 'recovery')->count(),
            'discharged' => DaySurgery::where('status', 'discharged')->count(),
        ];

        return view('receptionist.day_surgeries.index', compact('surgeries', 'patients', 'doctors', 'stats', 'status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_profile_id' => ['required', 'exists:patient_profiles,id'],
            'procedure_name' => ['required', 'string', 'max:200'],
            'surgeon_doctor_id' => ['required', 'exists:doctors,id'],
            'scheduled_at' => ['required', 'date'],
            'operating_room' => ['required', 'string', 'max:50'],
            'fasting_hours' => ['required', 'integer', 'min:4', 'max:24'],
            'pre_op_notes' => ['nullable', 'string'],
        ]);

        $patient = PatientProfile::findOrFail($validated['patient_profile_id']);
        $surgeryCode = DaySurgery::generateSurgeryCode();

        $surgery = DaySurgery::create(array_merge($validated, [
            'surgery_code' => $surgeryCode,
            'checklist_passed' => false,
            'status' => 'scheduled',
            'checked_by_user_id' => Auth::id(),
        ]));

        ClinicalAuditLog::record(
            $patient,
            'day_surgery_scheduled',
            "Đặt lịch phẫu thuật trong ngày: {$surgery->procedure_name} (Mã: {$surgery->surgery_code}) lúc {$surgery->scheduled_at->format('H:i d/m/Y')}"
        );

        return back()->with('success', "Đã đặt lịch phẫu thuật trong ngày: {$surgery->surgery_code}");
    }

    public function updateChecklist(Request $request, DaySurgery $surgery): RedirectResponse
    {
        $validated = $request->validate([
            'checklist_passed' => ['required', 'boolean'],
            'pre_op_notes' => ['nullable', 'string'],
        ]);

        $surgery->update([
            'checklist_passed' => $validated['checklist_passed'],
            'status' => $validated['checklist_passed'] ? 'pre_op_ready' : 'scheduled',
            'pre_op_notes' => $validated['pre_op_notes'],
            'checked_by_user_id' => Auth::id(),
        ]);

        ClinicalAuditLog::record(
            $surgery->patientProfile,
            'day_surgery_checklist',
            "Hoàn tất Bảng kiểm an toàn tiền phẫu WHO (Bước Tiếp nhận) cho ca {$surgery->surgery_code}: Sẵn sàng vào phòng mổ"
        );

        return back()->with('success', "Đã xác nhận hoàn tất Checklist an toàn phẫu thuật WHO! Người bệnh sẵn sàng vào phòng mổ.");
    }

    public function updateStatus(Request $request, DaySurgery $surgery): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:in_surgery,recovery,discharged,cancelled'],
        ]);

        $surgery->update(['status' => $validated['status']]);

        return back()->with('success', "Đã cập nhật trạng thái phẫu thuật trong ngày thành công!");
    }
}
