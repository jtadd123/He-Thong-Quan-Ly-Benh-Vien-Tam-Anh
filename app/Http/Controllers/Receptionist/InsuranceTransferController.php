<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\ClinicalAuditLog;
use App\Models\InsuranceTransfer;
use App\Models\PatientProfile;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InsuranceTransferController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $query = InsuranceTransfer::with(['patientProfile', 'registeredBy'])
            ->latest('id');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('transfer_code', 'LIKE', "%{$search}%")
                  ->orWhere('issuing_hospital_name', 'LIKE', "%{$search}%")
                  ->orWhere('icd10_diagnosis', 'LIKE', "%{$search}%")
                  ->orWhereHas('patientProfile', function ($p) use ($search) {
                      $p->where('full_name', 'LIKE', "%{$search}%")
                        ->orWhere('patient_code', 'LIKE', "%{$search}%")
                        ->orWhere('insurance_code', 'LIKE', "%{$search}%");
                  });
            });
        }

        $transfers = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => InsuranceTransfer::count(),
            'valid' => InsuranceTransfer::where('status', 'valid')->where('valid_to', '>=', Carbon::today())->count(),
            'used' => InsuranceTransfer::where('status', 'used')->count(),
            'expired' => InsuranceTransfer::where('valid_to', '<', Carbon::today())->count(),
        ];

        return view('receptionist.insurance_transfers.index', compact('transfers', 'search', 'status', 'stats'));
    }

    public function create(Request $request): View
    {
        $selectedPatient = null;
        if ($request->has('patient_id')) {
            $selectedPatient = PatientProfile::find($request->query('patient_id'));
        }

        $patients = PatientProfile::whereNotNull('insurance_code')
            ->where('insurance_code', '!=', '')
            ->latest('id')
            ->limit(100)
            ->get();

        return view('receptionist.insurance_transfers.create', compact('selectedPatient', 'patients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_profile_id' => ['required', 'exists:patient_profiles,id'],
            'issuing_hospital_code' => ['required', 'string', 'max:20'],
            'issuing_hospital_name' => ['required', 'string', 'max:200'],
            'transfer_type' => ['required', 'in:right_route,emergency,patient_request'],
            'icd10_code' => ['nullable', 'string', 'max:20'],
            'icd10_diagnosis' => ['required', 'string', 'max:255'],
            'clinical_summary' => ['nullable', 'string'],
            'valid_from' => ['required', 'date'],
            'valid_to' => ['required', 'date', 'after_or_equal:valid_from'],
            'bhyt_reimbursement_rate' => ['required', 'integer', 'in:100,80,40'],
        ]);

        $patient = PatientProfile::findOrFail($validated['patient_profile_id']);

        if (empty($patient->insurance_code)) {
            return back()->withInput()->with('error', 'Bệnh nhân này chưa đăng ký mã thẻ BHYT trong hồ sơ. Vui lòng cập nhật thẻ BHYT trước.');
        }

        $transferCode = InsuranceTransfer::generateTransferCode();

        $transfer = InsuranceTransfer::create(array_merge($validated, [
            'transfer_code' => $transferCode,
            'status' => 'valid',
            'registered_by_user_id' => Auth::id(),
        ]));

        ClinicalAuditLog::record(
            $patient,
            'insurance_transfer',
            "Tiếp nhận giấy chuyển tuyến BHYT số {$transfer->transfer_code} từ {$transfer->issuing_hospital_name} (Hưởng {$transfer->bhyt_reimbursement_rate}% BHYT)"
        );

        return redirect()->route('reception.insurance-transfers.show', $transfer)
            ->with('success', "Đã tiếp nhận thành công giấy chuyển tuyến BHYT: {$transfer->transfer_code}");
    }

    public function show(InsuranceTransfer $insuranceTransfer): View
    {
        $insuranceTransfer->load(['patientProfile', 'appointment', 'registeredBy']);
        return view('receptionist.insurance_transfers.show', compact('insuranceTransfer'));
    }

    public function print(InsuranceTransfer $insuranceTransfer): View
    {
        $insuranceTransfer->load(['patientProfile', 'registeredBy']);
        return view('receptionist.insurance_transfers.print', compact('insuranceTransfer'));
    }
}
