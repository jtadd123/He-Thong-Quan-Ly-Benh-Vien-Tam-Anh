<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DrgBillingRecord;
use App\Models\ResearchProject;

class FinanceHrController extends Controller
{
    public function index()
    {
        $stats = [
            'drg_pending' => DrgBillingRecord::where('billing_status', 'pending')->count(),
            'drg_approved_today' => DrgBillingRecord::whereDate('created_at', today())->where('billing_status', 'approved')->count(),
            'research_active' => ResearchProject::whereIn('status', ['recruiting', 'data_collection'])->count(),
            'research_total' => ResearchProject::count(),
        ];
        return view('reception.finance_hr.index', compact('stats'));
    }

    public function storeDrgBilling(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'doctor_id' => 'required|exists:doctors,id',
            'drg_code' => 'required|string|max:20',
            'drg_description' => 'required|string|max:300',
            'principal_diagnosis_icd10' => 'required|string|max:10',
            'length_of_stay_days' => 'required|integer|min:1',
            'drg_weight' => 'required|numeric|min:0',
            'base_rate' => 'required|numeric|min:0',
            'insurance_type' => 'required|in:bhyt,private,self_pay,workers_comp',
            'insurance_coverage_pct' => 'nullable|numeric|between:0,100',
            'actual_cost' => 'nullable|numeric|min:0',
            'coding_notes' => 'nullable|string|max:2000',
        ]);

        $expected = DrgBillingRecord::calculateExpectedPayment(
            (float)$validated['drg_weight'], (float)$validated['base_rate']
        );
        $actualCost = (float)($validated['actual_cost'] ?? $expected);
        $variance = DrgBillingRecord::calculateCostVariance($actualCost, $expected);
        $copay = DrgBillingRecord::calculatePatientCopay($expected, (float)($validated['insurance_coverage_pct'] ?? 80));
        $outlier = DrgBillingRecord::isOutlierCase($validated['length_of_stay_days'], 5, (float)$validated['drg_weight']);

        $record = DrgBillingRecord::create(array_merge($validated, [
            'expected_payment' => $expected,
            'actual_cost' => $actualCost,
            'cost_variance' => $variance,
            'patient_copay' => $copay,
            'outlier_case' => $outlier,
            'billing_status' => 'draft',
            'mcc_cc' => 'no_mcc_cc',
        ]));

        return response()->json(['success' => true, 'message' => 'Lập phiếu DRG thành công', 'data' => $record], 201);
    }

    public function storeResearchProject(Request $request)
    {
        $validated = $request->validate([
            'project_code' => 'required|string|max:50|unique:research_projects,project_code',
            'title' => 'required|string|max:500',
            'principal_investigator' => 'required|string|max:200',
            'department' => 'required|string|max:200',
            'research_type' => 'required|in:clinical_trial,observational,retrospective,case_study,rct,meta_analysis,quality_improvement',
            'start_date' => 'required|date',
            'target_enrollment' => 'nullable|integer|min:0',
            'budget_approved' => 'nullable|numeric|min:0',
            'funding_source' => 'nullable|string|max:200',
            'description' => 'nullable|string|max:3000',
        ]);

        $record = ResearchProject::create(array_merge($validated, [
            'status' => 'planning',
            'phase' => 'na',
            'actual_enrollment' => 0,
            'budget_spent' => 0,
        ]));

        return response()->json(['success' => true, 'message' => 'Tạo đề tài nghiên cứu thành công', 'data' => $record], 201);
    }
}
