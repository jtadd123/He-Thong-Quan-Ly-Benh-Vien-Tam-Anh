<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PatientProfile;
use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\OperatingRoom;
use App\Models\SurgicalCase;
use App\Models\BloodUnit;
use App\Models\BiomedicalEquipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class NextGenSmartHospitalOperationsTest extends TestCase
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
            'full_name' => 'Nguyễn Thị Hoa',
            'phone' => '0987' . rand(100000, 999999),
            'dob' => '1990-05-15',
            'gender' => 'female',
            'identity_card' => '001290' . rand(100000, 999999),
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
            ['slug' => 'ngoai-tong-quat'],
            ['name' => 'Ngoại Tổng Quát', 'description' => 'Phẫu thuật ngoại khoa']
        );

        return Doctor::create([
            'user_id' => $user->id,
            'specialty_id' => $specialty->id,
            'qualification' => 'ThS.BS Chuyên Khoa II',
            'consultation_fee' => 300000,
            'room_number' => 'P.302',
            'is_available' => true,
        ]);
    }

    /**
     * Test 1: Dashboard Phòng mổ & Lên lịch mổ mới
     */
    public function test_receptionist_can_view_smart_or_dashboard_and_schedule_surgery(): void
    {
        $receptionist = $this->createReceptionistUser();
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $room = OperatingRoom::firstOrCreate(
            ['room_number' => 'OR-TEST-01'],
            [
                'room_name' => 'Phòng Mổ Kiểm Thử Hybrid',
                'zone' => 'Khu vô trùng Tầng 3',
                'cleanliness_class' => 'ISO_5',
                'status' => 'available',
                'has_laminar_flow' => true,
            ]
        );

        $response = $this->actingAs($receptionist)->get(route('reception.operating-room.index'));
        $response->assertStatus(200);
        $response->assertSee('Phòng Mổ Thông Minh');

        // Tạo ca mổ mới
        $storeResponse = $this->actingAs($receptionist)->post(route('reception.operating-room.store-case'), [
            'patient_id' => $patient->id,
            'room_id' => $room->id,
            'lead_surgeon_id' => $doctor->id,
            'surgical_procedure' => 'Phẫu thuật cắt túi mật nội soi',
            'icd10_code' => 'K80.2',
            'priority' => 'elective',
            'scheduled_start' => Carbon::now()->addHours(2)->format('Y-m-d H:i:s'),
        ]);

        $storeResponse->assertRedirect(route('reception.operating-room.index'));
        $this->assertDatabaseHas('surgical_cases', [
            'patient_id' => $patient->id,
            'surgical_procedure' => 'Phẫu thuật cắt túi mật nội soi',
            'priority' => 'elective',
            'phase' => 'scheduled',
        ]);
    }

    /**
     * Test 2: Tiến trình ca mổ, Kiểm đếm gạc JCI & Chấm điểm Aldrete PACU
     */
    public function test_surgical_phase_sponge_count_and_pacu_aldrete(): void
    {
        $receptionist = $this->createReceptionistUser();
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $case = SurgicalCase::create([
            'case_code' => 'SURG-TEST-' . rand(1000, 9999),
            'patient_id' => $patient->id,
            'lead_surgeon_id' => $doctor->id,
            'surgical_procedure' => 'Phẫu thuật kết hợp xương đùi',
            'priority' => 'urgent',
            'phase' => 'scheduled',
            'scheduled_start' => Carbon::now(),
        ]);

        // Chuyển sang phẫu thuật
        $phaseResponse = $this->actingAs($receptionist)->patch(route('reception.operating-room.update-phase', $case->id), [
            'phase' => 'surgery',
        ]);
        $phaseResponse->assertSessionHas('success');
        $this->assertEquals('surgery', $case->fresh()->phase);

        // Kiểm đếm gạc khớp 100%
        $spongeResponse = $this->actingAs($receptionist)->post(route('reception.operating-room.sponge-count', $case->id), [
            'item_name' => 'Gạc phẫu thuật cản quang 10x10',
            'initial_count' => 15,
            'added_count' => 5,
            'before_closure_count' => 20,
            'final_count' => 20,
            'scrub_nurse_name' => 'ĐD. Vòng Trong',
            'circulating_nurse_name' => 'ĐD. Vòng Ngoài',
        ]);
        $spongeResponse->assertSessionHas('success');
        $this->assertDatabaseHas('surgical_sponge_counts', [
            'surgical_case_id' => $case->id,
            'is_matched' => true,
        ]);

        // Chuyển PACU và chấm điểm Aldrete >= 9
        $aldreteResponse = $this->actingAs($receptionist)->post(route('reception.operating-room.aldrete-score', $case->id), [
            'respiration_score' => 2,
            'circulation_score' => 2,
            'consciousness_score' => 2,
            'activity_score' => 2,
            'color_score' => 1,
            'evaluated_by' => 'BS. Trưởng PACU',
        ]);
        $aldreteResponse->assertSessionHas('success');
        $this->assertDatabaseHas('pacu_aldrete_scores', [
            'surgical_case_id' => $case->id,
            'total_score' => 9,
            'is_eligible_for_discharge' => true,
        ]);

        // Kiểm tra sảnh chờ thân nhân
        $waitingResponse = $this->get(route('public.or-waiting-board'));
        $waitingResponse->assertStatus(200);
        $waitingResponse->assertSee('BẢNG ĐIỆN TỬ THEO DÕI TIẾN TRÌNH PHẪU THUẬT');
    }

    /**
     * Test 3: AI Lâm Sàng PACS Triage & qSOFA Dự Báo Sốc Nhiễm Khuẩn
     */
    public function test_clinical_ai_pacs_and_sepsis_qsofa(): void
    {
        $receptionist = $this->createReceptionistUser();
        $patient = $this->createPatient();

        $viewResponse = $this->actingAs($receptionist)->get(route('reception.clinical-ai.index'));
        $viewResponse->assertStatus(200);

        // Nạp PACS AI phát hiện tràn khí màng phổi cấp
        $pacsResponse = $this->actingAs($receptionist)->post(route('reception.clinical-ai.pacs'), [
            'patient_id' => $patient->id,
            'modality' => 'X-RAY',
            'body_part' => 'Lồng ngực',
            'finding_detected' => 'Tràn khí màng phổi áp lực',
            'confidence_score' => 95.5,
            'triage_urgency' => 'CRITICAL',
        ]);
        $pacsResponse->assertSessionHas('success');
        $this->assertDatabaseHas('pacs_ai_analyses', [
            'patient_id' => $patient->id,
            'is_prioritized' => true,
            'triage_urgency' => 'CRITICAL',
        ]);

        // Đánh giá qSOFA = 3 (Nguy cơ sốc cao)
        $qsofaResponse = $this->actingAs($receptionist)->post(route('reception.clinical-ai.sepsis'), [
            'patient_id' => $patient->id,
            'respiratory_rate' => 26, // >= 22 (1đ)
            'glasgow_coma_scale' => 12, // < 15 (1đ)
            'systolic_bp' => 85, // <= 100 (1đ)
            'assessed_by' => 'BS. Cấp Cứu A&E',
        ]);
        $qsofaResponse->assertSessionHas('error'); // Cảnh báo nguy cơ sốc
        $this->assertDatabaseHas('sepsis_qsofa_alerts', [
            'patient_id' => $patient->id,
            'qsofa_score' => 3,
            'risk_level' => 'HIGH_RISK_SHOCK',
            'icu_notified' => true,
        ]);
    }

    /**
     * Test 4: Bản Đồ Gen Dược Lý PGx & Tai Biến Truyền Máu Hemovigilance
     */
    public function test_pharmacogenomics_and_hemovigilance_reaction(): void
    {
        $receptionist = $this->createReceptionistUser();
        $patient = $this->createPatient();

        $bloodUnit = BloodUnit::firstOrCreate(
            ['unit_code' => 'ISBT-TEST-' . rand(1000, 9999)],
            [
                'blood_type_abo' => 'O',
                'blood_type_rh' => 'positive',
                'product_type' => 'prbc',
                'volume_ml' => 350,
                'collection_date' => Carbon::today(),
                'expiry_date' => Carbon::today()->addDays(35),
                'status' => 'in_stock',
            ]
        );

        // Lưu bản đồ gen CYP2C19
        $pgxResponse = $this->actingAs($receptionist)->post(route('reception.clinical-ai.pharmacogenomic'), [
            'patient_id' => $patient->id,
            'gene_name' => 'CYP2C19',
            'genotype' => '*2/*2',
            'phenotype' => 'Poor Metabolizer',
            'contraindicated_medication' => 'Clopidogrel',
            'recommended_alternative' => 'Ticagrelor',
        ]);
        $pgxResponse->assertSessionHas('success');
        $this->assertDatabaseHas('pharmacogenomic_profiles', [
            'patient_id' => $patient->id,
            'gene_name' => 'CYP2C19',
            'contraindicated_medication' => 'Clopidogrel',
        ]);

        // Báo cáo tai biến truyền máu dừng truyền khẩn cấp
        $reactionResponse = $this->actingAs($receptionist)->post(route('reception.clinical-ai.transfusion-reaction'), [
            'blood_unit_id' => $bloodUnit->id,
            'patient_id' => $patient->id,
            'reaction_type' => 'ALLERGIC_ANAPHYLACTIC',
            'symptoms' => 'Mẩn ngứa toàn thân, tụt huyết áp',
            'reporting_physician' => 'BS. Trực Nội',
        ]);
        $reactionResponse->assertSessionHas('error');
        $this->assertDatabaseHas('transfusion_reaction_reports', [
            'blood_unit_id' => $bloodUnit->id,
            'immediate_transfusion_stopped' => true,
            'blood_unit_quarantined' => true,
        ]);
    }

    /**
     * Test 5: Kỹ Thuật Y Sinh CMMS & Báo Hỏng SOS SLA 5 Phút
     */
    public function test_biomedical_equipment_and_sos_repair(): void
    {
        $receptionist = $this->createReceptionistUser();

        $eqResponse = $this->actingAs($receptionist)->post(route('reception.biomedical-infection.equipment'), [
            'equipment_code' => 'MED-VENT-TEST-' . rand(1000, 9999),
            'name' => 'Máy Thở Cao Cấp Hamilton-G5',
            'model' => 'HG5-Intelligent',
            'risk_class' => 'CLASS_D',
            'department' => 'ICU',
            'next_calibration_due' => Carbon::now()->addYear()->format('Y-m-d'),
        ]);
        $eqResponse->assertSessionHas('success');

        $equipment = BiomedicalEquipment::where('name', 'Máy Thở Cao Cấp Hamilton-G5')->latest()->first();

        // Báo hỏng SOS thiết bị cứu sinh
        $sosResponse = $this->actingAs($receptionist)->post(route('reception.biomedical-infection.repair-sos'), [
            'equipment_id' => $equipment->id,
            'reporter_name' => 'ĐD. Trưởng ICU',
            'urgency' => 'CRITICAL_LIFE_SAVING',
            'fault_description' => 'Mất áp lực cung cấp khí nén',
        ]);
        $sosResponse->assertSessionHas('success');
        $this->assertEquals('under_repair', $equipment->fresh()->operational_status);
        $this->assertDatabaseHas('biomedical_repair_requests', [
            'equipment_id' => $equipment->id,
            'urgency' => 'CRITICAL_LIFE_SAVING',
            'status' => 'pending',
        ]);
    }

    /**
     * Test 6: Giám Sát Nhiễm Khuẩn Bệnh Viện (HAI) & Phê Duyệt Kháng Sinh Dự Trữ (ASP)
     */
    public function test_hai_infection_surveillance_and_asp_antibiotic(): void
    {
        $receptionist = $this->createReceptionistUser();
        $patient = $this->createPatient();

        // Ghi nhận ca viêm phổi thở máy VAP
        $haiResponse = $this->actingAs($receptionist)->post(route('reception.biomedical-infection.hai'), [
            'patient_id' => $patient->id,
            'infection_type' => 'VAP',
            'pathogen_detected' => 'Acinetobacter baumannii MDR',
            'department' => 'Khoa Hồi Sức Tích Cực',
            'containment_action' => 'CONTACT_ISOLATION',
        ]);
        $haiResponse->assertSessionHas('error');
        $this->assertDatabaseHas('hai_surveillances', [
            'patient_id' => $patient->id,
            'infection_type' => 'VAP',
            'containment_action' => 'CONTACT_ISOLATION',
        ]);

        // Duyệt kháng sinh dự trữ Colistin ký kép
        $aspResponse = $this->actingAs($receptionist)->post(route('reception.biomedical-infection.asp'), [
            'patient_id' => $patient->id,
            'antibiotic_name' => 'Colistin 1.000.000 UI',
            'tier_level' => 'TIER_3_RESERVE',
            'prescribing_doctor' => 'BS. Hưng',
            'clinical_indication' => 'Viêm phổi thở máy do Acinetobacter đa kháng',
            'pharmacist_approver' => 'DS. Trang',
            'microbiologist_approver' => 'BS. Tuấn',
        ]);
        $aspResponse->assertSessionHas('success');
        $this->assertDatabaseHas('antimicrobial_approvals', [
            'patient_id' => $patient->id,
            'antibiotic_name' => 'Colistin 1.000.000 UI',
            'approval_status' => 'approved',
        ]);
    }

    /**
     * Test 7: Partograph Chuyển Dạ & Vòng Đeo RFID Chống Bắt Cóc Trẻ Sơ Sinh
     */
    public function test_partograph_and_infant_rfid_security(): void
    {
        $receptionist = $this->createReceptionistUser();
        $mother = $this->createPatient();

        // Ghi nhận Partograph suy tim thai cắt đường hành động (Action Line)
        $partoResponse = $this->actingAs($receptionist)->post(route('reception.maternity-oncology.partograph'), [
            'patient_id' => $mother->id,
            'observation_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'cervical_dilation_cm' => 6.0,
            'fetal_head_station' => 0.0,
            'fetal_heart_rate' => 90, // < 110 bpm -> Suy thai cấp (Action Line)
            'contractions_per_10min' => 4,
            'midwife_name' => 'NHS. Hộ Sinh',
        ]);
        $partoResponse->assertSessionHas('error');
        $this->assertDatabaseHas('electronic_partographs', [
            'patient_id' => $mother->id,
            'crosses_action_line' => true,
        ]);

        // Kích hoạt vòng RFID mẹ-bé
        $rfidResponse = $this->actingAs($receptionist)->post(route('reception.maternity-oncology.infant-tag'), [
            'mother_patient_id' => $mother->id,
            'baby_gender' => 'Bé trai',
            'birth_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'birth_weight_grams' => 3400,
            'mother_band_code' => 'MOTHER-BAND-' . rand(1000, 9999),
            'perimeter_status' => 'INSIDE_WARD',
        ]);
        $rfidResponse->assertSessionHas('success');
        $this->assertDatabaseHas('infant_security_tags', [
            'mother_patient_id' => $mother->id,
            'is_paired' => true,
            'perimeter_status' => 'INSIDE_WARD',
        ]);
    }

    /**
     * Test 8: Hóa Trị BSA, Virtual Tumor Board & Hộp Đen Giờ Vàng Cấp Cứu
     */
    public function test_chemo_bsa_tumor_board_and_golden_hour(): void
    {
        $receptionist = $this->createReceptionistUser();
        $patient = $this->createPatient();

        // Lên chu kỳ hóa trị đủ điều kiện an toàn
        $chemoResponse = $this->actingAs($receptionist)->post(route('reception.maternity-oncology.chemo-cycle'), [
            'patient_id' => $patient->id,
            'regimen_name' => 'FOLFOX6',
            'cycle_number' => 1,
            'total_cycles' => 6,
            'body_surface_area' => 1.72,
            'anc_neutrophil_count' => 2500, // >= 1500
            'platelet_count' => 180000, // >= 100000
            'scheduled_date' => Carbon::today()->format('Y-m-d'),
        ]);
        $chemoResponse->assertSessionHas('success');
        $this->assertDatabaseHas('chemo_treatment_cycles', [
            'patient_id' => $patient->id,
            'is_eligible_to_dispense' => true,
        ]);

        // Biên bản Tumor Board
        $tumorResponse = $this->actingAs($receptionist)->post(route('reception.maternity-oncology.tumor-board'), [
            'patient_id' => $patient->id,
            'cancer_diagnosis' => 'K đại trực tràng Giai đoạn III',
            'meeting_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'consensus_treatment_plan' => 'Phẫu thuật triệt căn + Hóa trị bổ trợ',
            'attendees' => 'BS. Ung Bướu, BS. Ngoại Khoa, BS. Giải Phẫu Bệnh',
        ]);
        $tumorResponse->assertSessionHas('success');
        $this->assertDatabaseHas('virtual_tumor_boards', [
            'patient_id' => $patient->id,
            'status' => 'approved',
        ]);

        // Hộp đen giờ vàng: Đột quỵ Door-to-Needle = 42 phút (< 60 phút -> Đạt chuẩn)
        $goldenResponse = $this->actingAs($receptionist)->post(route('reception.maternity-oncology.golden-hour'), [
            'patient_id' => $patient->id,
            'emergency_type' => 'STROKE_BRAIN',
            'door_arrival_time' => Carbon::now()->subMinutes(42)->format('Y-m-d H:i:s'),
            'intervention_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'attending_team' => 'Stroke Team BV Tâm Anh',
        ]);
        $goldenResponse->assertSessionHas('success');
        $this->assertDatabaseHas('golden_hour_trackers', [
            'patient_id' => $patient->id,
            'emergency_type' => 'STROKE_BRAIN',
            'door_to_needle_minutes' => 42,
            'met_target' => true,
        ]);
    }
}
