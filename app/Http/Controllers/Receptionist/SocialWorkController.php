<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\ClinicalAuditLog;
use App\Models\MedicalInterpreter;
use App\Models\PatientProfile;
use App\Models\SocialWorkCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SocialWorkController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'social');

        $socialCases = SocialWorkCase::with(['patientProfile', 'officer', 'approvedBy'])
            ->latest('id')
            ->paginate(15, ['*'], 'social_page');

        $interpreters = MedicalInterpreter::with(['patientProfile', 'assignedBy'])
            ->latest('id')
            ->paginate(15, ['*'], 'inter_page');

        $patients = PatientProfile::latest('id')->limit(50)->get();

        $stats = [
            'total_social_cases' => SocialWorkCase::count(),
            'total_aid_granted' => SocialWorkCase::where('status', 'approved')->sum('approved_discount_amount'),
            'active_interpreters' => MedicalInterpreter::where('status', 'in_progress')->count(),
            'total_interpreters' => MedicalInterpreter::count(),
        ];

        return view('receptionist.social_work.index', compact('socialCases', 'interpreters', 'patients', 'stats', 'tab'));
    }

    public function storeSocialCase(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_profile_id' => ['required', 'exists:patient_profiles,id'],
            'difficulty_type' => ['required', 'string', 'max:50'],
            'proposed_discount_amount' => ['required', 'numeric', 'min:100000', 'max:500000000'],
            'funding_source' => ['required', 'string', 'max:100'],
            'situation_assessment' => ['required', 'string'],
        ]);

        $patient = PatientProfile::findOrFail($validated['patient_profile_id']);
        $caseCode = SocialWorkCase::generateCaseCode();

        $case = SocialWorkCase::create(array_merge($validated, [
            'case_code' => $caseCode,
            'status' => 'pending',
            'officer_user_id' => Auth::id(),
        ]));

        ClinicalAuditLog::record(
            $patient,
            'social_work_case_created',
            "Tạo hồ sơ trợ cấp viện phí {$case->case_code}: Đề xuất hỗ trợ " . number_format($case->proposed_discount_amount) . " VNĐ từ {$case->funding_source}"
        );

        return back()->with('success', "Đã nộp hồ sơ xin trợ cấp viện phí từ Quỹ Từ Thiện: {$case->case_code}");
    }

    public function approveSocialCase(Request $request, SocialWorkCase $case): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected,disbursed'],
            'approved_discount_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $approvedAmount = $validated['approved_discount_amount'] ?? $case->proposed_discount_amount;

        $case->update([
            'status' => $validated['status'],
            'approved_discount_amount' => $validated['status'] === 'approved' ? $approvedAmount : 0,
            'approved_by_user_id' => Auth::id(),
        ]);

        ClinicalAuditLog::record(
            $case->patientProfile,
            'social_work_case_approved',
            "Phê duyệt trợ cấp viện phí: " . number_format($approvedAmount) . " VNĐ cho bệnh nhân {$case->patientProfile->full_name}"
        );

        return back()->with('success', "Cập nhật xét duyệt trợ cấp viện phí thành công!");
    }

    public function storeInterpreter(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_profile_id' => ['required', 'exists:patient_profiles,id'],
            'language' => ['required', 'string', 'max:50'],
            'interpreter_name' => ['required', 'string', 'max:100'],
            'interpreter_phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
        ]);

        $requestCode = MedicalInterpreter::generateRequestCode();

        $interpreter = MedicalInterpreter::create(array_merge($validated, [
            'request_code' => $requestCode,
            'status' => 'in_progress',
            'assigned_by_user_id' => Auth::id(),
        ]));

        return back()->with('success', "Đã điều phối Thông dịch viên y khoa {$interpreter->language} ({$interpreter->interpreter_name}) hỗ trợ người bệnh!");
    }

    public function completeInterpreter(MedicalInterpreter $interpreter): RedirectResponse
    {
        $interpreter->update(['status' => 'completed']);
        return back()->with('success', "Ca hỗ trợ thông dịch y khoa đã hoàn tất thành công!");
    }
}
