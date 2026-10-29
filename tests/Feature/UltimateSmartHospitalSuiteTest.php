<?php

namespace Tests\Feature;

use App\Models\ColdChainStorage;
use App\Models\CssdInstrumentTray;
use App\Models\HospitalBed;
use App\Models\HospitalRoom;
use App\Models\NarcoticEmergencyBox;
use App\Models\PatientProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UltimateSmartHospitalSuiteTest extends TestCase
{
    private User $receptionistUser;
    private PatientProfile $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receptionistUser = User::firstOrCreate(
            ['email' => 'receptionist_ultimate@tamanh.com'],
            [
                'name' => 'Lễ Tân Test Ultimate',
                'password' => bcrypt('password123'),
                'role' => User::ROLE_RECEPTIONIST,
            ]
        );

        $this->patient = PatientProfile::firstOrCreate(
            ['patient_code' => 'BN-TEST-ULTIMATE'],
            [
                'full_name' => 'Bệnh Nhân Thử Nghiệm Ultimate',
                'phone' => '0909' . rand(100000, 999999),
                'dob' => '1985-05-15',
                'gender' => 'male',
                'blood_type' => 'O Rh(+)',
                'address' => 'TP. Hồ Chí Minh',
            ]
        );
    }

    public function test_news2_early_warning_and_rrt_activation(): void
    {
        // 1. Bình thường: Điểm 0 (Low risk)
        $calcNormal = \App\Models\News2VitalAlert::calculateScore(16, 98, false, 120, 72, 'alert', 37.0);
        $this->assertEquals(0, $calcNormal['score']);
        $this->assertEquals('low', $calcNormal['risk_level']);
        $this->assertFalse($calcNormal['trigger_rrt']);

        // 2. Nguy kịch: Điểm >= 7 (High Critical -> Trigger RRT)
        $response = $this->actingAs($this->receptionistUser)->post('/reception/news2-rrt', [
            'patient_profile_id' => $this->patient->id,
            'respiratory_rate' => 28, // +3
            'oxygen_saturation' => 88, // +3
            'supplemental_oxygen' => 1, // +2
            'systolic_bp' => 85, // +3
            'pulse_rate' => 135, // +3
            'consciousness_level' => 'pain', // +3
            'temperature' => 39.5, // +2
            'ward_location' => 'Khoa Cấp Cứu Giường 01',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('news2_vital_alerts', [
            'patient_profile_id' => $this->patient->id,
            'risk_level' => 'high_critical',
            'rrt_triggered' => true,
        ]);
        $this->assertDatabaseHas('rrt_activations', [
            'patient_profile_id' => $this->patient->id,
            'status' => 'dispatched',
        ]);
    }

    public function test_clinical_nursing_risk_assessments_braden_and_morse(): void
    {
        // Test Morse Fall Scale (Nguy cơ ngã >= 45)
        $responseMorse = $this->actingAs($this->receptionistUser)->post('/reception/risk-assessments', [
            'patient_profile_id' => $this->patient->id,
            'assessment_type' => 'morse_fall',
            'score' => 65,
            'details' => ['gait' => 'weak', 'history_falls' => 'yes'],
            'preventive_actions' => ['Đeo vòng tay cảnh báo vàng', 'Kéo thanh chắn giường'],
        ]);
        $responseMorse->assertSessionHas('success');
        $this->assertDatabaseHas('clinical_risk_assessments', [
            'patient_profile_id' => $this->patient->id,
            'assessment_type' => 'morse_fall',
            'risk_category' => 'high',
        ]);

        // Test Braden Scale (Loét tì đè <= 12 là High)
        $responseBraden = $this->actingAs($this->receptionistUser)->post('/reception/risk-assessments', [
            'patient_profile_id' => $this->patient->id,
            'assessment_type' => 'braden_scale',
            'score' => 10,
            'details' => ['mobility' => 'completely_immobile'],
            'preventive_actions' => ['Nằm đệm hơi', 'Lăn trở 2h'],
        ]);
        $responseBraden->assertSessionHas('success');
        $this->assertDatabaseHas('clinical_risk_assessments', [
            'patient_profile_id' => $this->patient->id,
            'assessment_type' => 'braden_scale',
            'risk_category' => 'high',
        ]);
    }

    public function test_narcotic_emergency_box_dispense_and_disposal(): void
    {
        $box = NarcoticEmergencyBox::create([
            'box_code' => 'BOX-TEST-' . rand(10000, 99999),
            'ward_name' => 'Tủ Trực Cấp Cứu',
            'medication_name' => 'Morphin hydroclorid 10mg/ml',
            'concentration' => '10mg/1ml',
            'unit' => 'ampoule',
            'quantity_stock' => 10,
            'min_threshold' => 3,
        ]);

        // Xuất thuốc có ký kép
        $response = $this->actingAs($this->receptionistUser)->post('/reception/narcotics/dispense', [
            'box_id' => $box->id,
            'patient_profile_id' => $this->patient->id,
            'doctor_name' => 'BS. CKII. Nguyễn Minh Trí',
            'nurse_name' => 'ĐD. Lê Thị Mai',
            'witness_name' => 'ĐD. Hoàng Văn An',
            'quantity_dispensed' => 2,
            'indication' => 'Giảm đau sau mổ bắc cầu mạch vành',
        ]);

        $response->assertSessionHas('success');
        $box->refresh();
        $this->assertEquals(8, $box->quantity_stock);

        $log = \App\Models\NarcoticDispenseLog::where('box_id', $box->id)->latest()->first();
        $this->assertNotNull($log);
        $this->assertFalse($log->disposal_verified);

        // Hủy vỏ ống nộp lại
        $responseDisposal = $this->actingAs($this->receptionistUser)->patch("/reception/narcotics/verify-disposal/{$log->id}", [
            'empty_ampoules_returned' => 2,
        ]);
        $responseDisposal->assertSessionHas('success');
        $log->refresh();
        $this->assertTrue($log->disposal_verified);
        $this->assertEquals(2, $log->empty_ampoules_returned);
    }

    public function test_occupational_exposure_surveillance_and_pep(): void
    {
        $response = $this->actingAs($this->receptionistUser)->post('/reception/occupational-exposure', [
            'employee_name' => 'ĐD. Phạm Thị Thủy',
            'employee_role' => 'nurse',
            'department' => 'Khoa Hồi Sức Tích Cực ICU',
            'exposure_type' => 'needlestick',
            'source_patient_id' => $this->patient->id,
            'source_status' => 'hbv_pos',
            'pep_started' => 1,
            'pep_start_hours_elapsed' => 2,
            'incident_description' => 'Kim tiêm lấy khí máu đâm thấu găng tay cao su',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('occupational_exposures', [
            'employee_name' => 'ĐD. Phạm Thị Thủy',
            'exposure_type' => 'needlestick',
            'pep_started' => true,
            'status' => 'monitoring',
        ]);
    }

    public function test_cold_chain_continuous_monitoring_and_safety_lock(): void
    {
        $storage = ColdChainStorage::firstOrCreate(
            ['storage_code' => 'CC-TEST-01'],
            [
                'storage_name' => 'Tủ Vắc-Xin Test GSP',
                'storage_type' => 'vaccine_fridge',
                'temp_min' => 2.0,
                'temp_max' => 8.0,
                'current_temp' => 4.5,
                'status' => 'normal',
                'is_locked' => false,
            ]
        );

        // 1. Cập nhật nhiệt độ bình thường
        $this->actingAs($this->receptionistUser)->post('/reception/cold-chain/telemetry', [
            'storage_id' => $storage->id,
            'temperature' => 5.2,
            'humidity' => 60.0,
        ])->assertSessionHas('success');

        $storage->refresh();
        $this->assertFalse($storage->is_locked);
        $this->assertEquals('normal', $storage->status);

        // 2. Cập nhật nhiệt độ vượt ngưỡng an toàn (>8.0C) -> Kích hoạt khóa an toàn
        $this->actingAs($this->receptionistUser)->post('/reception/cold-chain/telemetry', [
            'storage_id' => $storage->id,
            'temperature' => 12.5,
            'humidity' => 70.0,
        ])->assertSessionHas('warning');

        $storage->refresh();
        $this->assertTrue($storage->is_locked);
        $this->assertEquals('warning_high', $storage->status);
    }

    public function test_cssd_surgical_tray_traceability_and_autoclave(): void
    {
        $tray = CssdInstrumentTray::firstOrCreate(
            ['tray_code' => 'TRAY-TEST-CARDIO'],
            [
                'tray_name' => 'Khay Mổ Tim Hở Test',
                'department' => 'Phòng Mổ #01',
                'total_instruments_count' => 45,
                'instruments_manifest' => ['kẹp phẫu thuật', 'kéo Metzenbaum', 'banh ngực'],
                'status' => 'contaminated',
            ]
        );

        $response = $this->actingAs($this->receptionistUser)->post('/reception/cssd/run-cycle', [
            'machine_name' => 'Nồi Hấp Autoclave #01',
            'method' => 'steam_autoclave_134c',
            'temp_celsius' => 134,
            'pressure_bar' => 2.1,
            'chemical_indicator_pass' => 1,
            'biological_indicator_pass' => 1,
            'operator_name' => 'KTV. Trần Văn Đức',
            'tray_ids' => [$tray->id],
        ]);

        $response->assertSessionHas('success');
        $tray->refresh();
        $this->assertEquals('sterilized', $tray->status);
        $this->assertNotNull($tray->expiry_date);
    }

    public function test_hemodialysis_shift_management_and_dialyzer_reuse(): void
    {
        $response = $this->actingAs($this->receptionistUser)->post('/reception/hemodialysis', [
            'patient_profile_id' => $this->patient->id,
            'session_date' => date('Y-m-d'),
            'shift' => 'morning',
            'machine_number' => 'HD-TEST-02',
            'dry_weight_kg' => 52.0,
            'pre_weight_kg' => 54.5, // Dư 2.5kg = 2500ml
            'dialyzer_barcode' => 'DIAL-TEST-99',
            'dialyzer_reuse_count' => 2,
            'nurse_in_charge' => 'ĐD. Lê Lan',
        ]);

        $response->assertSessionHas('success');

        $session = \App\Models\HemodialysisSession::where('patient_profile_id', $this->patient->id)->latest()->first();
        $this->assertNotNull($session);
        $this->assertEquals(2500, $session->uf_target_ml);
        $this->assertEquals('ongoing', $session->status);

        // Kết thúc ca lọc máu
        $responseComplete = $this->actingAs($this->receptionistUser)->patch("/reception/hemodialysis/{$session->id}/complete", [
            'post_weight_kg' => 52.1,
            'uf_actual_ml' => 2450,
        ]);

        $responseComplete->assertSessionHas('success');
        $session->refresh();
        $this->assertEquals('completed', $session->status);
        $this->assertEquals(2450, $session->uf_actual_ml);
    }

    public function test_smart_bed_board_turnaround_and_cleaning_lifecycle(): void
    {
        $room = HospitalRoom::firstOrCreate(
            ['room_number' => 'P.TEST-301'],
            [
                'department' => 'Khoa Hồi Sức Tích Cực',
                'room_type' => 'single_vip',
                'floor' => 3,
            ]
        );

        $bed = HospitalBed::firstOrCreate(
            ['bed_code' => 'BED-TEST-301-A'],
            [
                'room_id' => $room->id,
                'status' => 'available_clean',
            ]
        );

        // 1. Xếp bệnh nhân vào
        $this->actingAs($this->receptionistUser)->post("/reception/bed-board/assign/{$bed->id}", [
            'patient_profile_id' => $this->patient->id,
        ])->assertSessionHas('success');

        $bed->refresh();
        $this->assertEquals('occupied', $bed->status);
        $this->assertEquals($this->patient->id, $bed->current_patient_id);

        // 2. Xuất viện / rời giường -> Trở thành dirty_pending
        $this->actingAs($this->receptionistUser)->post("/reception/bed-board/discharge/{$bed->id}")
            ->assertSessionHas('success');

        $bed->refresh();
        $this->assertEquals('dirty_pending', $bed->status);
        $this->assertNull($bed->current_patient_id);

        // 3. Khử trùng giường -> Trở lại available_clean
        $this->actingAs($this->receptionistUser)->post("/reception/bed-board/clean/{$bed->id}", [
            'cleaner_name' => 'NV. Hộ lý Nguyễn Thị Mai',
            'disinfectant_chemical' => 'Cloramin B 0.5%',
        ])->assertSessionHas('success');

        $bed->refresh();
        $this->assertEquals('available_clean', $bed->status);
        $this->assertNotNull($bed->last_disinfected_at);
    }

    public function test_indoor_wayfinding_turn_by_turn_navigation(): void
    {
        $response = $this->actingAs($this->receptionistUser)->post('/reception/wayfinding', [
            'start_location' => 'Kiosk Sảnh Cửa 1',
            'destination_department' => 'Khoa X-Quang Tầng 2',
            'floor_level' => 2,
            'estimated_walk_minutes' => 4,
            'step_directions' => ['Đi thẳng 30m', 'Lên thang máy B', 'Rẽ phải tới phòng X201'],
            'accessibility_friendly' => 'wheelchair_accessible',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('hospital_wayfindings', [
            'destination_department' => 'Khoa X-Quang Tầng 2',
            'floor_level' => 2,
        ]);
    }

    public function test_pre_visit_health_screener_and_tele_triage(): void
    {
        // 1. Khai báo có cờ đỏ cấp cứu
        $response = $this->actingAs($this->receptionistUser)->post('/reception/pre-visit', [
            'appointment_code' => 'AP-TEST-PREVISIT-99',
            'patient_name' => 'Trần Văn Mạnh',
            'patient_phone' => '0938112233',
            'symptoms_declared' => ['dau_nguc_du_doi', 'kho_tho_cap'],
            'home_medications' => 'Nitroglycerin',
            'known_allergies' => 'Penicillin',
        ]);

        $response->assertSessionHas('warning'); // Gắn cờ cảnh báo đỏ
        $this->assertDatabaseHas('pre_visit_screenings', [
            'appointment_code' => 'AP-TEST-PREVISIT-99',
            'risk_stratification' => 'urgent_red_flag',
            'synced_to_emr' => true,
        ]);
    }

    public function test_realtime_insurance_claim_scrubbing_and_4210_xml(): void
    {
        // 1. Vi phạm: Chỉ định MRI não nhưng mã ICD-10 là tăng huyết áp I10
        $response = $this->actingAs($this->receptionistUser)->post('/reception/claim-scrubber/scrub', [
            'patient_profile_id' => $this->patient->id,
            'icd10_code' => 'I10',
            'icd10_name' => 'Tăng huyết áp nguyên phát',
            'claim_amount' => 3200000,
            'designated_tests' => ['mri_brain'],
        ]);

        $response->assertSessionHas('warning');
        $this->assertDatabaseHas('insurance_claim_scrubs', [
            'patient_profile_id' => $this->patient->id,
            'icd10_code' => 'I10',
            'scrub_status' => 'rejected_high_risk',
            'xml_4210_exported' => false,
        ]);

        // 2. Hợp lệ hoàn toàn (Clean Claim) -> Tự động xuất XML 4210
        $responseClean = $this->actingAs($this->receptionistUser)->post('/reception/claim-scrubber/scrub', [
            'patient_profile_id' => $this->patient->id,
            'icd10_code' => 'E11',
            'icd10_name' => 'Đái tháo đường type 2',
            'claim_amount' => 450000,
            'designated_tests' => ['glucose_blood'],
        ]);

        $responseClean->assertSessionHas('success');
        $this->assertDatabaseHas('insurance_claim_scrubs', [
            'patient_profile_id' => $this->patient->id,
            'icd10_code' => 'E11',
            'scrub_status' => 'clean',
            'xml_4210_exported' => true,
        ]);
    }

    public function test_organ_donation_registry_and_living_will_dnr(): void
    {
        // 1. Đăng ký hiến tạng nhân đạo
        $responseOrgan = $this->actingAs($this->receptionistUser)->post('/reception/living-will', [
            'patient_profile_id' => $this->patient->id,
            'record_type' => 'organ_donation',
            'organs_pledged' => ['Tim', 'Gan', '2 Thận', 'Giác Mạc Mắt'],
            'representative_name' => 'Nguyễn Thị Hoa',
            'representative_phone' => '0988776655',
            'representative_relationship' => 'Vợ',
            'legal_consent_signed' => 1,
        ]);
        $responseOrgan->assertSessionHas('success');
        $this->assertDatabaseHas('organ_donations_and_living_wills', [
            'patient_profile_id' => $this->patient->id,
            'record_type' => 'organ_donation',
            'status' => 'active_registered',
        ]);

        // 2. Di chúc y tế / Chỉ thị không hồi sức DNR
        $responseDNR = $this->actingAs($this->receptionistUser)->post('/reception/living-will', [
            'patient_profile_id' => $this->patient->id,
            'record_type' => 'living_will_dnr',
            'dnr_directives' => ['Không ép tim (No CPR)', 'Chăm sóc giảm nhẹ'],
            'representative_name' => 'Nguyễn Văn Hùng',
            'representative_phone' => '0988112233',
            'representative_relationship' => 'Con trai',
            'legal_consent_signed' => 1,
        ]);
        $responseDNR->assertSessionHas('success');
        $this->assertDatabaseHas('organ_donations_and_living_wills', [
            'patient_profile_id' => $this->patient->id,
            'record_type' => 'living_will_dnr',
            'status' => 'active_registered',
        ]);
    }
}
