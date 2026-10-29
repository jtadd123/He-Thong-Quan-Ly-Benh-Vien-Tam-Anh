<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PatientProfile;
use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\SepsisPredictionLog;
use App\Models\ClinicalDecisionRule;
use App\Models\Icd10CodingRecord;
use App\Models\MedicationReconciliation;
use App\Models\FluidBalanceRecord;
use App\Models\AnticoagulationManagement;
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
use App\Models\OrBlockSchedule;
use App\Models\PorterTransportRequest;
use App\Models\DietaryOrder;
use App\Models\DrgBillingRecord;
use App\Models\ResearchProject;
use App\Models\PatientDigitalTwin;
use App\Models\TelehealthConsultation;
use App\Models\DischargeChecklist;
use App\Models\PostdischargeFollowup;
use App\Models\HealthScreeningCampaign;
use App\Models\LoyaltyPointsLog;
use App\Models\ChronicDiseaseCohort;
use Tests\TestCase;

class Wave5HospitalInnovationTest extends TestCase
{
    private function createReceptionistUser(): User
    {
        return User::factory()->create([
            'email' => 'rec_' . uniqid() . '_' . rand(1000, 9999) . '@tamanh.vn',
            'role' => 'receptionist',
            'is_active' => true,
        ]);
    }

    private function createPatient(): PatientProfile
    {
        return PatientProfile::create([
            'patient_code' => 'BN-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5)) . rand(100, 999),
            'full_name' => 'Nguyễn Minh Quân',
            'phone' => '0988' . rand(100000, 999999),
            'dob' => '1988-03-15',
            'gender' => 'male',
            'identity_card' => '001088' . rand(100000, 999999),
            'address' => 'Hà Nội',
        ]);
    }

    private function createDoctor(): Doctor
    {
        $user = User::factory()->create([
            'email' => 'doc_' . uniqid() . '_' . rand(1000, 9999) . '@tamanh.vn',
            'role' => 'doctor',
            'is_active' => true,
        ]);

        $specialty = Specialty::firstOrCreate(
            ['slug' => 'hoi-suc-tich-cuc'],
            ['name' => 'Hồi Sức Tích Cực ICU', 'description' => 'Khoa Hồi Sức Tích Cực']
        );

        return Doctor::create([
            'user_id' => $user->id,
            'specialty_id' => $specialty->id,
            'qualification' => 'ThS.BS Hồi Sức Cấp Cứu',
            'consultation_fee' => 350000,
            'room_number' => 'ICU.01',
            'is_active' => true,
        ]);
    }

    // 1. Dashboard AI Clinical Decision
    public function test_receptionist_can_view_ai_clinical_decision_dashboard(): void
    {
        $user = $this->createReceptionistUser();
        $response = $this->actingAs($user)->get(route('reception.ai-clinical-decision.index'));
        $response->assertStatus(200);
        $response->assertSee('AI & Hỗ Trợ Quyết Định Lâm Sàng Thông Minh');
    }

