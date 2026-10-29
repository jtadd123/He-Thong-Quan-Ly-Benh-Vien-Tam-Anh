<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\ClinicalAuditLog;
use App\Models\MedicalRecord;
use App\Models\MedicalRecordRequest;
use App\Models\PatientProfile;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MedicalRecordRequestController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $query = MedicalRecordRequest::with(['patientProfile', 'medicalRecord', 'approvedBy'])
            ->latest('id');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('request_code', 'LIKE', "%{$search}%")
                  ->orWhere('requester_name', 'LIKE', "%{$search}%")
                  ->orWhere('requester_identity_card', 'LIKE', "%{$search}%")
                  ->orWhereHas('patientProfile', function ($p) use ($search) {
                      $p->where('full_name', 'LIKE', "%{$search}%")
                        ->orWhere('patient_code', 'LIKE', "%{$search}%");
                  });
            });
        }

        $requests = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => MedicalRecordRequest::count(),
            'pending' => MedicalRecordRequest::whereIn('status', ['submitted', 'reviewing'])->count(),
            'approved' => MedicalRecordRequest::where('status', 'approved')->count(),
            'completed' => MedicalRecordRequest::where('status', 'printed')->count(),
        ];

        return view('receptionist.record_requests.index', compact('requests', 'search', 'status', 'stats'));
    }

    public function create(Request $request): View
    {
        $selectedPatient = null;
        if ($request->has('patient_id')) {
            $selectedPatient = PatientProfile::find($request->query('patient_id'));
        }

        $patients = PatientProfile::has('medicalRecords')
            ->latest('id')
            ->limit(100)
            ->get();

        return view('receptionist.record_requests.create', compact('selectedPatient', 'patients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_profile_id' => ['required', 'exists:patient_profiles,id'],
            'medical_record_id' => ['nullable', 'exists:medical_records,id'],
            'requester_type' => ['required', 'in:patient_self,guardian,law_enforcement,insurance_assessor'],
            'requester_name' => ['required', 'string', 'max:100'],
            'requester_phone' => ['required', 'string', 'max:20'],
            'requester_identity_card' => ['required', 'string', 'max:20'],
            'relationship_to_patient' => ['nullable', 'string', 'max:50'],
            'purpose' => ['required', 'string'],
            'copies_count' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $patient = PatientProfile::findOrFail($validated['patient_profile_id']);
        $requestCode = MedicalRecordRequest::generateRequestCode();

        $recordRequest = MedicalRecordRequest::create(array_merge($validated, [
            'request_code' => $requestCode,
            'status' => 'submitted',
        ]));

        ClinicalAuditLog::record(
            $patient,
            'medical_record_request',
            "Tiếp nhận đơn đề nghị sao lục bệnh án số {$recordRequest->request_code} từ {$recordRequest->requester_name} (Mục đích: {$recordRequest->purpose})"
        );

        return redirect()->route('reception.record-requests.show', $recordRequest)
            ->with('success', "Đã tiếp nhận đơn đề nghị sao lục hồ sơ bệnh án: {$recordRequest->request_code}");
    }

    public function show(MedicalRecordRequest $recordRequest): View
    {
        $recordRequest->load(['patientProfile.medicalRecords', 'medicalRecord', 'approvedBy']);
        return view('receptionist.record_requests.show', compact('recordRequest'));
    }

    public function updateStatus(Request $request, MedicalRecordRequest $recordRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:reviewing,approved,printed,rejected'],
            'rejection_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'approved') {
            $updateData['approved_by_user_id'] = Auth::id();
        } elseif ($validated['status'] === 'printed') {
            $updateData['completed_at'] = Carbon::now();
        } elseif ($validated['status'] === 'rejected') {
            $updateData['rejection_reason'] = $validated['rejection_reason'] ?? 'Không đủ căn cứ pháp lý theo Luật Khám bệnh, chữa bệnh.';
        }

        $recordRequest->update($updateData);

        return back()->with('success', "Cập nhật trạng thái đề nghị sao lục hồ sơ thành công!");
    }

    public function print(MedicalRecordRequest $recordRequest): View
    {
        $recordRequest->load(['patientProfile.medicalRecords.doctor.user', 'medicalRecord', 'approvedBy']);
        return view('receptionist.record_requests.print', compact('recordRequest'));
    }
}
