<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StrokePathwayLog;
use App\Models\StemiCathlabActivation;
use App\Models\AnesthesiaRecord;
use App\Models\VentilatorRecord;
use App\Models\WoundCareAssessment;
use App\Models\PainAssessment;
use App\Models\NicuMonitoringRecord;
use App\Models\MentalHealthAssessment;
use App\Models\LiverCirrhosisScore;
use App\Models\DiabeticFootAssessment;

class AdvancedSpecialtyController extends Controller
{
    public function index()
    {
        $stats = [
            'stroke_cases_today' => StrokePathwayLog::whereDate('created_at', today())->count(),
            'stemi_activations_today' => StemiCathlabActivation::whereDate('created_at', today())->count(),
            'ventilated_patients' => VentilatorRecord::whereDate('created_at', today())->count(),
            'pain_uncontrolled' => PainAssessment::whereDate('created_at', today())->where('pain_controlled', false)->count(),
            'wounds_deteriorating' => WoundCareAssessment::where('healing_trend', 'deteriorating')->count(),
        ];
        return view('reception.advanced_specialty.index', compact('stats'));
    }

    public function storeStroke(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'doctor_id' => 'required|exists:doctors,id',
            'stroke_type' => 'required|in:ischemic,hemorrhagic,tia,undetermined',
            'nihss_score' => 'nullable|integer|between:0,42',
            'aspects_score' => 'nullable|integer|between:0,10',
            'tpa_eligible' => 'nullable|boolean',
            'tpa_given' => 'nullable|boolean',
            'tpa_dose_mg' => 'nullable|numeric|min:0',
            'tpa_contraindication' => 'nullable|string|max:500',
            'thrombectomy_done' => 'nullable|boolean',
            'door_time' => 'nullable|date',
            'tpa_start_time' => 'nullable|date',
            'outcome' => 'nullable|in:improving,stable,deteriorating,discharged,deceased',
            'clinical_notes' => 'nullable|string|max:3000',
        ]);

        $dtn = StrokePathwayLog::calculateDoorToNeedle(
            $validated['door_time'] ?? null,
            $validated['tpa_start_time'] ?? null
        );
        $targetMet = $dtn !== null && $dtn <= 60;

        $log = StrokePathwayLog::create(array_merge($validated, [
            'door_to_needle_minutes' => $dtn,
            'target_dtn_met' => $targetMet,
            'tpa_eligible' => (bool)($validated['tpa_eligible'] ?? false),
            'tpa_given' => (bool)($validated['tpa_given'] ?? false),
            'thrombectomy_done' => (bool)($validated['thrombectomy_done'] ?? false),
        ]));

        return response()->json(['success' => true, 'message' => 'Ghi nhận Stroke Pathway thành công', 'data' => array_merge($log->toArray(), ['door_to_needle_minutes' => $dtn, 'target_met' => $targetMet])], 201);
    }

    public function storeStemi(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'doctor_id' => 'required|exists:doctors,id',
            'reperfusion_strategy' => 'required|in:primary_pci,thrombolysis,pharmaco_invasive,cabg,conservative',
            'infarct_territory' => 'nullable|in:anterior,inferior,lateral,posterior,rvmi',
            'killip_class' => 'nullable|in:I,II,III,IV',
            'door_time' => 'nullable|date',
            'balloon_time' => 'nullable|date',
            'outcome' => 'nullable|in:survived,cardiogenic_shock,vf_arrested,deceased',
            'clinical_notes' => 'nullable|string|max:3000',
        ]);

        $dtb = StemiCathlabActivation::calculateDoorToBalloon(
            $validated['door_time'] ?? null,
            $validated['balloon_time'] ?? null
        );
        $targetMet = StemiCathlabActivation::isTargetMet($dtb, $validated['reperfusion_strategy']);

        $record = StemiCathlabActivation::create(array_merge($validated, [
            'door_to_balloon_minutes' => $dtb,
            'target_dtb_met' => $targetMet,
            'cathlab_activation_time' => now(),
        ]));

        return response()->json(['success' => true, 'message' => 'Kích hoạt Cath Lab STEMI thành công', 'data' => $record], 201);
    }

    public function storeAnesthesia(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'doctor_id' => 'required|exists:doctors,id',
            'surgery_name' => 'required|string|max:200',
            'anesthesia_type' => 'required|in:general,regional,spinal,epidural,local,sedation,combined',
            'asa_class' => 'required|in:I,II,III,IV,V',
            'mallampati_score' => 'nullable|integer|between:1,4',
            'total_fentanyl_mcg' => 'nullable|numeric|min:0',
            'total_propofol_mg' => 'nullable|numeric|min:0',
            'total_blood_loss_ml' => 'nullable|numeric|min:0',
            'total_fluid_given_ml' => 'nullable|numeric|min:0',
            'aldrete_score' => 'nullable|integer|between:0,10',
            'pacu_discharge_criteria' => 'nullable|in:met,not_met,icu_required',
            'anesthesia_notes' => 'nullable|string|max:3000',
        ]);

        $record = AnesthesiaRecord::create(array_merge($validated, [
            'anesthesia_start_time' => now(),
        ]));

        return response()->json(['success' => true, 'message' => 'Ghi nhận gây mê thành công', 'data' => $record], 201);
    }

    public function storeVentilator(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'doctor_id' => 'required|exists:doctors,id',
            'ventilation_mode' => 'required|in:ac_vc,ac_pc,simv,psv,cpap,bipap,hfov,hfnc',
            'tidal_volume_ml' => 'nullable|numeric|min:0',
            'peep' => 'nullable|numeric|min:0|max:30',
            'fio2' => 'nullable|numeric|between:0.21,1.0',
            'plateau_pressure' => 'nullable|numeric|min:0',
            'peak_pressure' => 'nullable|numeric|min:0',
            'pao2' => 'nullable|numeric|min:0',
            'pco2' => 'nullable|numeric|min:0',
            'ph_value' => 'nullable|numeric|between:6.8,7.8',
            'spo2' => 'nullable|numeric|between:0,100',
            'weaning_status' => 'nullable|in:not_started,in_progress,successful,failed',
            'nursing_notes' => 'nullable|string|max:2000',
        ]);

        $pf = null;
        $berlin = null;
        $drivingPressure = null;
        if (!empty($validated['pao2']) && !empty($validated['fio2'])) {
            $pf = VentilatorRecord::calculatePf((float)$validated['pao2'], (float)$validated['fio2']);
            $berlin = VentilatorRecord::classifyBerlin($pf);
        }
        if (!empty($validated['plateau_pressure']) && !empty($validated['peep'])) {
            $drivingPressure = VentilatorRecord::calculateDrivingPressure((float)$validated['plateau_pressure'], (float)$validated['peep']);
        }

        $record = VentilatorRecord::create(array_merge($validated, [
            'driving_pressure' => $drivingPressure,
            'berlin_category' => $berlin,
        ]));

        return response()->json(['success' => true, 'message' => 'Cài đặt máy thở thành công', 'data' => array_merge($record->toArray(), ['pf_ratio' => $pf, 'berlin' => $berlin])], 201);
    }

    public function storeWoundCare(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'doctor_id' => 'required|exists:doctors,id',
            'wound_type' => 'required|in:pressure_injury,diabetic_foot,venous_ulcer,arterial_ulcer,surgical,traumatic,burn,other',
            'wound_location' => 'required|string|max:200',
            'pressure_injury_stage' => 'nullable|in:stage_1,stage_2,stage_3,stage_4,unstageable,deep_tissue,na',
            'wound_length_cm' => 'nullable|numeric|min:0',
            'wound_width_cm' => 'nullable|numeric|min:0',
            'wound_depth_cm' => 'nullable|numeric|min:0',
            'braden_score' => 'nullable|integer|between:6,23',
            'wound_bed' => 'nullable|in:red,yellow,black,mixed',
            'exudate_amount' => 'nullable|in:none,scant,small,moderate,large',
            'exudate_type' => 'nullable|in:serous,serosanguineous,sanguineous,purulent,none',
            'wound_infection' => 'nullable|boolean',
            'dressing_type' => 'nullable|string|max:200',
            'healing_trend' => 'nullable|in:improving,stable,deteriorating,healed',
            'assessment_notes' => 'nullable|string|max:3000',
        ]);

        $area = null;
        if (!empty($validated['wound_length_cm']) && !empty($validated['wound_width_cm'])) {
            $area = WoundCareAssessment::calculateWoundArea((float)$validated['wound_length_cm'], (float)$validated['wound_width_cm']);
        }

        $record = WoundCareAssessment::create(array_merge($validated, [
            'wound_area_cm2' => $area,
            'pressure_injury_stage' => $validated['pressure_injury_stage'] ?? 'na',
            'wound_infection' => (bool)($validated['wound_infection'] ?? false),
            'next_assessment_date' => now()->addDays(3)->toDateString(),
        ]));

        return response()->json(['success' => true, 'message' => 'Đánh giá vết thương thành công', 'data' => $record], 201);
    }

    public function storePainAssessment(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'doctor_id' => 'required|exists:doctors,id',
            'scale_type' => 'required|in:vas,nrs,flacc,cpot,faces_wong',
            'pain_score' => 'required|numeric|between:0,10',
            'pain_location' => 'nullable|string|max:200',
            'pain_quality' => 'nullable|in:sharp,dull,burning,throbbing,stabbing,cramping,radiating',
            'pain_at_rest' => 'nullable|boolean',
            'pain_on_movement' => 'nullable|boolean',
            'analgesic_ladder' => 'nullable|in:step1_nonopioid,step2_weak_opioid,step3_strong_opioid,adjuvant',
            'nursing_notes' => 'nullable|string|max:2000',
        ]);

        $severity = PainAssessment::classifyPainSeverity((float)$validated['pain_score']);
        $ladder = PainAssessment::recommendAnalgesicLadder((float)$validated['pain_score']);

        $record = PainAssessment::create(array_merge($validated, [
            'pain_severity' => $severity,
            'analgesic_ladder' => $validated['analgesic_ladder'] ?? $ladder,
            'pain_at_rest' => (bool)($validated['pain_at_rest'] ?? false),
            'pain_on_movement' => (bool)($validated['pain_on_movement'] ?? false),
            'pain_controlled' => (float)$validated['pain_score'] <= 3.0,
        ]));

        return response()->json(['success' => true, 'message' => 'Đánh giá đau thành công', 'data' => $record], 201);
    }

    public function storeNicu(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'doctor_id' => 'required|exists:doctors,id',
            'gestational_age_weeks' => 'nullable|integer|between:22,44',
            'birth_weight_grams' => 'nullable|numeric|min:200',
            'apgar_1min' => 'nullable|integer|between:0,10',
            'apgar_5min' => 'nullable|integer|between:0,10',
            'bilirubin_total' => 'nullable|numeric|min:0',
            'blood_glucose' => 'nullable|numeric|min:0',
            'temperature' => 'nullable|numeric|between:34,42',
            'spo2_percent' => 'nullable|numeric|between:0,100',
            'respiratory_support' => 'nullable|in:room_air,nasal_cannula,cpap,hfnc,intubated',
            'phototherapy_active' => 'nullable|boolean',
            'breast_feeding' => 'nullable|boolean',
            'nursing_notes' => 'nullable|string|max:2000',
        ]);

        $record = NicuMonitoringRecord::create(array_merge($validated, [
            'phototherapy_active' => (bool)($validated['phototherapy_active'] ?? false),
            'breast_feeding' => (bool)($validated['breast_feeding'] ?? false),
            'oxygen_support' => !empty($validated['respiratory_support']) && $validated['respiratory_support'] !== 'room_air',
        ]));

        return response()->json(['success' => true, 'message' => 'Ghi nhận theo dõi NICU thành công', 'data' => $record], 201);
    }

    public function storeMentalHealth(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'doctor_id' => 'required|exists:doctors,id',
            'assessment_type' => 'required|in:phq9,gad7,columbia_scale,mmse,moca,audit,cage,madrs,panss,ham_a,ham_d',
            'responses' => 'required|array',
            'suicide_risk' => 'nullable|in:none,low,moderate,high,imminent',
            'safety_plan_made' => 'nullable|boolean',
            'medication_started' => 'nullable|boolean',
            'psychotherapy_recommended' => 'nullable|boolean',
            'clinical_impression' => 'nullable|string|max:3000',
        ]);

        $result = match($validated['assessment_type']) {
            'phq9' => MentalHealthAssessment::scorePHQ9($validated['responses']),
            'gad7' => MentalHealthAssessment::scoreGAD7($validated['responses']),
            default => ['score' => array_sum($validated['responses']), 'severity' => 'unknown', 'recommendation' => 'Đánh giá theo phác đồ chuyên khoa']
        };

        $record = MentalHealthAssessment::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $validated['doctor_id'],
            'assessment_type' => $validated['assessment_type'],
            'responses' => $validated['responses'],
            'total_score' => $result['score'],
            'severity_level' => $result['severity'] ?? 'unknown',
            'interpretation' => $result['recommendation'] ?? '',
            'suicide_risk' => $validated['suicide_risk'] ?? 'none',
            'safety_plan_made' => (bool)($validated['safety_plan_made'] ?? false),
            'medication_started' => (bool)($validated['medication_started'] ?? false),
            'psychotherapy_recommended' => (bool)($validated['psychotherapy_recommended'] ?? false),
            'clinical_impression' => $validated['clinical_impression'] ?? null,
            'follow_up_date' => now()->addDays(14)->toDateString(),
        ]);

        return response()->json(['success' => true, 'message' => 'Đánh giá tâm thần thành công', 'data' => $record], 201);
    }

    public function storeLiverScore(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'doctor_id' => 'required|exists:doctors,id',
            'bilirubin_cp' => 'required|numeric|min:0',
            'albumin_cp' => 'required|numeric|min:0',
            'inr_cp' => 'required|numeric|min:0',
            'ascites_grade' => 'required|in:none,mild,moderate,severe',
            'encephalopathy_grade' => 'required|in:none,grade1,grade2,grade3,grade4',
            'creatinine_meld' => 'nullable|numeric|min:0',
            'bilirubin_meld' => 'nullable|numeric|min:0',
            'inr_meld' => 'nullable|numeric|min:0',
            'sodium_meld' => 'nullable|numeric|between:100,145',
            'dialysis_twice_weekly' => 'nullable|boolean',
            'clinical_notes' => 'nullable|string|max:2000',
        ]);

        $cp = LiverCirrhosisScore::calculateChildPugh(
            (float)$validated['bilirubin_cp'], (float)$validated['albumin_cp'],
            (float)$validated['inr_cp'], $validated['ascites_grade'], $validated['encephalopathy_grade']
        );

        $meld = 0.0;
        $meldNa = 0.0;
        $mortality = 0;
        if (!empty($validated['creatinine_meld'])) {
            $meld = LiverCirrhosisScore::calculateMeld(
                (float)$validated['creatinine_meld'], (float)($validated['bilirubin_meld'] ?? 1),
                (float)($validated['inr_meld'] ?? 1), (bool)($validated['dialysis_twice_weekly'] ?? false)
            );
            if (!empty($validated['sodium_meld'])) {
                $meldNa = LiverCirrhosisScore::calculateMeldNa($meld, (float)$validated['sodium_meld']);
            }
            $mortality = LiverCirrhosisScore::getMeldMortality($meld);
        }

        $record = LiverCirrhosisScore::create(array_merge($validated, [
            'child_pugh_score' => $cp['score'],
            'child_pugh_class' => $cp['class'],
            'one_year_survival' => $cp['one_year_survival'],
            'meld_score' => $meld,
            'meld_na_score' => $meldNa,
            'three_month_mortality_pct' => $mortality,
            'dialysis_twice_weekly' => (bool)($validated['dialysis_twice_weekly'] ?? false),
        ]));

        return response()->json(['success' => true, 'message' => 'Tính điểm xơ gan thành công', 'data' => $record], 201);
    }

    public function storeDiabeticFoot(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'doctor_id' => 'required|exists:doctors,id',
            'foot_side' => 'required|in:left,right,bilateral',
            'wagner_grade' => 'required|integer|between:0,5',
            'abi_left' => 'nullable|numeric|between:0,2',
            'abi_right' => 'nullable|numeric|between:0,2',
            'neuropathy_present' => 'nullable|boolean',
            'infection_present' => 'nullable|boolean',
            'infection_severity' => 'nullable|in:none,mild,moderate,severe,limb_threatening',
            'osteomyelitis_suspected' => 'nullable|boolean',
            'treatment_plan' => 'nullable|in:conservative,debridement,minor_amputation,major_amputation,revascularization',
            'hba1c' => 'nullable|numeric|between:4,20',
            'assessment_notes' => 'nullable|string|max:3000',
        ]);

        $wagnerInfo = DiabeticFootAssessment::classifyWagner($validated['wagner_grade']);
        $abiInterp = DiabeticFootAssessment::interpretAbi((float)($validated['abi_right'] ?? 1.0));
        $ampRisk = DiabeticFootAssessment::calculateAmputationRisk(
            $validated['wagner_grade'],
            (float)($validated['abi_right'] ?? 1.0),
            (bool)($validated['osteomyelitis_suspected'] ?? false)
        );

        $record = DiabeticFootAssessment::create(array_merge($validated, [
            'wagner_description' => $wagnerInfo['description'],
            'abi_interpretation' => $abiInterp,
            'neuropathy_present' => (bool)($validated['neuropathy_present'] ?? false),
            'infection_present' => (bool)($validated['infection_present'] ?? false),
            'osteomyelitis_suspected' => (bool)($validated['osteomyelitis_suspected'] ?? false),
            'protective_sensation_lost' => (bool)($validated['neuropathy_present'] ?? false),
        ]));

        return response()->json(['success' => true, 'message' => 'Đánh giá bàn chân đái tháo đường thành công', 'data' => array_merge($record->toArray(), ['amputation_risk' => $ampRisk])], 201);
    }
}
