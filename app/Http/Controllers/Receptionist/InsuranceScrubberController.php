<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\InsuranceClaimScrub;
use App\Models\PatientProfile;
use Illuminate\Http\Request;

class InsuranceScrubberController extends Controller
{
    public function index(Request $request)
    {
        $query = InsuranceClaimScrub::with('patientProfile')->latest();

        if ($request->filled('scrub_status')) {
            $query->where('scrub_status', $request->scrub_status);
        }

        $claims = $query->paginate(15);
        $patients = PatientProfile::orderBy('full_name')->limit(50)->get();

        $stats = [
            'total_claims' => InsuranceClaimScrub::count(),
            'clean_pass' => InsuranceClaimScrub::where('scrub_status', 'clean')->count(),
            'rejected_risk' => InsuranceClaimScrub::where('scrub_status', 'rejected_high_risk')->count(),
            'xml_exported' => InsuranceClaimScrub::where('xml_4210_exported', true)->count(),
        ];

        return view('receptionist.claim_scrubber.index', compact('claims', 'patients', 'stats'));
    }

    public function scrub(Request $request)
    {
        $validated = $request->validate([
            'patient_profile_id' => 'required|exists:patient_profiles,id',
            'icd10_code' => 'required|string|max:20',
            'icd10_name' => 'required|string|max:150',
            'claim_amount' => 'required|numeric|min:1000',
            'prescribed_drugs' => 'nullable|array',
            'designated_tests' => 'nullable|array',
        ]);

        $violations = [];
        $status = 'clean';

        // 1. Kiểm tra chỉ định Chụp MRI không phù hợp chẩn đoán
        $tests = $validated['designated_tests'] ?? [];
        if (in_array('mri_brain', $tests) && !in_array($validated['icd10_code'], ['G40', 'I63', 'G43', 'G44'])) {
            $violations[] = 'Lỗi BHYT: Chỉ định chụp MRI Sọ não không khớp với mã chẩn đoán ICD-10 (Nguy cơ bị từ chối 100%).';
            $status = 'rejected_high_risk';
        }

        // 2. Kháng sinh quý thế hệ 3/4 không biên bản hội chẩn
        $drugs = $validated['prescribed_drugs'] ?? [];
        if (in_array('meropenem', $drugs) || in_array('vancomycin', $drugs)) {
            $violations[] = 'Cảnh báo BHYT: Kháng sinh nhóm Carbapenem/Glycopeptide bắt buộc đính kèm Biên bản Hội chẩn duyệt sử dụng.';
            if ($status !== 'rejected_high_risk') {
                $status = 'warning_minor';
            }
        }

        InsuranceClaimScrub::create([
            'patient_profile_id' => $validated['patient_profile_id'],
            'icd10_code' => strtoupper($validated['icd10_code']),
            'icd10_name' => $validated['icd10_name'],
            'claim_amount' => $validated['claim_amount'],
            'scrub_status' => $status,
            'scrub_violations' => $violations,
            'xml_4210_exported' => ($status === 'clean'),
            'verified_by' => 'Giám Định Viên BHYT Bệnh Viện',
        ]);

        return redirect()->route('reception.claim-scrubber.index')->with(
            $status === 'clean' ? 'success' : 'warning',
            $status === 'clean' ? "Hồ sơ BHYT đã vượt qua kiểm tra tự động (Clean Claim) và sẵn sàng xuất XML 4210!" : "CẢNH BÁO XUẤT TOÁN: Phát hiện " . count($violations) . " lỗi vi phạm quy định BHYT!"
        );
    }
}
