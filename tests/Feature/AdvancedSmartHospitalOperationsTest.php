<?php

namespace Tests\Feature;

use App\Models\Ambulance;
use App\Models\AmbulanceMission;
use App\Models\ChronicDiseaseRecall;
use App\Models\CorporateContract;
use App\Models\CorporateEmployee;
use App\Models\DaySurgery;
use App\Models\Doctor;
use App\Models\EmergencyCustody;
use App\Models\InsuranceTransfer;
use App\Models\LostFoundItem;
use App\Models\MedicalIncident;
use App\Models\MedicalInterpreter;
use App\Models\MedicalRecord;
use App\Models\MedicalRecordRequest;
use App\Models\PatientProfile;
use App\Models\PatientWallet;
use App\Models\PrivateInsuranceGuarantee;
use App\Models\SocialWorkCase;
use App\Models\Specialty;
use App\Models\User;
use App\Models\VisitorPass;
use Carbon\Carbon;
use Tests\TestCase;

class AdvancedSmartHospitalOperationsTest extends TestCase
{
    protected User $receptionistUser;
    protected User $doctorUser;
    protected Doctor $doctor;
    protected PatientProfile $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receptionistUser = User::where('role', User::ROLE_RECEPTIONIST)->first()
            ?? User::factory()->create(['role' => User::ROLE_RECEPTIONIST]);

        $this->doctorUser = User::where('role', User::ROLE_DOCTOR)->first()
            ?? User::factory()->create(['role' => User::ROLE_DOCTOR]);

        $this->doctor = Doctor::where('user_id', $this->doctorUser->id)->first()
            ?? Doctor::factory()->create(['user_id' => $this->doctorUser->id]);

