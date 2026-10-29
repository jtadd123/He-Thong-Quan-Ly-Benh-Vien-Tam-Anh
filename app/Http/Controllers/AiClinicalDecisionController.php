<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SepsisPredictionLog;
use App\Models\ClinicalDecisionRule;
use App\Models\Icd10CodingRecord;
use App\Models\MedicationReconciliation;
use App\Models\FluidBalanceRecord;
use App\Models\AnticoagulationManagement;

class AiClinicalDecisionController extends Controller
{
    public function index()
    {
        $stats = [
            'sepsis_alerts_today' => SepsisPredictionLog::whereDate('created_at', today())
                ->where('sepsis_risk', '!=', 'low')->count(),
            'icd10_pending' => Icd10CodingRecord::where('review_status', 'pending')->count(),
            'med_reconciliations_today' => MedicationReconciliation::whereDate('created_at', today())->count(),
            'fluid_imbalance_patients' => FluidBalanceRecord::whereDate('record_date', today())
                ->whereIn('balance_status', ['severely_positive', 'severely_negative'])->count(),
            'anticoagulation_active' => AnticoagulationManagement::where('status', 'active')->count(),
        ];
        $recentSepsis = SepsisPredictionLog::with(['patient', 'doctor'])
            ->whereIn('sepsis_risk', ['high', 'critical'])
            ->latest()->limit(10)->get();
        return view('reception.ai_clinical_decision.index', compact('stats', 'recentSepsis'));
    }

