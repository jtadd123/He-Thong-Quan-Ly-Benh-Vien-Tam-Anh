<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PatientProfile;
use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\TherapeuticDrugMonitoring;
use App\Models\ParenteralNutrition;
use App\Models\AdrAdverseDrugReport;
use App\Models\RehabilitationCarePlan;
use App\Models\HerbalPrescription;
use App\Models\BhutaniJaundiceEvaluation;
use App\Models\DonorMilkBatch;
use App\Models\GreenHospitalEsgLog;
use App\Models\RadiationDoseLog;
use Tests\TestCase;

class Wave4AdvancedClinicalHospitalTest extends TestCase
{
    private function createReceptionistUser(): User
    {
        return User::factory()->create([
            'role' => 'receptionist',
            'is_active' => true,
        ]);
    }

    private function createPatient(): PatientProfile
    {
        return PatientProfile::create([
            'patient_code' => 'BN-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5)) . rand(100, 999),
            'full_name' => 'Lê Hoàng Yến',
            'phone' => '0912' . rand(100000, 999999),
            'dob' => '1992-08-20',
            'gender' => 'female',
            'identity_card' => '001292' . rand(100000, 999999),
            'address' => 'Hà Nội',
        ]);
    }

    private function createDoctor(): Doctor
    {
        $user = User::factory()->create([
            'role' => 'doctor',
            'is_active' => true,
        ]);

        $specialty = Specialty::firstOrCreate(
            ['slug' => 'duoc-lam-sang'],
            ['name' => 'Dược Lâm Sàng', 'description' => 'Khoa Dược & Dược lý lâm sàng']
        );

        return Doctor::create([
            'user_id' => $user->id,
            'specialty_id' => $specialty->id,
            'qualification' => 'ThS. Dược Sĩ Lâm Sàng',
            'consultation_fee' => 250000,
            'room_number' => 'P.108',
            'is_available' => true,
        ]);
    }

    /**
     * Test 1: Xem Dashboard Dược Lâm Sàng, TDM & TPN
     */
    public function test_receptionist_can_view_clinical_pharmacy_dashboard(): void
    {
        $receptionist = $this->createReceptionistUser();

        $response = $this->actingAs($receptionist)
            ->get(route('reception.clinical-pharmacy.index'));

        $response->assertStatus(200);
        $response->assertSee('Dược Lâm Sàng');
        $response->assertSee('TDM');
        $response->assertSee('TPN');
        $response->assertSee('ADR');
    }

