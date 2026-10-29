<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\CorporateContract;
use App\Models\CorporateEmployee;
use App\Models\PatientProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CorporateScreeningController extends Controller
{
    public function index(): View
    {
        $contracts = CorporateContract::withCount(['employees', 'employees as completed_count' => function ($q) {
            $q->where('screening_status', 'completed');
        }])->latest('id')->paginate(15);

        $stats = [
            'total_contracts' => CorporateContract::count(),
            'active_contracts' => CorporateContract::where('status', 'active')->count(),
            'total_employees' => CorporateEmployee::count(),
            'completed_screenings' => CorporateEmployee::where('screening_status', 'completed')->count(),
        ];

        return view('receptionist.corporate_screening.index', compact('contracts', 'stats'));
    }

    public function storeContract(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:200'],
            'company_tax_code' => ['nullable', 'string', 'max:30'],
            'contact_person' => ['required', 'string', 'max:100'],
            'contact_phone' => ['required', 'string', 'max:20'],
            'checkup_start_date' => ['required', 'date'],
            'checkup_end_date' => ['required', 'date', 'after_or_equal:checkup_start_date'],
            'total_employees' => ['required', 'integer', 'min:1'],
            'package_name' => ['required', 'string', 'max:100'],
            'cost_per_person' => ['required', 'numeric', 'min:0'],
        ]);

        $contractCode = CorporateContract::generateContractCode();

        $contract = CorporateContract::create(array_merge($validated, [
            'contract_code' => $contractCode,
            'status' => 'active',
        ]));

        return redirect()->route('reception.corporate-screening.show', $contract)
            ->with('success', "Đã khởi tạo Hợp đồng khám sức khỏe đoàn doanh nghiệp: {$contract->contract_code}");
    }

    public function show(CorporateContract $contract): View
    {
        $contract->load(['employees' => function ($q) {
            $q->latest('id');
        }]);

        $employees = $contract->employees()->paginate(20);

        return view('receptionist.corporate_screening.show', compact('contract', 'employees'));
    }

    public function addEmployee(Request $request, CorporateContract $contract): RedirectResponse
    {
        $validated = $request->validate([
            'employee_code' => ['required', 'string', 'max:50'],
            'full_name' => ['required', 'string', 'max:100'],
            'dob' => ['required', 'date'],
            'gender' => ['required', 'in:male,female'],
            'phone' => ['nullable', 'string', 'max:20'],
            'department' => ['nullable', 'string', 'max:100'],
        ]);

        // Auto-link hoặc tạo hồ sơ bệnh nhân nếu chưa có
        $patient = null;
        if (!empty($validated['phone'])) {
            $patient = PatientProfile::where('phone', $validated['phone'])->first();
        }
        if (!$patient) {
            $patient = PatientProfile::create([
                'patient_code' => PatientProfile::generatePatientCode(),
                'full_name' => $validated['full_name'],
                'dob' => $validated['dob'],
                'gender' => $validated['gender'],
                'phone' => $validated['phone'] ?? ('098' . rand(1000000, 9999999)),
                'address' => 'Công ty ' . $contract->company_name,
            ]);
        }

        CorporateEmployee::create(array_merge($validated, [
            'corporate_contract_id' => $contract->id,
            'patient_profile_id' => $patient->id,
            'screening_status' => 'waiting',
        ]));

        return back()->with('success', "Đã tiếp nhận nhân viên {$validated['full_name']} vào danh sách đoàn khám!");
    }

    public function updateScreening(Request $request, CorporateEmployee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'screening_status' => ['required', 'in:waiting,in_progress,completed'],
            'health_classification' => ['required', 'in:Loại I,Loại II,Loại III,Loại IV,Loại V'],
            'conclusion' => ['nullable', 'string'],
        ]);

        $employee->update($validated);

        return back()->with('success', "Đã cập nhật kết luận phân loại sức khỏe Thông tư 32/2023/TT-BYT cho nhân viên {$employee->full_name}!");
    }
}