    public function storeSepsis(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'doctor_id' => 'required|exists:doctors,id',
            'temperature' => 'nullable|numeric|between:30,45',
            'heart_rate' => 'nullable|integer|between:20,300',
            'respiratory_rate' => 'nullable|integer|between:5,80',
            'sbp' => 'nullable|numeric|between:40,250',
            'gcs' => 'nullable|numeric|between:3,15',
            'lactate' => 'nullable|numeric|between:0,30',
            'creatinine' => 'nullable|numeric|between:0,20',
            'bilirubin' => 'nullable|numeric|between:0,50',
            'platelet_count' => 'nullable|integer|between:0,1000',
            'pao2_fio2' => 'nullable|numeric|between:0,600',
            'map' => 'nullable|numeric|between:20,200',
            'urine_output' => 'nullable|numeric|between:0,10',
            'clinical_notes' => 'nullable|string|max:2000',
        ]);

        $qsofa = SepsisPredictionLog::calculateQsofa(
            (float)($validated['respiratory_rate'] ?? 0),
            (float)($validated['sbp'] ?? 120),
            (float)($validated['gcs'] ?? 15)
        );
        $sofa = SepsisPredictionLog::calculateSofa(
            (float)($validated['pao2_fio2'] ?? 400),
            (float)($validated['platelet_count'] ?? 200),
            (float)($validated['bilirubin'] ?? 0.5),
            (float)($validated['map'] ?? 80),
            (float)($validated['creatinine'] ?? 0.8),
            (float)($validated['gcs'] ?? 15)
        );
        $risk = SepsisPredictionLog::classifySepsisRisk($qsofa, $sofa);
        $prediction = SepsisPredictionLog::predictAi($qsofa, $sofa, (float)($validated['lactate'] ?? 0));
        $confidence = min(95.0, 50.0 + ($qsofa * 15.0) + ($sofa * 5.0));

        $log = SepsisPredictionLog::create(array_merge($validated, [
            'qsofa_score' => $qsofa,
            'sofa_score' => $sofa,
            'sirs_score' => 0,
            'sepsis_risk' => $risk,
            'ai_prediction' => $prediction,
            'ai_confidence_score' => $confidence,
            'alert_sent' => in_array($risk, ['high', 'critical']),
            'status' => 'monitoring',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Phân tích nguy cơ sepsis thành công',
            'data' => [
                'id' => $log->id,
                'qsofa' => $qsofa,
                'sofa' => $sofa,
                'risk' => $risk,
                'prediction' => $prediction,
                'confidence' => $confidence,
                'alert_triggered' => in_array($risk, ['high', 'critical']),
            ]
        ], 201);
    }

    public function storeClinicalRule(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'doctor_id' => 'required|exists:doctors,id',
            'rule_type' => 'required|in:wells_dvt,wells_pe,heart_score,timi_score,chads2_vasc,has_bled,curb65,sofa,apache2,glasgow_coma',
            'input_parameters' => 'required|array',
        ]);

        $result = match($validated['rule_type']) {
            'wells_pe' => ClinicalDecisionRule::calculateWellsPe($validated['input_parameters']),
            'heart_score' => ClinicalDecisionRule::calculateHeartScore($validated['input_parameters']),
            default => ['score' => 0, 'risk' => 'low', 'recommendation' => 'Vui lòng đánh giá theo phác đồ chuyên khoa']
        };

        $rule = ClinicalDecisionRule::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $validated['doctor_id'],
            'rule_type' => $validated['rule_type'],
            'input_parameters' => $validated['input_parameters'],
            'calculated_score' => $result['score'],
            'score_interpretation' => ClinicalDecisionRule::interpretScore($validated['rule_type'], $result['score']),
            'recommendation' => $result['recommendation'] ?? '',
            'next_action' => $result['recommendation'] ?? '',
            'is_emergency' => ($result['risk'] ?? 'low') === 'high',
            'calculated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Tính điểm lâm sàng thành công', 'data' => $rule], 201);
    }

    public function storeIcd10(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'doctor_id' => 'required|exists:doctors,id',
            'clinical_text' => 'required|string|max:5000',
            'coding_method' => 'required|in:ai_auto,manual,ai_assisted',
        ]);

        $suggestion = Icd10CodingRecord::suggestCode($validated['clinical_text']);

        $record = Icd10CodingRecord::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $validated['doctor_id'],
            'clinical_text' => $validated['clinical_text'],
            'primary_icd10_code' => $suggestion['code'],
            'primary_icd10_description' => $suggestion['description'],
            'ai_confidence' => $suggestion['confidence'],
            'coding_method' => $validated['coding_method'],
            'review_status' => 'pending',
        ]);

        return response()->json(['success' => true, 'message' => 'Mã hóa ICD-10 AI thành công', 'data' => $record], 201);
    }

    public function storeMedReconciliation(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'doctor_id' => 'required|exists:doctors,id',
            'reconciliation_type' => 'required|in:admission,transfer,discharge',
            'home_medications' => 'nullable|array',
            'hospital_medications' => 'nullable|array',
            'pharmacist_name' => 'nullable|string|max:100',
            'pharmacist_notes' => 'nullable|string|max:2000',
        ]);

        $home = $validated['home_medications'] ?? [];
        $hospital = $validated['hospital_medications'] ?? [];
        $discrepancies = MedicationReconciliation::findDiscrepancies($home, $hospital);
        $risk = MedicationReconciliation::assessRisk(count($discrepancies), count($home) + count($hospital));

        $rec = MedicationReconciliation::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $validated['doctor_id'],
            'reconciliation_type' => $validated['reconciliation_type'],
            'home_medications' => $home,
            'hospital_medications' => $hospital,
            'discrepancies' => $discrepancies,
            'total_home_meds' => count($home),
            'total_hospital_meds' => count($hospital),
            'discrepancies_count' => count($discrepancies),
            'discrepancies_resolved' => 0,
            'risk_level' => $risk,
            'status' => 'completed',
            'pharmacist_name' => $validated['pharmacist_name'] ?? null,
            'pharmacist_notes' => $validated['pharmacist_notes'] ?? null,
            'completed_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Đối chiếu thuốc hoàn tất', 'data' => $rec], 201);
    }

    public function storeFluidBalance(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'doctor_id' => 'required|exists:doctors,id',
            'record_date' => 'required|date',
            'oral_intake' => 'nullable|numeric|min:0',
            'iv_intake' => 'nullable|numeric|min:0',
            'feeding_tube_intake' => 'nullable|numeric|min:0',
            'urine_output_ml' => 'nullable|numeric|min:0',
            'drain_output' => 'nullable|numeric|min:0',
            'stool_output' => 'nullable|numeric|min:0',
            'body_weight' => 'nullable|numeric|min:0',
            'nursing_notes' => 'nullable|string|max:2000',
        ]);

        $oral = (float)($validated['oral_intake'] ?? 0);
        $iv = (float)($validated['iv_intake'] ?? 0);
        $tube = (float)($validated['feeding_tube_intake'] ?? 0);
        $totalIn = $oral + $iv + $tube;

        $insensible = FluidBalanceRecord::calculateInsensibleLoss((float)($validated['body_weight'] ?? 70));
        $urine = (float)($validated['urine_output_ml'] ?? 0);
        $drain = (float)($validated['drain_output'] ?? 0);
        $stool = (float)($validated['stool_output'] ?? 0);
        $totalOut = $urine + $drain + $stool + $insensible;

        $balance = FluidBalanceRecord::calculateBalance($totalIn, $totalOut);
        $status = FluidBalanceRecord::classifyBalance($balance);

        $record = FluidBalanceRecord::create(array_merge($validated, [
            'oral_intake' => $oral,
            'iv_intake' => $iv,
            'feeding_tube_intake' => $tube,
            'total_intake' => $totalIn,
            'insensible_loss' => $insensible,
            'total_output' => $totalOut,
            'fluid_balance' => $balance,
            'cumulative_balance' => $balance,
            'balance_status' => $status,
        ]));

        return response()->json(['success' => true, 'message' => 'Ghi nhận cân bằng dịch thành công', 'data' => $record], 201);
    }

    public function storeAnticoagulation(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'doctor_id' => 'required|exists:doctors,id',
            'indication' => 'required|in:af,dvt,pe,mechanical_valve,bridge_therapy,acs,vte_prevention',
            'anticoagulant' => 'required|in:warfarin,heparin_iv,enoxaparin,rivaroxaban,apixaban,dabigatran,fondaparinux',
            'current_inr' => 'nullable|numeric|between:0.5,10',
            'current_dose' => 'nullable|numeric|min:0',
            'dose_unit' => 'nullable|string|max:30',
            'has_bled_score' => 'nullable|numeric|between:0,9',
            'clinical_notes' => 'nullable|string|max:2000',
        ]);

        $hasBled = (float)($validated['has_bled_score'] ?? 0);
        $bleedingRisk = AnticoagulationManagement::assessBleedingRisk($hasBled);
        $targetInrMin = in_array($validated['indication'], ['mechanical_valve']) ? 2.5 : 2.0;
        $targetInrMax = in_array($validated['indication'], ['mechanical_valve']) ? 3.5 : 3.0;

        $adjustment = null;
        if ($validated['anticoagulant'] === 'warfarin' && !empty($validated['current_inr']) && !empty($validated['current_dose'])) {
            $adjustment = AnticoagulationManagement::suggestWarfarinAdjustment(
                (float)$validated['current_inr'], $targetInrMin, $targetInrMax, (float)$validated['current_dose']
            );
        }

        $mgmt = AnticoagulationManagement::create(array_merge($validated, [
            'target_inr_min' => $targetInrMin,
            'target_inr_max' => $targetInrMax,
            'has_bled_score' => $hasBled,
            'bleeding_risk' => $bleedingRisk,
            'dose_adjustment' => $adjustment,
            'status' => 'active',
            'next_monitoring_date' => now()->addDays(7)->toDateString(),
        ]));

        return response()->json(['success' => true, 'message' => 'Quản lý chống đông thành công', 'data' => $mgmt], 201);
    }
}