        $this->patient = PatientProfile::first() ?? PatientProfile::create([
            'patient_code' => PatientProfile::generatePatientCode(),
            'full_name' => 'Nguyễn Bệnh Nhân Kiểm Thử',
            'dob' => '1990-01-01',
            'gender' => 'male',
            'phone' => '0912345678',
            'insurance_code' => 'DN4010123456789',
        ]);
    }

    public function test_receptionist_can_process_insurance_transfer_and_calculate_rate(): void
    {
        $response = $this->actingAs($this->receptionistUser)->post(route('reception.insurance-transfers.store'), [
            'patient_profile_id' => $this->patient->id,
            'issuing_hospital_code' => '79-012',
            'issuing_hospital_name' => 'Bệnh viện Đa khoa Củ Chi',
            'transfer_type' => 'right_route',
            'icd10_code' => 'I10',
            'icd10_diagnosis' => 'Tăng huyết áp nguyên phát vượt khả năng điều trị tuyến dưới',
            'clinical_summary' => 'Huyết áp không hạ với phác đồ 2 thuốc, chuyển Tâm Anh điều trị chuyên sâu.',
            'valid_from' => Carbon::today()->toDateString(),
            'valid_to' => Carbon::today()->addDays(30)->toDateString(),
            'bhyt_reimbursement_rate' => 100,
        ]);

        $response->assertRedirect();

        $transfer = InsuranceTransfer::where('patient_profile_id', $this->patient->id)->latest('id')->first();
        $this->assertNotNull($transfer);
        $this->assertEquals(100, $transfer->bhyt_reimbursement_rate);
        $this->assertTrue($transfer->is_valid);
    }

    public function test_patient_wallet_deposit_and_refund_lifecycle(): void
    {
        $uniquePatient = PatientProfile::create([
            'patient_code' => PatientProfile::generatePatientCode(),
            'full_name' => 'Bệnh Nhân Ví Test ' . rand(100, 999),
            'dob' => '1995-01-01',
            'gender' => 'female',
            'phone' => '093' . rand(1000000, 9999999),
        ]);

        // 1. Nạp tiền tạm ứng 2.000.000 VNĐ
        $depositResponse = $this->actingAs($this->receptionistUser)->post(
            route('reception.patients.wallets.deposit', $uniquePatient),
            [
                'amount' => 2000000,
                'payment_method' => 'vietqr',
                'notes' => 'Tạm ứng viện phí xét nghiệm ngoại trú',
            ]
        );
        $depositResponse->assertRedirect();

        $wallet = PatientWallet::where('patient_profile_id', $uniquePatient->id)->first();
        $this->assertNotNull($wallet);
        $this->assertEquals(2000000, $wallet->balance);

        // 2. Hoàn ứng 500.000 VNĐ
        $refundResponse = $this->actingAs($this->receptionistUser)->post(
            route('reception.wallets.refund', $wallet),
            [
                'refund_amount' => 500000,
                'notes' => 'Hoàn ứng thừa sau khi thanh toán',
            ]
        );
        $refundResponse->assertRedirect();

        $wallet->refresh();
        $this->assertEquals(1500000, $wallet->balance);
    }

    public function test_private_insurance_tpa_gop_issuance(): void
    {
        $response = $this->actingAs($this->receptionistUser)->post(route('reception.private-insurance.store'), [
            'patient_profile_id' => $this->patient->id,
            'tpa_company' => 'Insmart TPA',
            'card_number' => 'INS-GOLD-998811',
            'coverage_limit' => 50000000,
            'approved_amount' => 12000000,
            'patient_copay_amount' => 500000,
            'vip_level' => 'gold',
            'contact_officer' => 'Chuyên viên Trần Bảo Lan',
            'contact_phone' => '02873001234',
            'remarks' => 'Bảo lãnh viện phí trọn gói nội soi tiêu hóa',
        ]);

        $response->assertRedirect();

        $gop = PrivateInsuranceGuarantee::where('patient_profile_id', $this->patient->id)->latest('id')->first();
        $this->assertNotNull($gop);
        $this->assertEquals('approved', $gop->guarantee_status);
        $this->assertEquals(12000000, $gop->approved_amount);
    }

    public function test_medical_record_request_approval_and_watermark_release(): void
    {
        $response = $this->actingAs($this->receptionistUser)->post(route('reception.record-requests.store'), [
            'patient_profile_id' => $this->patient->id,
            'requester_type' => 'patient_self',
            'requester_name' => $this->patient->full_name,
            'requester_phone' => $this->patient->phone,
            'requester_identity_card' => '001092008899',
            'purpose' => 'Làm thủ tục thanh toán bảo hiểm nhân thọ Prudential',
            'copies_count' => 2,
        ]);

        $response->assertRedirect();

        $requestRecord = MedicalRecordRequest::where('patient_profile_id', $this->patient->id)->latest('id')->first();
        $this->assertNotNull($requestRecord);
        $this->assertEquals('submitted', $requestRecord->status);

        // Duyệt đơn
        $approveResponse = $this->actingAs($this->receptionistUser)->patch(
            route('reception.record-requests.status', $requestRecord),
            ['status' => 'approved']
        );
        $approveResponse->assertRedirect();

        $requestRecord->refresh();
        $this->assertEquals('approved', $requestRecord->status);
    }

    public function test_emergency_custody_and_lost_found_lifecycle(): void
    {
        $sealNumber = 'SEAL-' . rand(10000, 99999);

        // 1. Niêm phong tài sản cấp cứu
        $custodyResponse = $this->actingAs($this->receptionistUser)->post(route('reception.emergency-custody.store'), [
            'patient_profile_id' => $this->patient->id,
            'bag_seal_number' => $sealNumber,
            'item_description' => '01 Ví da màu nâu, 01 điện thoại iPhone 15 Pro Max, 01 dây chuyền kim loại màu vàng',
            'cash_amount' => 5000000,
            'security_user_id' => $this->receptionistUser->id,
        ]);
        $custodyResponse->assertRedirect();

        $custody = EmergencyCustody::where('bag_seal_number', $sealNumber)->first();
        $this->assertNotNull($custody);
        $this->assertEquals('sealed', $custody->status);

        // 2. Bàn giao trả người nhà
        $returnResponse = $this->actingAs($this->receptionistUser)->patch(
            route('reception.emergency-custody.return', $custody),
            [
                'return_recipient_name' => 'Nguyễn Thị Hoa',
                'return_recipient_id_card' => '001095001234',
                'return_relationship' => 'Vợ của người bệnh',
            ]
        );
        $returnResponse->assertRedirect();

        $custody->refresh();
        $this->assertEquals('returned', $custody->status);
    }

    public function test_visitor_pass_blocks_more_than_two_visitors_for_infection_control(): void
    {
        // Cấp người thứ 1
        $this->actingAs($this->receptionistUser)->post(route('reception.visitor-passes.store'), [
            'patient_profile_id' => $this->patient->id,
            'visitor_name' => 'Khách Thăm Số 1',
            'visitor_phone' => '0981111111',
            'relationship' => 'Bạn bè',
            'target_department' => 'Khoa Hồi Sức Tích Cực (ICU)',
            'valid_hours' => 2,
            'infection_screening_passed' => 1,
        ]);

        // Cấp người thứ 2
        $this->actingAs($this->receptionistUser)->post(route('reception.visitor-passes.store'), [
            'patient_profile_id' => $this->patient->id,
            'visitor_name' => 'Khách Thăm Số 2',
            'visitor_phone' => '0982222222',
            'relationship' => 'Đồng nghiệp',
            'target_department' => 'Khoa Hồi Sức Tích Cực (ICU)',
            'valid_hours' => 2,
            'infection_screening_passed' => 1,
        ]);

        // Thử cấp người thứ 3 (Phải bị chặn theo quy chuẩn kiểm soát nhiễm khuẩn JCI)
        $response3 = $this->actingAs($this->receptionistUser)->post(route('reception.visitor-passes.store'), [
            'patient_profile_id' => $this->patient->id,
            'visitor_name' => 'Khách Thăm Số 3 (Vượt hạn mức)',
            'visitor_phone' => '0983333333',
            'relationship' => 'Người quen',
            'target_department' => 'Khoa Hồi Sức Tích Cực (ICU)',
            'valid_hours' => 2,
            'infection_screening_passed' => 1,
        ]);

        $response3->assertSessionHas('error');
        $this->assertStringContainsString('hiện đã có 2 người đang trong phòng thăm', session('error'));
    }

    public function test_medical_incident_near_miss_reporting_and_rca(): void
    {
        $response = $this->actingAs($this->receptionistUser)->post(route('reception.medical-incidents.store'), [
            'reporter_type' => 'named',
            'location' => 'Quầy Tiếp Nhận Số 2 Sảnh A',
            'occurred_at' => Carbon::now()->toDateTimeString(),
            'severity_level' => 'NC0',
            'incident_category' => 'Nhầm lẫn định danh người bệnh (Trùng tên, nhầm số hồ sơ)',
            'description' => 'Hai bệnh nhân cùng tên Nguyễn Văn Nam vào khám cùng lúc, lễ tân kịp đối chiếu ngày sinh và CCCD nên đã chặn đứng nguy cơ dán nhầm hồ sơ.',
            'immediate_action' => 'Đã yêu cầu đọc lại số CCCD và in vòng tay nhận diện mã vạch tức thì.',
        ]);

        $response->assertRedirect();

        $incident = MedicalIncident::where('severity_level', 'NC0')->latest('id')->first();
        $this->assertNotNull($incident);
        $this->assertEquals('reported', $incident->status);
    }

    public function test_social_work_aid_and_medical_interpreter(): void
    {
        // 1. Hồ sơ xin trợ cấp
        $responseAid = $this->actingAs($this->receptionistUser)->post(route('reception.social-work.store'), [
            'patient_profile_id' => $this->patient->id,
            'difficulty_type' => 'Hộ nghèo / Cận nghèo có chứng nhận',
            'proposed_discount_amount' => 4500000,
            'funding_source' => 'Quỹ Từ Thiện Tâm Anh Heart',
            'situation_assessment' => 'Bệnh nhân có hoàn cảnh khó khăn neo đơn, mắc bệnh tim cần hỗ trợ tiền thuốc.',
        ]);
        $responseAid->assertRedirect();

        $case = SocialWorkCase::where('patient_profile_id', $this->patient->id)->latest('id')->first();
        $this->assertNotNull($case);
        $this->assertEquals(4500000, $case->proposed_discount_amount);

        // 2. Thông dịch viên y khoa
        $responseInter = $this->actingAs($this->receptionistUser)->post(route('reception.interpreters.store'), [
            'patient_profile_id' => $this->patient->id,
            'language' => 'Tiếng Anh (English)',
            'interpreter_name' => 'Nguyễn Minh Thảo',
            'interpreter_phone' => '0988776655',
            'notes' => 'Hỗ trợ dịch phòng khám Tim mạch',
        ]);
        $responseInter->assertRedirect();

        $interpreter = MedicalInterpreter::where('patient_profile_id', $this->patient->id)->latest('id')->first();
        $this->assertNotNull($interpreter);
        $this->assertEquals('in_progress', $interpreter->status);
    }

    public function test_ambulance_mission_dispatch_and_mileage_billing(): void
    {
        $ambulance = Ambulance::first() ?? Ambulance::create([
            'license_plate' => '29A-TEST-115',
            'ambulance_type' => 'mobile_icu',
            'driver_name' => 'Tài xế Test',
            'driver_phone' => '0912115115',
            'status' => 'available',
        ]);

        $response = $this->actingAs($this->receptionistUser)->post(route('reception.ambulances.store'), [
            'ambulance_id' => $ambulance->id,
            'patient_profile_id' => $this->patient->id,
            'caller_name' => 'Người gọi khẩn cấp',
            'caller_phone' => '0912888999',
            'pickup_address' => 'Số 50 Hàng Bài, Hoàn Kiếm, Hà Nội',
            'destination_address' => 'Khoa Cấp Cứu - Bệnh Viện Tâm Anh',
            'mission_type' => 'home_pickup',
            'distance_km' => 20,
            'doctor_in_charge' => 'BS. Cấp Cứu A',
            'nurse_in_charge' => 'ĐD. Hồi Sức B',
        ]);

        $response->assertRedirect();

        $mission = AmbulanceMission::where('ambulance_id', $ambulance->id)->latest('id')->first();
        $this->assertNotNull($mission);
        $this->assertEquals('dispatched', $mission->status);
        $this->assertGreaterThan(0, $mission->total_cost);
    }

    public function test_corporate_health_screening_and_tt32_classification(): void
    {
        // 1. Tạo hợp đồng khám đoàn
        $contractResponse = $this->actingAs($this->receptionistUser)->post(route('reception.corporate-screening.store'), [
            'company_name' => 'Tập Đoàn Viettel Test',
            'company_tax_code' => '0100109106',
            'contact_person' => 'Nguyễn Thu Trang',
            'contact_phone' => '0988123456',
            'checkup_start_date' => Carbon::today()->toDateString(),
            'checkup_end_date' => Carbon::today()->addDays(14)->toDateString(),
            'total_employees' => 100,
            'package_name' => 'Gói Khám Tiêu Chuẩn Thông Tư 32/2023/TT-BYT',
            'cost_per_person' => 1500000,
        ]);
        $contractResponse->assertRedirect();

        $contract = CorporateContract::where('company_name', 'Tập Đoàn Viettel Test')->first();
        $this->assertNotNull($contract);

        // 2. Thêm nhân viên vào đoàn
        $empResponse = $this->actingAs($this->receptionistUser)->post(
            route('reception.corporate-screening.employees', $contract),
            [
                'employee_code' => 'VT-EMP-001',
                'full_name' => 'Đoàn Văn Hậu',
                'dob' => '1995-04-19',
                'gender' => 'male',
                'phone' => '0987654321',
                'department' => 'Kỹ thuật phần mềm',
            ]
        );
        $empResponse->assertRedirect();

        $employee = CorporateEmployee::where('employee_code', 'VT-EMP-001')->first();
        $this->assertNotNull($employee);

        // 3. Kết luận phân loại sức khỏe Loại I
        $evalResponse = $this->actingAs($this->receptionistUser)->patch(
            route('reception.corporate-screening.screening', $employee),
            [
                'screening_status' => 'completed',
                'health_classification' => 'Loại I',
                'conclusion' => 'Đủ sức khỏe làm việc bình thường.',
            ]
        );
        $evalResponse->assertRedirect();

        $employee->refresh();
        $this->assertEquals('completed', $employee->screening_status);
        $this->assertEquals('Loại I', $employee->health_classification);
    }

    public function test_day_surgery_pre_op_who_safety_checklist(): void
    {
        // 1. Đặt lịch phẫu thuật trong ngày
        $response = $this->actingAs($this->receptionistUser)->post(route('reception.day-surgeries.store'), [
            'patient_profile_id' => $this->patient->id,
            'procedure_name' => 'Phẫu thuật Phaco tán nhuyễn thể thủy tinh',
            'surgeon_doctor_id' => $this->doctor->id,
            'scheduled_at' => Carbon::tomorrow()->setHour(8)->toDateTimeString(),
            'operating_room' => 'Phòng Mổ Ngoại Trú OR-01',
            'fasting_hours' => 8,
            'pre_op_notes' => 'Đã xét nghiệm đông máu và ECG bình thường.',
        ]);
        $response->assertRedirect();

        $surgery = DaySurgery::where('patient_profile_id', $this->patient->id)->latest('id')->first();
        $this->assertNotNull($surgery);
        $this->assertEquals('scheduled', $surgery->status);

        // 2. Thực hiện WHO Checklist
        $checkResponse = $this->actingAs($this->receptionistUser)->patch(
            route('reception.day-surgeries.checklist', $surgery),
            [
                'checklist_passed' => 1,
                'pre_op_notes' => 'Đạt tiêu chuẩn an toàn phẫu thuật WHO',
            ]
        );
        $checkResponse->assertRedirect();

        $surgery->refresh();
        $this->assertTrue($surgery->checklist_passed);
        $this->assertEquals('pre_op_ready', $surgery->status);
    }

    public function test_chronic_disease_recall_auto_due_date_calculation(): void
    {
        $lastVisit = Carbon::today()->subDays(5);

        $response = $this->actingAs($this->receptionistUser)->post(route('reception.chronic-recalls.store'), [
            'patient_profile_id' => $this->patient->id,
            'chronic_condition' => 'Tăng Huyết Áp Nguyên Phát (I10)',
            'last_visit_date' => $lastVisit->toDateString(),
            'prescription_days' => 28,
            'reminder_channel' => 'zalo_zns',
            'notes' => 'Theo dõi huyết áp mục tiêu dưới 130/80 mmHg',
        ]);

        $response->assertRedirect();

        $recall = ChronicDiseaseRecall::where('patient_profile_id', $this->patient->id)->latest('id')->first();
        $this->assertNotNull($recall);
        $this->assertEquals($lastVisit->copy()->addDays(28)->toDateString(), $recall->due_recall_date->toDateString());
        $this->assertEquals('scheduled', $recall->status);

        // Gửi tin nhắn nhắc tái khám
        $remindResponse = $this->actingAs($this->receptionistUser)->post(
            route('reception.chronic-recalls.remind', $recall)
        );
        $remindResponse->assertRedirect();

        $recall->refresh();
        $this->assertEquals('reminder_sent', $recall->status);
        $this->assertNotNull($recall->reminder_sent_at);
    }
}
