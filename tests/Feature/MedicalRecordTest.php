<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Attachment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\MedicalRecord;
use App\Models\PatientProfile;
use App\Models\Specialty;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MedicalRecordTest extends TestCase
{
    protected User $adminUser;
    protected User $doctorUser;
    protected Doctor $doctor;
    protected User $patientUserA;
    protected PatientProfile $patientProfileA;
    protected User $patientUserB;
    protected PatientProfile $patientProfileB;
    protected Specialty $specialty;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Tạo hoặc lấy Admin
        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin_test_bao@tamanh.vn'],
            [
                'name' => 'Admin Test Bao',
                'password' => bcrypt('123456'),
                'role' => User::ROLE_ADMIN,
            ]
        );

        // 2. Chuyên khoa
        $this->specialty = Specialty::firstOrCreate(
            ['slug' => 'chuyen-khoa-noi-bao'],
            [
                'name' => 'Chuyên Khoa Nội Test',
                'description' => 'Mô tả chuyên khoa test',
                'is_active' => true,
            ]
        );

        // 3. Bác sĩ
        $this->doctorUser = User::firstOrCreate(
            ['email' => 'doctor_test_bao@tamanh.vn'],
            [
                'name' => 'BS. Võ Lê Gia Bảo Test',
                'password' => bcrypt('123456'),
                'role' => User::ROLE_DOCTOR,
            ]
        );

        $this->doctor = Doctor::firstOrCreate(
            ['user_id' => $this->doctorUser->id],
            [
                'specialty_id' => $this->specialty->id,
                'qualification' => 'ThS.BS',
                'room_number' => '402',
                'consultation_fee' => 300000,
                'is_available' => true,
            ]
        );

        // 4. Bệnh nhân A (Chính chủ)
        $this->patientUserA = User::firstOrCreate(
            ['email' => 'patient_a_test@gmail.com'],
            [
                'name' => 'Bệnh nhân Nguyễn Văn A',
                'password' => bcrypt('123456'),
                'role' => User::ROLE_PATIENT,
            ]
        );

        $this->patientProfileA = PatientProfile::firstOrCreate(
            ['user_id' => $this->patientUserA->id],
            [
                'patient_code' => 'BN-TEST-0001',
                'full_name' => 'Nguyễn Văn A',
                'dob' => '1995-05-20',
                'gender' => 'male',
                'phone' => '0901111222',
            ]
        );

        // 5. Bệnh nhân B (Người ngoài - dùng để test bảo mật 403)
        $this->patientUserB = User::firstOrCreate(
            ['email' => 'patient_b_test@gmail.com'],
            [
                'name' => 'Bệnh nhân Trần Thị B',
                'password' => bcrypt('123456'),
                'role' => User::ROLE_PATIENT,
            ]
        );

        $this->patientProfileB = PatientProfile::firstOrCreate(
            ['user_id' => $this->patientUserB->id],
            [
                'patient_code' => 'BN-TEST-0002',
                'full_name' => 'Trần Thị B',
                'dob' => '1998-10-12',
                'gender' => 'female',
                'phone' => '0903333444',
            ]
        );
    }

    /**
     * Test Bác sĩ xem hàng đợi phòng khám hôm nay.
     */
    public function test_doctor_can_view_today_queue(): void
    {
        $response = $this->actingAs($this->doctorUser)
            ->get(route('doctor.queue'));

        $response->assertStatus(200);
        $response->assertSee('Hàng Đợi Khám Bệnh Hôm Nay');
        $response->assertSee($this->doctor->room_number);
    }

    /**
     * Test Bác sĩ mở màn hình khám cho ca hẹn đã check-in (confirmed).
     */
    public function test_doctor_can_open_examination_room_for_confirmed_appointment(): void
    {
        $appointment = Appointment::create([
            'appointment_code' => 'AP-TEST-001',
            'patient_profile_id' => $this->patientProfileA->id,
            'doctor_id' => $this->doctor->id,
            'specialty_id' => $this->specialty->id,
            'appointment_date' => Carbon::today(),
            'shift' => 'morning',
            'symptom_description' => 'Sốt nhẹ, rát cổ họng',
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($this->doctorUser)
            ->get(route('doctor.examine', $appointment->id));

        $response->assertStatus(200);
        $response->assertSee('Bàn Khám Bệnh & Lập Bệnh Án');
        $response->assertSee($this->patientProfileA->full_name);
        $response->assertSee('Sốt nhẹ, rát cổ họng');
    }

    /**
     * Test Bác sĩ lưu bệnh án và kê đơn thuốc động thành công.
     * Kiểm tra chuyển trạng thái sang completed và sinh mã MR-YYYYMMDD-XXXX.
     */
    public function test_doctor_can_store_medical_record_and_prescriptions(): void
    {
        $appointment = Appointment::create([
            'appointment_code' => 'AP-TEST-002',
            'patient_profile_id' => $this->patientProfileA->id,
            'doctor_id' => $this->doctor->id,
            'specialty_id' => $this->specialty->id,
            'appointment_date' => Carbon::today(),
            'shift' => 'morning',
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        $postData = [
            'appointment_id' => $appointment->id,
            'symptoms' => 'Họng đỏ xung huyết, amidan sưng nhẹ, không sốt cao',
            'diagnosis' => 'Viêm họng cấp tính J02.9',
            'clinical_notes' => 'HA: 120/80 mmHg, SpO2: 99%',
            'treatment_plan' => 'Súc miệng nước muối sinh lý, uống nhiều nước ấm',
            'follow_up_date' => Carbon::today()->addDays(7)->toDateString(),
            'prescriptions' => [
                [
                    'medication_name' => 'Amoxicillin 500mg',
                    'dosage' => '1 viên/lần',
                    'frequency' => '2 lần/ngày sau ăn',
                    'duration_days' => 5,
                    'instructions' => 'Uống sáng - chiều sau ăn',
                ],
                [
                    'medication_name' => 'Paracetamol 500mg',
                    'dosage' => '1 viên/lần',
                    'frequency' => 'Khi sốt trên 38.5 độ',
                    'duration_days' => 3,
                    'instructions' => 'Cách nhau 4-6 tiếng',
                ],
            ],
        ];

        $response = $this->actingAs($this->doctorUser)
            ->post(route('doctor.records.store'), $postData);

        // Kiểm tra redirect về trang xem chi tiết bệnh án
        $response->assertStatus(302);

        // Kiểm tra cơ sở dữ liệu
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => Appointment::STATUS_COMPLETED,
        ]);

        $this->assertDatabaseHas('medical_records', [
            'appointment_id' => $appointment->id,
            'patient_profile_id' => $this->patientProfileA->id,
            'doctor_id' => $this->doctor->id,
            'diagnosis' => 'Viêm họng cấp tính J02.9',
        ]);

        $record = MedicalRecord::where('appointment_id', $appointment->id)->first();
        $this->assertNotNull($record);
        $this->assertStringStartsWith('MR-', $record->record_code);

        // Kiểm tra đơn thuốc đã tạo
        $this->assertDatabaseHas('prescriptions', [
            'medical_record_id' => $record->id,
            'medication_name' => 'Amoxicillin 500mg',
        ]);

        $this->assertDatabaseHas('prescriptions', [
            'medical_record_id' => $record->id,
            'medication_name' => 'Paracetamol 500mg',
        ]);
    }

    /**
     * Test tải lên file tài liệu y tế bảo mật và lưu vào storage local riêng tư.
     */
    public function test_doctor_can_upload_secure_medical_attachment(): void
    {
        Storage::fake('local');

        $appointment = Appointment::create([
            'appointment_code' => 'AP-TEST-003',
            'patient_profile_id' => $this->patientProfileA->id,
            'doctor_id' => $this->doctor->id,
            'specialty_id' => $this->specialty->id,
            'appointment_date' => Carbon::today(),
            'shift' => 'morning',
            'status' => Appointment::STATUS_COMPLETED,
        ]);

        $record = MedicalRecord::create([
            'record_code' => MedicalRecord::generateRecordCode(),
            'appointment_id' => $appointment->id,
            'patient_profile_id' => $this->patientProfileA->id,
            'doctor_id' => $this->doctor->id,
            'symptoms' => 'Triệu chứng kiểm thử file upload',
            'diagnosis' => 'Chẩn đoán kiểm thử upload',
        ]);

        $fakePdf = UploadedFile::fake()->create('ket_qua_xet_nghiem.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->doctorUser)
            ->post(route('records.attachments.upload', $record->id), [
                'file' => $fakePdf,
                'file_type' => 'lab_result',
            ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('attachments', [
            'medical_record_id' => $record->id,
            'original_name' => 'ket_qua_xet_nghiem.pdf',
            'file_type' => 'lab_result',
        ]);

        $attachment = Attachment::where('medical_record_id', $record->id)->first();
        $this->assertNotNull($attachment);
        Storage::disk('local')->assertExists($attachment->file_path);
    }

    /**
     * Test bảo mật Policy (AttachmentPolicy):
     * - Bác sĩ điều trị tải được file (200).
     * - Chính bệnh nhân A sở hữu bệnh án tải được file (200).
     * - Bệnh nhân B (người ngoài) cố tình tải file của A bị chặn HTTP 403 Forbidden.
     */
    public function test_security_policy_blocks_unauthorized_patient_from_downloading_attachment(): void
    {
        Storage::fake('local');

        $appointment = Appointment::create([
            'appointment_code' => 'AP-TEST-004',
            'patient_profile_id' => $this->patientProfileA->id,
            'doctor_id' => $this->doctor->id,
            'specialty_id' => $this->specialty->id,
            'appointment_date' => Carbon::today(),
            'shift' => 'morning',
            'status' => Appointment::STATUS_COMPLETED,
        ]);

        $record = MedicalRecord::create([
            'record_code' => MedicalRecord::generateRecordCode(),
            'appointment_id' => $appointment->id,
            'patient_profile_id' => $this->patientProfileA->id,
            'doctor_id' => $this->doctor->id,
            'symptoms' => 'Bệnh án bảo mật',
            'diagnosis' => 'Chẩn đoán bảo mật',
        ]);

        $filePath = 'medical_records/test_secure_file.pdf';
        Storage::disk('local')->put($filePath, 'NOI DUNG XET NGHIEM BI MAT');

        $attachment = Attachment::create([
            'medical_record_id' => $record->id,
            'uploaded_by_user_id' => $this->doctorUser->id,
            'original_name' => 'bao_mat_benh_nhan_a.pdf',
            'file_path' => $filePath,
            'file_size' => 1024,
            'file_mime' => 'application/pdf',
            'file_type' => 'lab_result',
        ]);

        // 1. Bệnh nhân B (người lạ) truy cập -> Phải bị chặn 403 Forbidden!
        $responseUnauthorized = $this->actingAs($this->patientUserB)
            ->get(route('attachments.download', $attachment->id));
        $responseUnauthorized->assertStatus(403);

        // 2. Chính Bệnh nhân A sở hữu hồ sơ -> Cho phép tải (200 OK)
        $responsePatientA = $this->actingAs($this->patientUserA)
            ->get(route('attachments.download', $attachment->id));
        $responsePatientA->assertStatus(200);

        // 3. Bác sĩ điều trị -> Cho phép tải (200 OK)
        $responseDoctor = $this->actingAs($this->doctorUser)
            ->get(route('attachments.download', $attachment->id));
        $responseDoctor->assertStatus(200);

        // 4. Admin quản trị hệ thống -> Cho phép tải (200 OK)
        $responseAdmin = $this->actingAs($this->adminUser)
            ->get(route('attachments.download', $attachment->id));
        $responseAdmin->assertStatus(200);
    }
}