    // 2. Sepsis Prediction Log
    public function test_sepsis_prediction_log_calculation_and_threshold_alert(): void
    {
        $qsofa = SepsisPredictionLog::calculateQsofa(24, 90, 13);
        $this->assertEquals(3.0, $qsofa);

        $sofa = SepsisPredictionLog::calculateSofa(150, 45, 3.5, 65, 2.5, 12, true);
        $this->assertGreaterThanOrEqual(10.0, $sofa);

        $risk = SepsisPredictionLog::classifySepsisRisk($qsofa, $sofa);
        $this->assertEquals('critical', $risk);

        $pred = SepsisPredictionLog::predictAi($qsofa, $sofa, 3.2);
        $this->assertEquals('septic_shock', $pred);

        $user = $this->createReceptionistUser();
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $response = $this->actingAs($user)->postJson(route('reception.ai-clinical-decision.sepsis'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'respiratory_rate' => 24,
            'sbp' => 90,
            'gcs' => 13,
            'lactate' => 3.2,
            'pao2_fio2' => 150,
            'platelet_count' => 45,
            'bilirubin' => 3.5,
            'map' => 65,
            'creatinine' => 2.5,
            'clinical_notes' => 'Bệnh nhân nghi ngờ sốc nhiễm khuẩn từ viêm phổi nặng',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('sepsis_prediction_logs', [
            'patient_id' => $patient->id,
            'sepsis_risk' => 'critical',
            'ai_prediction' => 'septic_shock',
        ]);
    }

    // 3. Clinical Decision Rules (Wells PE & HEART)
    public function test_clinical_decision_rule_wells_pe_and_heart_score(): void
    {
        $wellsResult = ClinicalDecisionRule::calculateWellsPe([
            'clinical_signs_dvt' => true,
            'pe_most_likely' => true,
            'heart_rate_gt100' => true,
        ]);
        $this->assertEquals(7.5, $wellsResult['score']);
        $this->assertEquals('high', $wellsResult['risk']);

        $heartResult = ClinicalDecisionRule::calculateHeartScore([
            'history_suspicion' => 2,
            'ecg_finding' => 2,
            'age_score' => 2,
            'risk_factors' => 2,
            'troponin_score' => 2,
        ]);
        $this->assertEquals(10, $heartResult['score']);
        $this->assertEquals('high', $heartResult['risk']);

        $user = $this->createReceptionistUser();
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $response = $this->actingAs($user)->postJson(route('reception.ai-clinical-decision.clinical-rule'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'rule_type' => 'wells_pe',
            'input_parameters' => [
                'clinical_signs_dvt' => true,
                'pe_most_likely' => true,
                'heart_rate_gt100' => true,
            ],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('clinical_decision_rules', [
            'patient_id' => $patient->id,
            'rule_type' => 'wells_pe',
            'calculated_score' => 7.5,
            'is_emergency' => true,
        ]);
    }

    // 4. ICD-10 Coding
    public function test_icd10_ai_auto_coding_and_accuracy_rate(): void
    {
        $suggestion = Icd10CodingRecord::suggestCode('Bệnh nhân nhập viện vì cơn đau thắt ngực nhồi máu cơ tim cấp');
        $this->assertEquals('I21.9', $suggestion['code']);
        $this->assertEquals(94.7, Icd10CodingRecord::getAccuracyRate());

        $user = $this->createReceptionistUser();
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $response = $this->actingAs($user)->postJson(route('reception.ai-clinical-decision.icd10'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'clinical_text' => 'Bệnh nhân có tiền sử tăng huyết áp kèm đái tháo đường typ 2',
            'coding_method' => 'ai_auto',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('icd10_coding_records', [
            'patient_id' => $patient->id,
            'coding_method' => 'ai_auto',
        ]);
    }

    // 5. Medication Reconciliation
    public function test_medication_reconciliation_finds_discrepancies_and_evaluates_risk(): void
    {
        $home = [
            ['code' => 'MET500', 'name' => 'Metformin 500mg'],
            ['code' => 'AML5', 'name' => 'Amlodipine 5mg'],
        ];
        $hospital = [
            ['code' => 'MET500', 'name' => 'Metformin 500mg'],
            ['code' => 'INS100', 'name' => 'Insulin Glargine 100UI'],
        ];

        $discrepancies = MedicationReconciliation::findDiscrepancies($home, $hospital);
        $this->assertCount(2, $discrepancies);

        $risk = MedicationReconciliation::assessRisk(count($discrepancies), 4);
        $this->assertEquals('critical', $risk);

        $user = $this->createReceptionistUser();
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $response = $this->actingAs($user)->postJson(route('reception.ai-clinical-decision.med-reconciliation'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'reconciliation_type' => 'admission',
            'home_medications' => $home,
            'hospital_medications' => $hospital,
            'pharmacist_name' => 'DS. Nguyễn Văn Hưng',
            'pharmacist_notes' => 'Ngừng Amlodipine, chuyển sang ức chế men chuyển',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('medication_reconciliations', [
            'patient_id' => $patient->id,
            'total_home_meds' => 2,
            'total_hospital_meds' => 2,
            'discrepancies_count' => 2,
        ]);
    }

    // 6. Fluid Balance
    public function test_fluid_balance_record_calculates_balance_and_insensible_loss(): void
    {
        $loss = FluidBalanceRecord::calculateInsensibleLoss(70, 38.5);
        $this->assertEquals(1000.0, $loss);

        $balance = FluidBalanceRecord::calculateBalance(2500, 1800);
        $this->assertEquals(700.0, $balance);
        $this->assertEquals('positive', FluidBalanceRecord::classifyBalance($balance));

        $user = $this->createReceptionistUser();
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $response = $this->actingAs($user)->postJson(route('reception.ai-clinical-decision.fluid-balance'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'record_date' => now()->toDateString(),
            'oral_intake' => 500,
            'iv_intake' => 2000,
            'feeding_tube_intake' => 0,
            'urine_output_ml' => 1200,
            'drain_output' => 100,
            'stool_output' => 100,
            'body_weight' => 70,
            'nursing_notes' => 'Theo dõi sát cân bằng dịch phòng phù phổi',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('fluid_balance_records', [
            'patient_id' => $patient->id,
            'total_intake' => 2500,
        ]);
    }

    // 7. Anticoagulation Management
    public function test_anticoagulation_management_inr_targets_and_warfarin_adjustment(): void
    {
        $hasBled = AnticoagulationManagement::calculateHasBled([
            'hypertension' => true,
            'stroke_history' => true,
            'elderly_gt65' => true,
        ]);
        $this->assertEquals(3.0, $hasBled);
        $this->assertEquals('high', AnticoagulationManagement::assessBleedingRisk($hasBled));

        $status = AnticoagulationManagement::assessInrStatus(1.5, 2.0, 3.0);
        $this->assertEquals('sub_therapeutic', $status);

        $adjustment = AnticoagulationManagement::suggestWarfarinAdjustment(1.5, 2.0, 3.0, 5.0);
        $this->assertStringContainsString('Tăng liều 10%', $adjustment);

        $user = $this->createReceptionistUser();
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $response = $this->actingAs($user)->postJson(route('reception.ai-clinical-decision.anticoagulation'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'indication' => 'af',
            'anticoagulant' => 'warfarin',
            'current_inr' => 1.5,
            'current_dose' => 5.0,
            'dose_unit' => 'mg/ngày',
            'has_bled_score' => $hasBled,
            'clinical_notes' => 'Rung nhĩ không do van tim, chỉ định dùng Warfarin',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('anticoagulation_managements', [
            'patient_id' => $patient->id,
            'anticoagulant' => 'warfarin',
            'bleeding_risk' => 'high',
        ]);
    }

    // 8. Advanced Specialty Dashboard
    public function test_receptionist_can_view_advanced_specialty_dashboard(): void
    {
        $user = $this->createReceptionistUser();
        $response = $this->actingAs($user)->get(route('reception.advanced-specialty.index'));
        $response->assertStatus(200);
        $response->assertSee('Quản Lý Lâm Sàng Chuyên Khoa Sâu');
    }

    // 9. Stroke Pathway
    public function test_stroke_pathway_calculates_door_to_needle_and_tpa_dose(): void
    {
        $dtn = StrokePathwayLog::calculateDoorToNeedle('2026-10-29 08:00:00', '2026-10-29 08:45:00');
        $this->assertEquals(45, $dtn);

        $tpaDose = StrokePathwayLog::calculateTpaDose(70);
        $this->assertEquals(63.0, $tpaDose);

        $this->assertTrue(StrokePathwayLog::isTpaEligible(12, 8, 120, []));
        $this->assertFalse(StrokePathwayLog::isTpaEligible(12, 8, 300, [])); // >4.5h

        $user = $this->createReceptionistUser();
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $response = $this->actingAs($user)->postJson(route('reception.advanced-specialty.stroke'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'stroke_type' => 'ischemic',
            'nihss_score' => 14,
            'aspects_score' => 9,
            'tpa_eligible' => true,
            'tpa_given' => true,
            'tpa_dose_mg' => 63.0,
            'door_time' => '2026-10-29 08:00:00',
            'tpa_start_time' => '2026-10-29 08:45:00',
            'outcome' => 'improving',
            'clinical_notes' => 'Tiêu sợi huyết thành công, cải thiện yếu nửa người',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('stroke_pathway_logs', [
            'patient_id' => $patient->id,
            'target_dtn_met' => true,
            'door_to_needle_minutes' => 45,
        ]);
    }

    // 10. STEMI Cathlab Activation
    public function test_stemi_cathlab_activation_door_to_balloon_and_timi_score(): void
    {
        $dtb = StemiCathlabActivation::calculateDoorToBalloon('2026-10-29 09:00:00', '2026-10-29 10:15:00');
        $this->assertEquals(75, $dtb);
        $this->assertTrue(StemiCathlabActivation::isTargetMet($dtb, 'primary_pci'));

        $timi = StemiCathlabActivation::timiRiskScore([
            'age' => 68,
            'sbp' => 95,
            'heart_rate' => 110,
            'killip_class' => 2,
        ]);
        $this->assertGreaterThanOrEqual(4, $timi);

        $user = $this->createReceptionistUser();
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $response = $this->actingAs($user)->postJson(route('reception.advanced-specialty.stemi'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'reperfusion_strategy' => 'primary_pci',
            'infarct_territory' => 'anterior',
            'killip_class' => 'II',
            'door_time' => '2026-10-29 09:00:00',
            'balloon_time' => '2026-10-29 10:15:00',
            'outcome' => 'survived',
            'clinical_notes' => 'Can thiệp nhánh LAD thành công, đặt 1 stent DES',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('stemi_cathlab_activations', [
            'patient_id' => $patient->id,
            'target_dtb_met' => true,
            'door_to_balloon_minutes' => 75,
        ]);
    }

    // 11. Anesthesia Record & Aldrete Score
    public function test_anesthesia_record_aims_and_aldrete_pacu_discharge_criteria(): void
    {
        $score = AnesthesiaRecord::calculateAldreteScore([
            'activity' => 2,
            'respiration' => 2,
            'circulation' => 2,
            'consciousness' => 2,
            'oxygen_saturation' => 2,
        ]);
        $this->assertEquals(10, $score);
        $this->assertTrue(AnesthesiaRecord::isReadyForPacuDischarge($score));

        $user = $this->createReceptionistUser();
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $response = $this->actingAs($user)->postJson(route('reception.advanced-specialty.anesthesia'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'surgery_name' => 'Phẫu thuật thay khớp háng toàn phần',
            'anesthesia_type' => 'spinal',
            'asa_class' => 'II',
            'mallampati_score' => 2,
            'aldrete_score' => 10,
            'pacu_discharge_criteria' => 'met',
            'total_fentanyl_mcg' => 100,
            'anesthesia_notes' => 'Gây tê tủy sống ổn định, huyết động kiểm soát tốt',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('anesthesia_records', [
            'patient_id' => $patient->id,
            'aldrete_score' => 10,
            'pacu_discharge_criteria' => 'met',
        ]);
    }

    // 12. Ventilator Record & ARDS Berlin
    public function test_ventilator_record_ards_berlin_classification_and_weaning_sbt(): void
    {
        $pf = VentilatorRecord::calculatePf(120, 0.6);
        $this->assertEquals(200.0, $pf);
        $this->assertEquals('mild', VentilatorRecord::classifyBerlin(250));
        $this->assertEquals('severe', VentilatorRecord::classifyBerlin(80));

        $dp = VentilatorRecord::calculateDrivingPressure(22, 8);
        $this->assertEquals(14.0, $dp);

        $user = $this->createReceptionistUser();
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $response = $this->actingAs($user)->postJson(route('reception.advanced-specialty.ventilator'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'ventilation_mode' => 'ac_vc',
            'tidal_volume_ml' => 420,
            'peep' => 10,
            'fio2' => 0.6,
            'plateau_pressure' => 24,
            'pao2' => 95,
            'pco2' => 42,
            'ph_value' => 7.38,
            'spo2' => 96,
            'weaning_status' => 'not_started',
            'nursing_notes' => 'Chiến lược thông khí bảo vệ phổi ARDS Net',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('ventilator_records', [
            'patient_id' => $patient->id,
            'ventilation_mode' => 'ac_vc',
            'driving_pressure' => 14.0,
        ]);
    }

    // 13. Wound Care Assessment
    public function test_wound_care_push_score_braden_scale_and_dressing_recommendation(): void
    {
        $area = WoundCareAssessment::calculateWoundArea(4.5, 3.0);
        $this->assertEquals(13.5, $area);

        $this->assertEquals('very_high', WoundCareAssessment::classifyBradenRisk(8));
        $this->assertEquals('Foam dressing hút dịch', WoundCareAssessment::recommendDressing('red', 'large'));

        $user = $this->createReceptionistUser();
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $response = $this->actingAs($user)->postJson(route('reception.advanced-specialty.wound-care'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'wound_type' => 'pressure_injury',
            'wound_location' => 'Vùng cùng cụt',
            'pressure_injury_stage' => 'stage_3',
            'wound_length_cm' => 4.5,
            'wound_width_cm' => 3.0,
            'wound_depth_cm' => 1.2,
            'braden_score' => 11,
            'wound_bed' => 'red',
            'exudate_amount' => 'moderate',
            'wound_infection' => false,
            'dressing_type' => 'Băng foam hydrocellular',
            'healing_trend' => 'improving',
            'assessment_notes' => 'Tổ chức hạt lên tốt, không nhiễm trùng',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('wound_care_assessments', [
            'patient_id' => $patient->id,
            'wound_area_cm2' => 13.5,
            'pressure_injury_stage' => 'stage_3',
        ]);
    }

    // 14. Pain Assessment & WHO Analgesic Ladder
    public function test_pain_assessment_who_analgesic_ladder_and_response_tracking(): void
    {
        $this->assertEquals('severe', PainAssessment::classifyPainSeverity(8));
        $this->assertEquals('step3_strong_opioid', PainAssessment::recommendAnalgesicLadder(8));
        $this->assertTrue(PainAssessment::isPainControlled(8, 2));

        $user = $this->createReceptionistUser();
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $response = $this->actingAs($user)->postJson(route('reception.advanced-specialty.pain'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'scale_type' => 'nrs',
            'pain_score' => 7.0,
            'pain_location' => 'Vết mổ bụng',
            'pain_quality' => 'sharp',
            'pain_on_movement' => true,
            'analgesic_ladder' => 'step3_strong_opioid',
            'nursing_notes' => 'Đau nhiều sau mổ, dùng PCA Morphin',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('pain_assessments', [
            'patient_id' => $patient->id,
            'pain_severity' => 'severe',
            'analgesic_ladder' => 'step3_strong_opioid',
        ]);
    }

    // 15. NICU Monitoring
    public function test_nicu_monitoring_phototherapy_threshold_and_fluid_calculation(): void
    {
        $this->assertEquals('extremely_low_birth_weight', NicuMonitoringRecord::classifyBirthWeight(850));
        $this->assertEquals('preterm', NicuMonitoringRecord::classifyGestationalAge(34));
        $this->assertTrue(NicuMonitoringRecord::needsPhototherapy(16.5, 38, 72));
        $this->assertEquals(160.0, NicuMonitoringRecord::calculateFluidNeeds(2.0, 2));

        $user = $this->createReceptionistUser();
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $response = $this->actingAs($user)->postJson(route('reception.advanced-specialty.nicu'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'gestational_age_weeks' => 33,
            'birth_weight_grams' => 1800,
            'apgar_1min' => 7,
            'apgar_5min' => 9,
            'bilirubin_total' => 14.5,
            'temperature' => 36.8,
            'spo2_percent' => 97,
            'respiratory_support' => 'cpap',
            'phototherapy_active' => true,
            'breast_feeding' => false,
            'nursing_notes' => 'Trẻ non tháng 33 tuần, đang thở CPAP và chiếu đèn',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('nicu_monitoring_records', [
            'patient_id' => $patient->id,
            'phototherapy_active' => true,
            'oxygen_support' => true,
        ]);
    }

    // 16. Mental Health Assessment (PHQ-9 & GAD-7)
    public function test_mental_health_assessment_phq9_gad7_and_suicide_risk(): void
    {
        $phq = MentalHealthAssessment::scorePHQ9([2, 2, 2, 1, 2, 1, 2, 1, 1]);
        $this->assertEquals(14, $phq['score']);
        $this->assertEquals('moderate', $phq['severity']);
        $this->assertEquals('needs_assessment', $phq['suicide_item']);

        $gad = MentalHealthAssessment::scoreGAD7([2, 2, 2, 1, 2, 1, 2]);
        $this->assertEquals(12, $gad['score']);
        $this->assertEquals('moderate', $gad['severity']);

        $user = $this->createReceptionistUser();
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $response = $this->actingAs($user)->postJson(route('reception.advanced-specialty.mental-health'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'assessment_type' => 'phq9',
            'responses' => [2, 2, 2, 1, 2, 1, 2, 1, 1],
            'suicide_risk' => 'low',
            'safety_plan_made' => true,
            'psychotherapy_recommended' => true,
            'clinical_impression' => 'Rối loạn trầm cảm mức độ vừa, có ý tưởng tự sát thoáng qua',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('mental_health_assessments', [
            'patient_id' => $patient->id,
            'total_score' => 14,
            'severity_level' => 'moderate',
            'safety_plan_made' => true,
        ]);
    }

    // 17. Liver Cirrhosis Child-Pugh & MELD
    public function test_liver_cirrhosis_child_pugh_and_meld_na_mortality_calculation(): void
    {
        $cp = LiverCirrhosisScore::calculateChildPugh(2.5, 3.0, 1.8, 'mild', 'none');
        $this->assertEquals('B', $cp['class']);
        $this->assertEquals(80.0, $cp['one_year_survival']);

        $meld = LiverCirrhosisScore::calculateMeld(1.8, 2.5, 1.8);
        $this->assertGreaterThan(15.0, $meld);

        $meldNa = LiverCirrhosisScore::calculateMeldNa($meld, 130);
        $this->assertGreaterThan($meld, $meldNa);

        $user = $this->createReceptionistUser();
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $response = $this->actingAs($user)->postJson(route('reception.advanced-specialty.liver-score'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'bilirubin_cp' => 2.5,
            'albumin_cp' => 3.0,
            'inr_cp' => 1.8,
            'ascites_grade' => 'mild',
            'encephalopathy_grade' => 'none',
            'creatinine_meld' => 1.8,
            'bilirubin_meld' => 2.5,
            'inr_meld' => 1.8,
            'sodium_meld' => 130,
            'clinical_notes' => 'Xơ gan Child B, theo dõi biến chứng cổ trướng',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('liver_cirrhosis_scores', [
            'patient_id' => $patient->id,
            'child_pugh_class' => 'B',
        ]);
    }

    // 18. Diabetic Foot Assessment & Wagner Grade
    public function test_diabetic_foot_wagner_scale_and_abi_interpretation(): void
    {
        $wagner = DiabeticFootAssessment::classifyWagner(2);
        $this->assertStringContainsString('Loét sâu đến gân', $wagner['description']);

        $this->assertEquals('normal', DiabeticFootAssessment::interpretAbi(1.05));
        $this->assertEquals('severe_pad', DiabeticFootAssessment::interpretAbi(0.35));

        $risk = DiabeticFootAssessment::calculateAmputationRisk(3, 0.35, true);
        $this->assertEquals('very_high', $risk);

        $user = $this->createReceptionistUser();
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $response = $this->actingAs($user)->postJson(route('reception.advanced-specialty.diabetic-foot'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'foot_side' => 'right',
            'wagner_grade' => 2,
            'abi_right' => 0.85,
            'neuropathy_present' => true,
            'infection_present' => true,
            'infection_severity' => 'moderate',
            'treatment_plan' => 'conservative',
            'hba1c' => 8.5,
            'assessment_notes' => 'Bàn chân ĐTĐ tổn thương gân, chưa viêm xương',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('diabetic_foot_assessments', [
            'patient_id' => $patient->id,
            'wagner_grade' => 2,
            'neuropathy_present' => true,
        ]);
    }

    // 19. Hospital Operations Dashboard
    public function test_receptionist_can_view_hospital_operations_dashboard(): void
    {
        $user = $this->createReceptionistUser();
        $response = $this->actingAs($user)->get(route('reception.hospital-operations.index'));
        $response->assertStatus(200);
        $response->assertSee('Vận Hành Bệnh Viện & Hậu Cần Thông Minh');
    }

    // 20. Operating Room Block Scheduling
    public function test_operating_room_block_scheduling_and_utilization_calculation(): void
    {
        $util = OrBlockSchedule::calculateOrUtilization(420, 480);
        $this->assertEquals(87.5, $util);

        $cases = [
            ['surgery_category' => 'elective', 'name' => 'Case 1'],
            ['surgery_category' => 'emergency', 'name' => 'Case 2'],
            ['surgery_category' => 'urgent', 'name' => 'Case 3'],
        ];
        $sorted = OrBlockSchedule::prioritySort($cases);
        $this->assertEquals('emergency', $sorted[0]['surgery_category']);

        $user = $this->createReceptionistUser();
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $response = $this->actingAs($user)->postJson(route('reception.hospital-operations.or-schedule'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'or_room' => 'OR-04',
            'surgery_date' => now()->toDateString(),
            'scheduled_start_time' => '13:30',
            'scheduled_end_time' => '15:30',
            'surgery_name' => 'Phẫu thuật nội soi cắt ruột thừa',
            'surgery_category' => 'urgent',
            'anesthesia_type' => 'general',
            'estimated_duration_minutes' => 60,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('or_block_schedules', [
            'patient_id' => $patient->id,
            'or_room' => 'OR-04',
            'block_status' => 'scheduled',
        ]);
    }

    // 21. Porter Transport Tracking
    public function test_porter_transport_tracking_sla_breach_and_priority_dispatch(): void
    {
        $wait = PorterTransportRequest::calculateWaitTime('2026-10-29 10:00:00', '2026-10-29 10:18:00');
        $this->assertEquals(18, $wait);
        $this->assertTrue(PorterTransportRequest::isSlaBreach(18, 'urgent'));
        $this->assertFalse(PorterTransportRequest::isSlaBreach(18, 'routine'));

        $user = $this->createReceptionistUser();
        $patient = $this->createPatient();

        $response = $this->actingAs($user)->postJson(route('reception.hospital-operations.porter-request'), [
            'patient_id' => $patient->id,
            'from_location' => 'Khoa Cấp Cứu A&E',
            'to_location' => 'Phòng Chụp CT Scanner',
            'transport_mode' => 'stretcher',
            'priority' => 'urgent',
            'requested_by' => 'ĐD. Lê Thị Mai',
            'oxygen_required' => true,
            'special_instructions' => 'Bệnh nhân chấn thương đầu, thở oxy 3L/phút',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('porter_transport_requests', [
            'patient_id' => $patient->id,
            'priority' => 'urgent',
            'status' => 'pending',
            'oxygen_required' => true,
        ]);
    }

    // 22. Dietary Order Management
    public function test_dietary_order_nutrition_assessment_bmi_and_caloric_requirements(): void
    {
        $bmi = DietaryOrder::calculateBMI(75, 172);
        $this->assertEquals(25.35, $bmi);
        $this->assertEquals('obese_1', DietaryOrder::classifyBMI(25.35));

        $calories = DietaryOrder::calculateCaloricNeeds(70, 'moderate', 'surgery');
        $this->assertEquals(2520.0, $calories);

        $user = $this->createReceptionistUser();
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $response = $this->actingAs($user)->postJson(route('reception.hospital-operations.dietary-order'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'diet_type' => 'diabetic',
            'caloric_target' => 1800,
            'protein_target_g' => 75,
            'texture' => 'minced',
            'order_start_date' => now()->toDateString(),
            'dietitian_notes' => 'Chế độ ăn kiểm soát carbohydrate, chia 4 bữa nhỏ',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('dietary_orders', [
            'patient_id' => $patient->id,
            'diet_type' => 'diabetic',
            'is_active' => true,
        ]);
    }

    // 23. Finance & HR Dashboard
    public function test_receptionist_can_view_finance_and_hr_dashboard(): void
    {
        $user = $this->createReceptionistUser();
        $response = $this->actingAs($user)->get(route('reception.finance-hr.index'));
        $response->assertStatus(200);
        $response->assertSee('Tài Chính & Quản Trị Y Tế Nâng Cao');
    }

    // 24. DRG Billing Calculation
    public function test_drg_billing_expected_payment_cost_variance_and_cmi_calculation(): void
    {
        $expected = DrgBillingRecord::calculateExpectedPayment(1.85, 25000000);
        $this->assertEquals(46250000.0, $expected);

        $variance = DrgBillingRecord::calculateCostVariance(50000000, 46250000);
        $this->assertEquals(3750000.0, $variance);

        $copay = DrgBillingRecord::calculatePatientCopay(46250000, 80);
        $this->assertEquals(9250000.0, $copay);

        $this->assertTrue(DrgBillingRecord::isOutlierCase(12, 5, 1.85));

        $cmi = DrgBillingRecord::getCaseMixIndex([1.5, 2.0, 1.0]);
        $this->assertEquals(1.5, $cmi);

        $user = $this->createReceptionistUser();
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $response = $this->actingAs($user)->postJson(route('reception.finance-hr.drg-billing'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'drg_code' => 'DRG-470',
            'drg_description' => 'Major Joint Replacement without MCC',
            'principal_diagnosis_icd10' => 'M16.1',
            'length_of_stay_days' => 4,
            'drg_weight' => 1.85,
            'base_rate' => 25000000,
            'insurance_type' => 'bhyt',
            'insurance_coverage_pct' => 80,
            'actual_cost' => 45000000,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('drg_billing_records', [
            'patient_id' => $patient->id,
            'drg_code' => 'DRG-470',
            'expected_payment' => 46250000,
        ]);
    }

    // 25. Research Project Management
    public function test_clinical_research_project_enrollment_tracking_and_budget_utilization(): void
    {
        $enrollmentRate = ResearchProject::getEnrollmentRate(85, 100);
        $this->assertEquals(85.0, $enrollmentRate);

        $budgetUtil = ResearchProject::getBudgetUtilization(450000000, 500000000);
        $this->assertEquals(90.0, $budgetUtil);

        $user = $this->createReceptionistUser();
        $code = 'PROJ-GCP-' . rand(1000, 9999);

        $response = $this->actingAs($user)->postJson(route('reception.finance-hr.research-project'), [
            'project_code' => $code,
            'title' => 'Nghiên cứu thử nghiệm lâm sàng ngẫu nhiên có đối chứng thuốc điều trị suy tim mới',
            'principal_investigator' => 'PGS.TS Nguyễn Văn Hưng',
            'department' => 'Viện Nghiên Cứu Tâm Anh',
            'research_type' => 'clinical_trial',
            'start_date' => now()->toDateString(),
            'target_enrollment' => 150,
            'budget_approved' => 1200000000,
            'funding_source' => 'Bộ Y Tế & Quỹ Khoa Học Công Nghệ',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('research_projects', [
            'project_code' => $code,
            'status' => 'planning',
        ]);
    }

    // 26. Patient Experience Dashboard
    public function test_receptionist_can_view_patient_experience_dashboard(): void
    {
        $user = $this->createReceptionistUser();
        $response = $this->actingAs($user)->get(route('reception.patient-experience.index'));
        $response->assertStatus(200);
        $response->assertSee('Trải Nghiệm Bệnh Nhân & Chăm Sóc Số');
    }

    // 27. Patient Digital Twin
    public function test_patient_digital_twin_360_health_score_and_care_gap_identification(): void
    {
        $score = PatientDigitalTwin::calculateHealthScore([
            'chronic_conditions_count' => 2,
            'er_visits' => 1,
            'hospitalizations' => 0,
            'medication_adherence_pct' => 90,
        ]);
        $this->assertLessThan(100, $score);
        $this->assertEquals('moderate', PatientDigitalTwin::classifyRisk(75));

        $gaps = PatientDigitalTwin::identifyCareGaps(['diabetes_t2'], [], []);
        $this->assertNotEmpty($gaps);

        $user = $this->createReceptionistUser();
        $patient = $this->createPatient();

        $response = $this->actingAs($user)->postJson(route('reception.patient-experience.digital-twin'), [
            'patient_id' => $patient->id,
            'chronic_conditions' => ['diabetes_t2', 'hypertension'],
            'allergy_profile' => ['Penicillin', 'Aspirin'],
            'medication_history' => ['Metformin 500mg', 'Amlodipine 5mg'],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('patient_digital_twins', [
            'patient_id' => $patient->id,
        ]);
    }

    // 28. Telehealth Consultation
    public function test_telehealth_consultation_scheduling_and_meeting_link_generation(): void
    {
        $mid = TelehealthConsultation::generateMeetingId();
        $link = TelehealthConsultation::generateMeetingLink($mid);
        $this->assertStringContainsString('https://meet.benhvientamanh.vn/', $link);

        $user = $this->createReceptionistUser();
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $response = $this->actingAs($user)->postJson(route('reception.patient-experience.telehealth'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'consultation_type' => 'video_call',
            'scheduled_at' => now()->addDays(2)->format('Y-m-d 14:00:00'),
            'consultation_reason' => 'follow_up',
            'chief_complaint' => 'Tái khám đường huyết và tư vấn điều chỉnh liều thuốc tiểu đường',
            'consultation_fee' => 300000,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('telehealth_consultations', [
            'patient_id' => $patient->id,
            'status' => 'scheduled',
            'platform' => 'Zoom for Healthcare',
        ]);
    }

    // 29. Discharge Checklist
    public function test_discharge_checklist_readmission_risk_score_and_completion_tracking(): void
    {
        $pct = DischargeChecklist::calculateCompletionPercentage([
            'discharge_summary_signed' => true,
            'medications_reconciled' => true,
            'medications_dispensed' => true,
            'patient_education_done' => true,
        ]);
        $this->assertGreaterThanOrEqual(40.0, $pct);

        $risk = DischargeChecklist::calculateReadmissionRisk([
            'multiple_comorbidities' => true,
            'prior_30d_readmission' => true,
        ]);
        $this->assertEquals(6, $risk);

        $user = $this->createReceptionistUser();
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $response = $this->actingAs($user)->postJson(route('reception.patient-experience.discharge-checklist'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'discharge_date' => now()->toDateString(),
            'discharge_type' => 'home',
            'discharge_summary_signed' => true,
            'medications_reconciled' => true,
            'patient_education_done' => true,
            'follow_up_appointment_booked' => true,
            'follow_up_date' => now()->addDays(7)->toDateString(),
            'red_flag_symptoms_explained' => true,
            'emergency_contact_confirmed' => true,
            'discharge_instructions' => 'Nghỉ ngơi tại nhà, dùng thuốc đúng đơn',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('discharge_checklists', [
            'patient_id' => $patient->id,
            'discharge_type' => 'home',
        ]);
    }

    // 30. Post-Discharge Follow-up
    public function test_post_discharge_follow_up_30_days_and_escalation_protocol(): void
    {
        $this->assertTrue(PostdischargeFollowup::needsEscalation(false, true, 8));
        $this->assertTrue(PostdischargeFollowup::needsEscalation(true, false, 8));
        $this->assertFalse(PostdischargeFollowup::needsEscalation(true, true, 8));

        $user = $this->createReceptionistUser();
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $response = $this->actingAs($user)->postJson(route('reception.patient-experience.followup'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'days_post_discharge' => 3,
            'contact_method' => 'phone_call',
            'contact_attempt' => 'first',
            'patient_reached' => true,
            'medication_adherence' => true,
            'symptoms_resolved' => true,
            'patient_wellbeing_score' => 8.5,
            'call_agent_name' => 'CSKH Nguyễn Thị Hoa',
            'call_notes' => 'Bệnh nhân hồi phục tốt, không có biến chứng',
            'outcome' => 'no_issues',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('postdischarge_followups', [
            'patient_id' => $patient->id,
            'patient_reached' => true,
            'escalation_needed' => false,
        ]);
    }

    // 31. Community Health Screening Campaign
    public function test_community_health_screening_campaign_detection_rate_and_cost_effectiveness(): void
    {
        $rate = HealthScreeningCampaign::calculateDetectionRate(45, 500);
        $this->assertEquals(9.0, $rate);

        $costEff = HealthScreeningCampaign::calculateCostEffectiveness(45000000, 45);
        $this->assertEquals(1000000.0, $costEff);

        $user = $this->createReceptionistUser();
        $code = 'CAMP-' . rand(1000, 9999);

        $response = $this->actingAs($user)->postJson(route('reception.patient-experience.screening-campaign'), [
            'campaign_code' => $code,
            'campaign_name' => 'Chiến dịch Tầm soát Đột quỵ & Bệnh lý Tim mạch Cộng đồng 2026',
            'campaign_type' => 'cardiovascular',
            'target_population' => 'Người cao tuổi từ 50-75 tuổi trên địa bàn Hà Nội',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(14)->toDateString(),
            'venue' => 'Hội trường Bệnh viện Đa khoa Tâm Anh',
            'target_participants' => 1000,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('health_screening_campaigns', [
            'campaign_code' => $code,
            'status' => 'planning',
        ]);
    }

    // 32. Loyalty Points Program
    public function test_patient_loyalty_program_tier_calculation_and_points_redemption(): void
    {
        $this->assertEquals('Platinum', LoyaltyPointsLog::calculateTierLevel(65000));
        $this->assertEquals('Gold', LoyaltyPointsLog::calculateTierLevel(25000));
        $this->assertEquals('Silver', LoyaltyPointsLog::calculateTierLevel(6000));
        $this->assertEquals('Bronze', LoyaltyPointsLog::calculateTierLevel(1000));

        $points = LoyaltyPointsLog::calculateEarnPoints(5000000);
        $this->assertEquals(500, $points);

        $user = $this->createReceptionistUser();
        $patient = $this->createPatient();

        $response = $this->actingAs($user)->postJson(route('reception.patient-experience.loyalty-points'), [
            'patient_id' => $patient->id,
            'transaction_type' => 'earn',
            'points_change' => 2500,
            'earn_reason' => 'Thanh toán viện phí đợt khám chuyên khoa',
            'monetary_value' => 250000,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('loyalty_points_logs', [
            'patient_id' => $patient->id,
            'points_balance' => 2500,
            'tier_level' => 'Bronze',
        ]);
    }

    // 33. Chronic Disease Cohort Management
    public function test_chronic_disease_cohort_population_health_risk_and_target_metrics(): void
    {
        $targets = ChronicDiseaseCohort::getTargetMetrics('diabetes_t2');
        $this->assertArrayHasKey('hba1c', $targets);

        $onTarget = ChronicDiseaseCohort::assessMetricsOnTarget($targets, [
            'hba1c' => '6.8%',
            'sbp' => '125',
            'ldl' => '2.1',
            'bmi' => '22.5',
        ]);
        $this->assertTrue($onTarget);

        $popRisk = ChronicDiseaseCohort::calculatePopulationRisk([
            ['er_visits_12mo' => 3, 'hospitalizations_12mo' => 1, 'metrics_on_target' => false],
            ['er_visits_12mo' => 0, 'hospitalizations_12mo' => 0, 'metrics_on_target' => true],
        ]);
        $this->assertEquals(50.0, $popRisk['high_risk_pct']);
        $this->assertEquals(50.0, $popRisk['on_target_pct']);

        $user = $this->createReceptionistUser();
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $response = $this->actingAs($user)->postJson(route('reception.patient-experience.chronic-cohort'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'disease_type' => 'diabetes_t2',
            'diagnosis_date' => '2023-05-10',
            'disease_stage' => 'moderate',
            'care_manager' => 'CNĐD. Trần Thị Thu',
            'care_plan_notes' => 'Kế hoạch theo dõi HbA1c 3 tháng/lần, kiểm soát chế độ ăn',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('chronic_disease_cohorts', [
            'patient_id' => $patient->id,
            'disease_type' => 'diabetes_t2',
            'enrolled_in_program' => true,
        ]);
    }
}