    /**
     * Test 2: Đo nồng độ thuốc hẹp TDM Vancomycin và tự động tính toán khuyến cáo chỉnh liều
     */
    public function test_therapeutic_drug_monitoring_calculates_recommendations_and_saves(): void
    {
        $receptionist = $this->createReceptionistUser();
        $patient = $this->createPatient();

        // 1. Kiểm tra tính toán nồng độ dưới ngưỡng điều trị (< 10 mg/L)
        $subTherapeutic = TherapeuticDrugMonitoring::evaluateConcentration('Vancomycin', 8.2);
        $this->assertEquals('subtherapeutic', $subTherapeutic['status']);
        $this->assertStringContainsString('dưới ngưỡng điều trị', $subTherapeutic['recommendation']);

        // 2. Kiểm tra tính toán nồng độ đạt mục tiêu điều trị (10 - 20 mg/L)
        $targetTherapeutic = TherapeuticDrugMonitoring::evaluateConcentration('Vancomycin', 14.5);
        $this->assertEquals('therapeutic', $targetTherapeutic['status']);
        $this->assertStringContainsString('đạt mục tiêu điều trị', $targetTherapeutic['recommendation']);

        // 3. Kiểm tra tính toán nồng độ vượt ngưỡng nguy cơ độc thận (> 20 mg/L)
        $toxicTherapeutic = TherapeuticDrugMonitoring::evaluateConcentration('Vancomycin', 26.0);
        $this->assertEquals('toxic', $toxicTherapeutic['status']);
        $this->assertStringContainsString('độc tính thận', $toxicTherapeutic['recommendation']);

        // 4. Gửi request tạo phiếu TDM vào hệ thống
        $postData = [
            'patient_id' => $patient->id,
            'medication_name' => 'Vancomycin',
            'trough_concentration_cmin' => 8.2,
            'peak_concentration_cmax' => 25.4,
            'creatinine_clearance_crcl' => 75.5,
            'target_range' => '15.0 - 20.0 mg/L',
            'dose_adjustment_recommendation' => 'Tăng liều dùng từ 1000mg q12h lên 1250mg q12h do nồng độ đáy Cmin đang dưới ngưỡng diệt khuẩn tối ưu.',
            'pharmacist_evaluator' => 'DS.CKI Nguyễn Thị Mai',
        ];

        $response = $this->actingAs($receptionist)
            ->post(route('reception.clinical-pharmacy.tdm'), $postData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('therapeutic_drug_monitorings', [
            'patient_id' => $patient->id,
            'medication_name' => 'Vancomycin',
            'is_within_target' => 0,
        ]);
    }

    /**
     * Test 3: Tính toán túi dinh dưỡng tĩnh mạch toàn phần TPN All-in-One
     */
    public function test_parenteral_nutrition_all_in_one_calculation_and_infusion_safety(): void
    {
        $receptionist = $this->createReceptionistUser();
        $patient = $this->createPatient();

        // 1. Kiểm tra tính toán tổng calo và tỷ lệ kcal/kg
        $calc = ParenteralNutrition::calculateCalories(500, 1000, 250, 50.0);
        $this->assertGreaterThan(1000, $calc['total_calories']);
        $this->assertGreaterThan(20, $calc['calories_per_kg']);

        // 2. Gửi request tạo đơn TPN
        $postData = [
            'patient_id' => $patient->id,
            'total_calories_kcal' => 1650,
            'glucose_grams' => 200.0,
            'amino_acids_grams' => 70.0,
            'lipids_grams' => 50.0,
            'infusion_route' => 'CENTRAL_LINE',
            'refeeding_risk' => 0,
            'clinical_dietitian' => 'ThS.BS Dinh Dưỡng Trần Văn Bách',
        ];

        $response = $this->actingAs($receptionist)
            ->post(route('reception.clinical-pharmacy.tpn'), $postData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('parenteral_nutritions', [
            'patient_id' => $patient->id,
            'total_calories_kcal' => 1650,
            'infusion_route' => 'CENTRAL_LINE',
        ]);
    }

    /**
     * Test 4: Báo cáo biến cố bất lợi có hại của thuốc ADR theo thang điểm Naranjo
     */
    public function test_adverse_drug_reaction_reporting_with_naranjo_score(): void
    {
        $receptionist = $this->createReceptionistUser();
        $patient = $this->createPatient();

        // 1. Tính toán thang điểm Naranjo
        $this->assertEquals('doubtful', AdrAdverseDrugReport::calculateCausality(0));
        $this->assertEquals('possible', AdrAdverseDrugReport::calculateCausality(3));
        $this->assertEquals('probable', AdrAdverseDrugReport::calculateCausality(6));
        $this->assertEquals('definite', AdrAdverseDrugReport::calculateCausality(9));

        // 2. Gửi request báo cáo ADR Naranjo = 9 điểm (Chắc chắn do thuốc)
        $postData = [
            'patient_id' => $patient->id,
            'suspected_medication' => 'Augmentin 1g (Amoxicillin + Clavulanic Acid)',
            'reaction_description' => 'Mày đay toàn thân, phù mạch quanh mí mắt và môi sau khi uống liều đầu 30 phút',
            'severity_level' => 'SEVERE_LIFE_THREATENING',
            'causality_naranjo_score' => 9,
            'reporter_name' => 'BS. CKII Lê Quốc Bảo',
        ];

        $response = $this->actingAs($receptionist)
            ->post(route('reception.clinical-pharmacy.adr'), $postData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('adr_adverse_drug_reports', [
            'patient_id' => $patient->id,
            'suspected_medication' => 'Augmentin 1g (Amoxicillin + Clavulanic Acid)',
            'causality_naranjo_score' => 9,
            'causality_category' => 'DEFINITE',
        ]);
    }

    /**
     * Test 5: Xem Dashboard Phục Hồi Chức Năng, YHCT, ESG & ALARA
     */
    public function test_receptionist_can_view_rehab_traditional_esg_dashboard(): void
    {
        $receptionist = $this->createReceptionistUser();

        $response = $this->actingAs($receptionist)
            ->get(route('reception.rehab-esg.index'));

        $response->assertStatus(200);
        $response->assertSee('Phục Hồi Chức Năng');
        $response->assertSee('Bệnh Viện Xanh ESG');
        $response->assertSee('Thuốc Thang Đông Y');
        $response->assertSee('Vàng Da Sơ Sinh Bhutani');
        $response->assertSee('Sữa Mẹ Pasteur');
    }

    /**
     * Test 6: Lập kế hoạch Phục hồi chức năng và đánh giá thang FIM
     */
    public function test_rehabilitation_care_plan_with_fim_score(): void
    {
        $receptionist = $this->createReceptionistUser();
        $patient = $this->createPatient();

        // 1. Kiểm tra mức độ độc lập theo FIM score
        $this->assertEquals('maximal_assistance', RehabilitationCarePlan::interpretFimScore(40));
        $this->assertEquals('moderate_assistance', RehabilitationCarePlan::interpretFimScore(60));
        $this->assertEquals('minimal_assistance', RehabilitationCarePlan::interpretFimScore(95));
        $this->assertEquals('complete_independence', RehabilitationCarePlan::interpretFimScore(115));

        // 2. Gửi request tạo kế hoạch PHCN (FIM = 75 -> Cần trợ giúp mức độ vừa MODERATE_ASSISTANCE)
        $postData = [
            'patient_id' => $patient->id,
            'diagnosis' => 'Yếu nửa người bên trái sau Tai biến mạch máu não nhồi máu não ngày thứ 14',
            'fim_score_total' => 75,
            'barthel_index' => 60,
            'assigned_therapy_type' => 'Vật lý trị liệu & Vận động trị liệu (PT)',
            'weekly_goals' => 'Tự ngồi vững ở mép giường 15 phút, đứng lên có trợ giúp nhẹ',
            'pt_specialist' => 'KTV. Hoàng Minh Tuấn',
        ];

        $response = $this->actingAs($receptionist)
            ->post(route('reception.rehab-esg.rehab-plan'), $postData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rehabilitation_care_plans', [
            'patient_id' => $patient->id,
            'fim_score_total' => 75,
            'functional_status' => 'MODERATE_ASSISTANCE',
        ]);
    }

    /**
     * Test 7: Kê đơn Đông y YHCT tự động phát hiện Tương kỵ Thập Bát Phản
     */
    public function test_traditional_herbal_prescription_detects_eighteen_incompatible_medicaments(): void
    {
        $receptionist = $this->createReceptionistUser();
        $patient = $this->createPatient();

        // 1. Kiểm tra thuật toán phát hiện Thập Bát Phản: Cam thảo phản Cam toại
        $incompatibles = HerbalPrescription::checkEighteenIncompatibilities([
            ['name' => 'Cam thảo', 'dose' => 6],
            ['name' => 'Cam toại', 'dose' => 3],
            ['name' => 'Bạch truật', 'dose' => 12],
        ]);

        $this->assertNotEmpty($incompatibles);
        $this->assertStringContainsString('Cam Thảo phản Cam Toại', $incompatibles[0]);

        // 2. Kiểm tra thang thuốc an toàn không có tương kỵ
        $safeList = HerbalPrescription::checkEighteenIncompatibilities([
            ['name' => 'Đương quy', 'dose' => 12],
            ['name' => 'Thục địa', 'dose' => 15],
            ['name' => 'Bạch thược', 'dose' => 10],
            ['name' => 'Xuyên khung', 'dose' => 8],
        ]);
        $this->assertEmpty($safeList);

        // 3. Kê đơn thuốc thang có vị vi phạm và xác nhận hệ thống tự động gắn cờ cảnh báo
        $postData = [
            'patient_id' => $patient->id,
            'remedy_name' => 'Thang Thập Toàn Đại Bổ Biến Phương',
            'herbal_ingredients' => "Cam thảo: 6g\nCam toại: 3g\nĐẳng sâm: 12g\nBạch linh: 10g",
            'decoction_bags_count' => 6,
            'traditional_doctor' => 'Lương Y CKII Phạm Minh Tuệ',
        ];

        $response = $this->actingAs($receptionist)
            ->post(route('reception.rehab-esg.herbal'), $postData);

        $response->assertRedirect();
        $response->assertSessionHas('error'); // Báo lỗi cảnh báo tương kỵ

        $this->assertDatabaseHas('herbal_prescriptions', [
            'patient_id' => $patient->id,
            'has_incompatibility_contraindication' => 1,
        ]);
    }

    /**
     * Test 8: Đánh giá vàng da sơ sinh theo Nomogram Bhutani và tự động chỉ định chiếu đèn
     */
    public function test_bhutani_neonatal_jaundice_nomogram_evaluation(): void
    {
        $receptionist = $this->createReceptionistUser();
        $patient = $this->createPatient();

        // 1. Kiểm tra thuật toán phân vùng nguy cơ Bhutani:
        // Trẻ 48 giờ tuổi, Bilirubin = 15.5 mg/dL -> Vùng nguy cơ cao (> 13.0 mg/dL) -> Cần chiếu đèn tích cực
        $eval = BhutaniJaundiceEvaluation::evaluateRisk(48, 15.5);
        $this->assertEquals('high_risk', $eval['risk_zone']);
        $this->assertEquals('intensive_phototherapy', $eval['phototherapy_indication']);

        // Trẻ 48 giờ tuổi, Bilirubin = 6.0 mg/dL -> Vùng nguy cơ thấp (< 13.0 mg/dL)
        $evalLow = BhutaniJaundiceEvaluation::evaluateRisk(48, 6.0);
        $this->assertEquals('low_risk', $evalLow['risk_zone']);
        $this->assertEquals('none', $evalLow['phototherapy_indication']);

        // 2. Gửi request đánh giá vàng da sơ sinh
        $postData = [
            'infant_patient_id' => $patient->id,
            'age_in_hours' => 48,
            'total_serum_bilirubin_mg_dl' => 15.5,
            'neonatologist' => 'BS. CKI Sơ Sinh Nguyễn Thu Hà',
        ];

        $response = $this->actingAs($receptionist)
            ->post(route('reception.rehab-esg.jaundice'), $postData);

        $response->assertRedirect();
        $response->assertSessionHas('warning'); // Vàng da vùng nguy cơ cao

        $this->assertDatabaseHas('bhutani_jaundice_evaluations', [
            'infant_patient_id' => $patient->id,
            'risk_zone' => 'HIGH_RISK',
            'phototherapy_indicated' => 1,
        ]);
    }

    /**
     * Test 9: Quản lý ngân hàng sữa mẹ thanh trùng Pasteur đạt chuẩn vi sinh
     */
    public function test_donor_milk_batch_pasteurization_and_microbiology_clearance(): void
    {
        $receptionist = $this->createReceptionistUser();

        $postData = [
            'donor_code' => 'DONOR-HANOI-08',
            'volume_ml' => 500,
            'microbiology_result' => 'PASSED_STERILE',
        ];

        $response = $this->actingAs($receptionist)
            ->post(route('reception.rehab-esg.milk-batch'), $postData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('donor_milk_batches', [
            'donor_code' => 'DONOR-HANOI-08',
            'status' => 'available',
            'microbiology_result' => 'PASSED_STERILE',
        ]);
    }

    /**
     * Test 10: Giám sát an toàn bức xạ ALARA mSv và Bệnh viện Xanh ESG
     */
    public function test_green_hospital_esg_and_radiation_dose_alara_tracking(): void
    {
        $receptionist = $this->createReceptionistUser();
        $patient = $this->createPatient();

        // 1. Kiểm tra phân loại liều bức xạ ALARA
        $this->assertEquals('low', RadiationDoseLog::classifyRiskLevel(0.8));
        $this->assertEquals('medium', RadiationDoseLog::classifyRiskLevel(4.5));
        $this->assertEquals('high', RadiationDoseLog::classifyRiskLevel(12.0));
        $this->assertEquals('critical', RadiationDoseLog::classifyRiskLevel(25.0));

        // 2. Gửi request ghi nhận liều tia bức xạ CT Scan Ngực
        $postData = [
            'patient_id' => $patient->id,
            'procedure_name' => 'Chụp Cắt Lớp Vi Tính Lồng Ngực Liều Thấp (HRCT)',
            'dose_msv' => 4.25,
            'radiology_technician' => 'KTV. Hoàng Minh Tuấn',
        ];

        $response = $this->actingAs($receptionist)
            ->post(route('reception.rehab-esg.radiation'), $postData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('radiation_dose_logs', [
            'patient_id' => $patient->id,
            'dose_msv' => 4.25,
            'alara_status' => 'OPTIMAL_SAFE',
        ]);
    }
}
