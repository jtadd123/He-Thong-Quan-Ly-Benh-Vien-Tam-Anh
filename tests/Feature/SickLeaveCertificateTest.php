<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\PatientProfile;
use App\Models\SickLeaveCertificate;
use App\Models\Specialty;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class SickLeaveCertificateTest extends TestCase
{
    protected User $receptionistUser;
    protected User $doctorUser;
    protected User $patientUser;
    protected Doctor $doctor;
    protected Specialty $specialty;
    protected PatientProfile $patientProfile;
    protected Appointment $appointment;
    protected MedicalRecord $record;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receptionistUser = User::where('role', User::ROLE_RECEPTIONIST)->first()
            ?? User::factory()->create(['role' => User::ROLE_RECEPTIONIST]);

        $this->doctorUser = User::where('role', User::ROLE_DOCTOR)->first()
            ?? User::factory()->create(['role' => User::ROLE_DOCTOR]);

        $this->patientUser = User::where('role', User::ROLE_PATIENT)->first()
            ?? User::factory()->create(['role' => User::ROLE_PATIENT]);

        $this->doctor = Doctor::where('user_id', $this->doctorUser->id)->first()
            ?? Doctor::factory()->create(['user_id' => $this->doctorUser->id]);

        $this->specialty = Specialty::first() ?? Specialty::factory()->create();

        $this->patientProfile = PatientProfile::first() ?? PatientProfile::factory()->create();

        $this->appointment = Appointment::create([
            'appointment_code' => 'AP-TEST-CERT-' . uniqid() . '-' . rand(100, 999),
            'patient_profile_id' => $this->patientProfile->id,
            'doctor_id' => $this->doctor->id,
            'specialty_id' => $this->specialty->id,
            'appointment_date' => Carbon::today(),
            'shift' => 'morning',
            'queue_number' => rand(100, 999),
            'status' => Appointment::STATUS_COMPLETED,
            'symptom_description' => 'Test xin giấy nghỉ ốm',
        ]);

        $this->record = MedicalRecord::create([
            'record_code' => MedicalRecord::generateRecordCode(),
            'appointment_id' => $this->appointment->id,
            'patient_profile_id' => $this->patientProfile->id,
            'doctor_id' => $this->doctor->id,
            'symptoms' => 'Sốt xuất huyết Dengue ngày 3',
            'diagnosis' => 'Sốt xuất huyết Dengue có dấu hiệu cảnh báo',
        ]);
    }

    public function test_receptionist_can_view_certificate_create_form(): void
    {
        $response = $this->actingAs($this->receptionistUser)->get(
            route('reception.certificates.create', $this->record)
        );

        $response->assertStatus(200);
        $response->assertSee('Cấp Giấy Chứng Nhận Nghỉ Việc Hưởng BHXH');
        $response->assertSee($this->patientProfile->full_name);
        $response->assertSee('Sốt xuất huyết Dengue có dấu hiệu cảnh báo');
    }

    public function test_can_issue_sick_leave_certificate_with_auto_calculated_dates(): void
    {
        $fromDate = Carbon::today()->toDateString();
        $expectedToDate = Carbon::today()->addDays(4)->toDateString(); // 5 days: today + 4

        $response = $this->actingAs($this->receptionistUser)->post(
            route('reception.certificates.store', $this->record),
            [
                'employer_name' => 'Công ty TNHH Phần Mềm Tâm Anh Tech',
                'diagnosis' => 'Sốt xuất huyết Dengue có dấu hiệu cảnh báo',
                'treatment_method' => 'Điều trị ngoại trú',
                'leave_days' => 5,
                'from_date' => $fromDate,
                'notes' => 'Tái khám xét nghiệm tiểu cầu sau 2 ngày',
            ]
        );

        $response->assertRedirect();

        $certificate = SickLeaveCertificate::where('medical_record_id', $this->record->id)->first();
        $this->assertNotNull($certificate);
        $this->assertStringStartsWith('GCN-', $certificate->certificate_code);
        $this->assertEquals(5, $certificate->leave_days);
        $this->assertEquals($fromDate, $certificate->from_date->toDateString());
        $this->assertEquals($expectedToDate, $certificate->to_date->toDateString());
        $this->assertEquals('5 ngày (Năm ngày)', $certificate->leave_days_text);
    }

    public function test_cannot_exceed_30_days_leave_in_single_certificate(): void
    {
        $response = $this->actingAs($this->receptionistUser)->post(
            route('reception.certificates.store', $this->record),
            [
                'diagnosis' => 'Chấn thương',
                'treatment_method' => 'Điều trị ngoại trú',
                'leave_days' => 45, // Exceeds 30 days
                'from_date' => Carbon::today()->toDateString(),
            ]
        );

        $response->assertSessionHasErrors(['leave_days']);
    }

    public function test_can_print_official_c65_certificate(): void
    {
        $certificate = SickLeaveCertificate::create([
            'certificate_code' => SickLeaveCertificate::generateCertificateCode(),
            'medical_record_id' => $this->record->id,
            'patient_profile_id' => $this->patientProfile->id,
            'doctor_id' => $this->doctor->id,
            'issued_by_user_id' => $this->receptionistUser->id,
            'employer_name' => 'Ngân hàng TMCP Quốc Tế',
            'diagnosis' => 'Viêm phế quản cấp',
            'treatment_method' => 'Điều trị ngoại trú',
            'leave_days' => 3,
            'from_date' => Carbon::today(),
            'to_date' => Carbon::today()->addDays(2),
        ]);

        $response = $this->actingAs($this->receptionistUser)->get(
            route('reception.certificates.print', $certificate)
        );

        $response->assertStatus(200);
        $response->assertSee('GIẤY CHỨNG NHẬN NGHỈ VIỆC HƯỞNG BẢO HIỂM XÃ HỘI');
        $response->assertSee($certificate->certificate_code);
        $response->assertSee($this->patientProfile->full_name);
        $response->assertSee('Ngân hàng TMCP Quốc Tế');
        $response->assertSee('Viêm phế quản cấp');
    }

    public function test_patient_cannot_view_another_patients_certificate(): void
    {
        $otherUser = User::factory()->create(['role' => User::ROLE_PATIENT]);
        $otherProfile = PatientProfile::create([
            'patient_code' => PatientProfile::generatePatientCode(),
            'user_id' => $otherUser->id,
            'full_name' => 'Bệnh Nhân Khác Nhau',
            'dob' => '1990-01-01',
            'gender' => 'male',
            'phone' => '0933' . rand(100000, 999999),
        ]);

        $certificate = SickLeaveCertificate::create([
            'certificate_code' => SickLeaveCertificate::generateCertificateCode(),
            'medical_record_id' => $this->record->id,
            'patient_profile_id' => $this->patientProfile->id,
            'doctor_id' => $this->doctor->id,
            'issued_by_user_id' => $this->receptionistUser->id,
            'diagnosis' => 'Bệnh kín',
            'treatment_method' => 'Điều trị ngoại trú',
            'leave_days' => 2,
            'from_date' => Carbon::today(),
            'to_date' => Carbon::today()->addDays(1),
        ]);

        $response = $this->actingAs($otherUser)->get(
            route('patient.certificates.print', $certificate)
        );

        // Expect 403 Forbidden because certificate belongs to $this->patientProfile, not $otherProfile
        $response->assertStatus(403);
    }
}
