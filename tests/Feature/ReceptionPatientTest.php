<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\PatientProfile;
use App\Models\Specialty;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class ReceptionPatientTest extends TestCase
{
    private function getReceptionist(): User
    {
        return User::where('role', User::ROLE_RECEPTIONIST)->first();
    }

    private function getPatient(): User
    {
        return User::where('role', User::ROLE_PATIENT)->first();
    }

    public function test_receptionist_and_admin_can_view_patients_index(): void
    {
        $receptionist = $this->getReceptionist();

        $response = $this->actingAs($receptionist)->get('/reception/patients');

        $response->assertStatus(200);
        $response->assertSee('Danh Sách Hồ Sơ Bệnh Nhân');
    }

    public function test_unauthorized_user_cannot_access_reception_patients(): void
    {
        // 1. Guest
        $guestResponse = $this->get('/reception/patients');
        $guestResponse->assertRedirect(route('login'));

        // 2. Patient
        $patient = $this->getPatient();
        $patientResponse = $this->actingAs($patient)->get('/reception/patients');
        $patientResponse->assertStatus(403);
    }

    public function test_receptionist_can_create_patient_with_auto_generated_code(): void
    {
        $receptionist = $this->getReceptionist();
        $randomPhone = '098' . rand(1000000, 9999999);

        $response = $this->actingAs($receptionist)->post('/reception/patients', [
            'full_name' => 'Nguyễn Bệnh Nhân Mới',
            'dob' => '1992-08-15',
            'gender' => 'male',
            'phone' => $randomPhone,
            'email' => 'newpatient' . rand(100, 999) . '@example.com',
            'identity_card' => '00109200' . rand(1000, 9999),
            'insurance_code' => 'DN4010' . rand(100000, 999999),
            'address' => 'Hà Nội, Việt Nam',
        ]);

        $createdPatient = PatientProfile::where('phone', $randomPhone)->first();
        $this->assertNotNull($createdPatient);
        $this->assertStringStartsWith('BN-', $createdPatient->patient_code);
        $this->assertEquals('Nguyễn Bệnh Nhân Mới', $createdPatient->full_name);

        $response->assertRedirect(route('reception.patients.show', $createdPatient));
    }

    public function test_patient_creation_validation_fails_on_invalid_data(): void
    {
        $receptionist = $this->getReceptionist();

        // 1. Future date of birth & invalid phone
        $response = $this->actingAs($receptionist)->post('/reception/patients', [
            'full_name' => '',
            'dob' => Carbon::tomorrow()->toDateString(),
            'gender' => 'invalid_gender',
            'phone' => '12345',
        ]);

        $response->assertSessionHasErrors(['full_name', 'dob', 'gender', 'phone']);
    }

    public function test_receptionist_can_search_patients_by_phone_or_name(): void
    {
        $receptionist = $this->getReceptionist();
        $uniquePhone = '093' . rand(1000000, 9999999);

        $patient = PatientProfile::create([
            'patient_code' => PatientProfile::generatePatientCode(),
            'full_name' => 'Bệnh Nhân Tìm Kiếm Test',
            'dob' => '1988-03-20',
            'gender' => 'female',
            'phone' => $uniquePhone,
        ]);

        // Search by phone
        $response = $this->actingAs($receptionist)->get('/reception/patients?search=' . $uniquePhone);
        $response->assertStatus(200);
        $response->assertSee('Bệnh Nhân Tìm Kiếm Test');

        // Search by name
        $responseName = $this->actingAs($receptionist)->get('/reception/patients?search=Tìm+Kiếm+Test');
        $responseName->assertStatus(200);
        $responseName->assertSee($patient->patient_code);
    }

    public function test_receptionist_can_view_patient_details(): void
    {
        $receptionist = $this->getReceptionist();
        $patient = PatientProfile::first();

        $response = $this->actingAs($receptionist)->get('/reception/patients/' . $patient->id);

        $response->assertStatus(200);
        $response->assertSee($patient->full_name);
        $response->assertSee($patient->patient_code);
    }

    public function test_receptionist_can_update_patient_profile(): void
    {
        $receptionist = $this->getReceptionist();
        $patient = PatientProfile::create([
            'patient_code' => PatientProfile::generatePatientCode(),
            'full_name' => 'Bệnh Nhân Cần Sửa',
            'dob' => '1995-01-01',
            'gender' => 'male',
            'phone' => '094' . rand(1000000, 9999999),
        ]);

        $response = $this->actingAs($receptionist)->put('/reception/patients/' . $patient->id, [
            'full_name' => 'Bệnh Nhân Đã Sửa Tên',
            'dob' => '1995-01-01',
            'gender' => 'female',
            'phone' => $patient->phone,
            'address' => 'Địa chỉ mới cập nhật',
        ]);

        $response->assertRedirect(route('reception.patients.show', $patient));
        $this->assertDatabaseHas('patient_profiles', [
            'id' => $patient->id,
            'full_name' => 'Bệnh Nhân Đã Sửa Tên',
            'gender' => 'female',
            'address' => 'Địa chỉ mới cập nhật',
        ]);
    }

    public function test_cannot_delete_patient_with_existing_appointments(): void
    {
        $receptionist = $this->getReceptionist();
        // Find a patient who has appointments
        $appointment = Appointment::first();
        $patient = $appointment->patientProfile;

        $response = $this->actingAs($receptionist)->delete('/reception/patients/' . $patient->id);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('patient_profiles', ['id' => $patient->id]);
    }

    public function test_can_delete_patient_without_appointments(): void
    {
        $receptionist = $this->getReceptionist();
        $freshPatient = PatientProfile::create([
            'patient_code' => PatientProfile::generatePatientCode(),
            'full_name' => 'Bệnh Nhân Chưa Khám',
            'dob' => '2000-01-01',
            'gender' => 'other',
            'phone' => '096' . rand(1000000, 9999999),
        ]);

        $response = $this->actingAs($receptionist)->delete('/reception/patients/' . $freshPatient->id);

        $response->assertRedirect(route('reception.patients.index'));
        $this->assertDatabaseMissing('patient_profiles', ['id' => $freshPatient->id]);
    }

    public function test_receptionist_can_checkin_appointment(): void
    {
        $receptionist = $this->getReceptionist();
        $patient = PatientProfile::first();
        $doctor = Doctor::first();

        $appointment = Appointment::create([
            'appointment_code' => Appointment::generateAppointmentCode(),
            'patient_profile_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'specialty_id' => $doctor->specialty_id,
            'appointment_date' => Carbon::today(),
            'shift' => 'morning',
            'symptom_description' => 'Khám tổng quát',
            'status' => Appointment::STATUS_PENDING,
        ]);

        $response = $this->actingAs($receptionist)->put('/reception/appointments/' . $appointment->id . '/checkin');

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => Appointment::STATUS_CONFIRMED,
        ]);
    }

    public function test_receptionist_can_cancel_appointment_with_reason(): void
    {
        $receptionist = $this->getReceptionist();
        $patient = PatientProfile::first();
        $doctor = Doctor::first();

        $appointment = Appointment::create([
            'appointment_code' => Appointment::generateAppointmentCode(),
            'patient_profile_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'specialty_id' => $doctor->specialty_id,
            'appointment_date' => Carbon::today(),
            'shift' => 'afternoon',
            'symptom_description' => 'Đau đầu',
            'status' => Appointment::STATUS_PENDING,
        ]);

        $response = $this->actingAs($receptionist)->put('/reception/appointments/' . $appointment->id . '/cancel', [
            'cancellation_reason' => 'Bệnh nhân có việc bận đột xuất',
        ]);

        $response->assertSessionHas('success');
        $appointment->refresh();
        $this->assertEquals(Appointment::STATUS_CANCELLED, $appointment->status);
        $this->assertStringContainsString('Bệnh nhân có việc bận đột xuất', $appointment->cancellation_reason);
    }

    public function test_receptionist_can_reassign_appointment(): void
    {
        $receptionist = $this->getReceptionist();
        $patient = PatientProfile::first();
        $doctor1 = Doctor::first();
        $doctor2 = Doctor::where('id', '!=', $doctor1->id)->first();

        $appointment = Appointment::create([
            'appointment_code' => Appointment::generateAppointmentCode(),
            'patient_profile_id' => $patient->id,
            'doctor_id' => $doctor1->id,
            'specialty_id' => $doctor1->specialty_id,
            'appointment_date' => Carbon::today(),
            'shift' => 'morning',
            'symptom_description' => 'Khám theo dõi',
            'status' => Appointment::STATUS_PENDING,
        ]);

        $response = $this->actingAs($receptionist)->put('/reception/appointments/' . $appointment->id . '/reassign', [
            'doctor_id' => $doctor2->id,
            'appointment_date' => Carbon::tomorrow()->toDateString(),
            'shift' => 'afternoon',
            'reassign_reason' => 'Bác sĩ 1 bận họp khẩn cấp',
        ]);

        $response->assertSessionHas('success');
        $appointment->refresh();
        $this->assertEquals($doctor2->id, $appointment->doctor_id);
        $this->assertEquals('afternoon', $appointment->shift);
    }

    public function test_receptionist_can_create_walkin_appointment_with_auto_checkin(): void
    {
        $receptionist = $this->getReceptionist();
        $specialty = Specialty::where('is_active', true)->first();
        $doctor = Doctor::where('specialty_id', $specialty->id)->first();
        $uniquePhone = '097' . rand(1000000, 9999999);
        $testDate = Carbon::tomorrow()->addDays(rand(400, 600))->toDateString();

        $response = $this->actingAs($receptionist)->post('/reception/appointments/walk-in', [
            'is_existing_patient' => 0,
            'full_name' => 'Bệnh Nhân Vãng Lai',
            'dob' => '1998-04-12',
            'gender' => 'male',
            'phone' => $uniquePhone,
            'specialty_id' => $specialty->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => $testDate,
            'shift' => 'morning',
            'symptom_description' => 'Đến khám trực tiếp tại quầy vì sốt',
        ]);

        $patient = PatientProfile::where('phone', $uniquePhone)->first();
        $this->assertNotNull($patient);

        $appointment = Appointment::where('patient_profile_id', $patient->id)->latest('id')->first();
        $this->assertNotNull($appointment);
        // Walk-in is auto checked-in
        $this->assertEquals(Appointment::STATUS_CONFIRMED, $appointment->status);
        $this->assertEquals($doctor->id, $appointment->doctor_id);

        $response->assertRedirect(route('reception.appointments.index', ['date' => $testDate]));
    }

    public function test_receptionist_can_view_and_print_appointment_ticket(): void
    {
        $receptionist = $this->getReceptionist();
        $appointment = Appointment::first();

        $response = $this->actingAs($receptionist)->get('/reception/appointments/' . $appointment->id . '/print');

        $response->assertStatus(200);
        $response->assertSee('PHIẾU TIẾP NHẬN NGOẠI TRÚ');
        $response->assertSee($appointment->appointment_code);
    }

    public function test_receptionist_can_export_daily_appointments_csv(): void
    {
        $receptionist = $this->getReceptionist();

        $response = $this->actingAs($receptionist)->get('/reception/appointments/export?date=' . Carbon::today()->toDateString());

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_receptionist_can_view_live_queue_display(): void
    {
        $receptionist = $this->getReceptionist();

        $response = $this->actingAs($receptionist)->get('/reception/appointments/queue-display');

        $response->assertStatus(200);
        $response->assertSee('BỆNH VIỆN ĐA KHOA TÂM ANH');
        $response->assertSee('DANH SÁCH MỜI VÀO PHÒNG KHÁM');
    }

    public function test_receptionist_can_print_patient_id_card(): void
    {
        $receptionist = $this->getReceptionist();
        $patient = PatientProfile::first();

        $response = $this->actingAs($receptionist)->get('/reception/patients/' . $patient->id . '/print-card');

        $response->assertStatus(200);
        $response->assertSee('THẺ BỆNH NHÂN NGOẠI TRÚ');
        $response->assertSee($patient->patient_code);
    }

    public function test_walkin_registration_with_priority_and_vitals(): void
    {
        $receptionist = $this->getReceptionist();
        $doctor = Doctor::where('is_available', true)->first();
        $specialty = $doctor->specialty;
        $uniquePhone = '096' . rand(1000000, 9999999);
        $testDate = Carbon::tomorrow()->addDays(rand(700, 900))->toDateString();

        $response = $this->actingAs($receptionist)->post('/reception/appointments/walk-in', [
            'is_existing_patient' => '0',
            'full_name' => 'Bệnh Nhân Cấp Cứu Ưu Tiên',
            'dob' => '1950-01-01',
            'gender' => 'male',
            'phone' => $uniquePhone,
            'specialty_id' => $specialty->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => $testDate,
            'shift' => 'morning',
            'is_priority' => '1',
            'symptom_description' => '[Sinh hiệu: HA: 140/90 mmHg, Thân nhiệt: 38.5°C] Sốt cao khó thở',
        ]);

        $createdPatient = PatientProfile::where('phone', $uniquePhone)->first();
        $this->assertNotNull($createdPatient);

        $appointment = Appointment::where('patient_profile_id', $createdPatient->id)->first();
        $this->assertNotNull($appointment);
        $this->assertEquals(Appointment::STATUS_CONFIRMED, $appointment->status);
        $this->assertTrue($appointment->is_priority);
        $this->assertStringContainsString('[ƯU TIÊN]', $appointment->symptom_description);
        $this->assertStringContainsString('Sinh hiệu: HA: 140/90 mmHg', $appointment->symptom_description);
    }

    public function test_patient_age_display_logic_for_infant_and_adult(): void
    {
        // 1. Infant (5 months old)
        $infant = new PatientProfile([
            'dob' => Carbon::now()->subMonths(5),
        ]);
        $this->assertEquals('5 tháng tuổi', $infant->age_text);

        // 2. Adult (30 years old)
        $adult = new PatientProfile([
            'dob' => Carbon::now()->subYears(30),
        ]);
        $this->assertEquals('30 tuổi', $adult->age_text);
    }

    public function test_receptionist_can_print_receipt_with_vietqr(): void
    {
        $receptionist = $this->getReceptionist();
        $appointment = Appointment::first();

        $response = $this->actingAs($receptionist)->get(route('reception.appointments.receipt', $appointment));

        $response->assertStatus(200);
        $response->assertSee('BIÊN LAI THU TIỀN KHÁM BỆNH');
        $response->assertSee('BỆNH VIỆN ĐA KHOA TÂM ANH');
        $response->assertSee('vietqr.io');
    }
}
