<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\ClinicalAuditLog;
use App\Models\PatientProfile;
use App\Models\PrivateInsuranceGuarantee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PrivateInsuranceController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $tpa = $request->query('tpa');

        $query = PrivateInsuranceGuarantee::with(['patientProfile', 'handledBy'])
            ->latest('id');

        if ($status) {
            $query->where('guarantee_status', $status);
        }

        if ($tpa) {
            $query->where('tpa_company', $tpa);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('gop_number', 'LIKE', "%{$search}%")
                  ->orWhere('card_number', 'LIKE', "%{$search}%")
                  ->orWhere('tpa_company', 'LIKE', "%{$search}%")
                  ->orWhereHas('patientProfile', function ($p) use ($search) {
                      $p->where('full_name', 'LIKE', "%{$search}%")
                        ->orWhere('patient_code', 'LIKE', "%{$search}%");
                  });
            });
        }

        $guarantees = $query->paginate(15)->withQueryString();

        $stats = [
            'total_gop' => PrivateInsuranceGuarantee::count(),
            'approved_count' => PrivateInsuranceGuarantee::where('guarantee_status', 'approved')->count(),
            'total_approved_amount' => PrivateInsuranceGuarantee::where('guarantee_status', 'approved')->sum('approved_amount'),
            'pending_count' => PrivateInsuranceGuarantee::where('guarantee_status', 'pending')->count(),
        ];

        return view('receptionist.private_insurance.index', compact('guarantees', 'search', 'status', 'tpa', 'stats'));
    }

    public function create(Request $request): View
    {
        $selectedPatient = null;
        if ($request->has('patient_id')) {
            $selectedPatient = PatientProfile::find($request->query('patient_id'));
        }

        $patients = PatientProfile::latest('id')->limit(100)->get();

        $tpaList = [
            'Insmart TPA',
            'Bảo Hiểm Bảo Việt',
            'Bảo Hiểm PTI',
            'Liberty Insurance',
            'Generali Vietnam',
            'Manulife Healthcare',
            'Prudential PruHealth',
            'Bảo Hiểm PVI',
            'Bảo Minh Care',
            'SOS International',
        ];

        return view('receptionist.private_insurance.create', compact('selectedPatient', 'patients', 'tpaList'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_profile_id' => ['required', 'exists:patient_profiles,id'],
            'tpa_company' => ['required', 'string', 'max:100'],
            'card_number' => ['required', 'string', 'max:50'],
            'coverage_limit' => ['required', 'numeric', 'min:0'],
            'approved_amount' => ['required', 'numeric', 'min:0'],
            'patient_copay_amount' => ['nullable', 'numeric', 'min:0'],
            'vip_level' => ['required', 'in:standard,silver,gold,platinum,diamond'],
            'contact_officer' => ['nullable', 'string', 'max:100'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'remarks' => ['nullable', 'string'],
        ]);

        $patient = PatientProfile::findOrFail($validated['patient_profile_id']);
        $gopNumber = PrivateInsuranceGuarantee::generateGopNumber();

        $guarantee = PrivateInsuranceGuarantee::create(array_merge($validated, [
            'gop_number' => $gopNumber,
            'guarantee_status' => 'approved',
            'patient_copay_amount' => $validated['patient_copay_amount'] ?? 0,
            'handled_by_user_id' => Auth::id(),
        ]));

        ClinicalAuditLog::record(
            $patient,
            'private_insurance_gop',
            "Phát hành Thư bảo lãnh GOP {$guarantee->gop_number} từ {$guarantee->tpa_company} hạn mức " . number_format($guarantee->approved_amount) . " VNĐ (Hạng {$guarantee->vip_level})"
        );

        return redirect()->route('reception.private-insurance.show', $guarantee)
            ->with('success', "Đã phát hành Thư bảo lãnh viện phí GOP: {$guarantee->gop_number} thành công!");
    }

    public function show(PrivateInsuranceGuarantee $privateInsurance): View
    {
        $privateInsurance->load(['patientProfile', 'handledBy']);
        return view('receptionist.private_insurance.show', compact('privateInsurance'));
    }

    public function print(PrivateInsuranceGuarantee $privateInsurance): View
    {
        $privateInsurance->load(['patientProfile', 'handledBy']);
        return view('receptionist.private_insurance.print', compact('privateInsurance'));
    }
}
