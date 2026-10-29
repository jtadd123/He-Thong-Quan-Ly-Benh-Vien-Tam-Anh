<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\PatientProfile;
use App\Models\Specialty;
use Carbon\Carbon;
use Tests\TestCase;

class KioskSelfServiceTest extends TestCase
{
    public function test_kiosk_home_page_accessible(): void
    {
        $response = $this->get(route('kiosk.index'));
        $response->assertStatus(200);
        $response->assertSee('Kính Chào Quý Khách Đến Khám Bệnh');
        $response->assertSee('ĐÃ CÓ HẸN TRƯỚC');
        $response->assertSee('TÁI KHÁM / HỒ SƠ CŨ');
        $response->assertSee('BỆNH NHÂN MỚI');
    }

    public function test_kiosk_checkin_existing_appointment(): void
    {
        $patient = PatientProfile::create([
            'patient_code' => 'BN-KIOSK-' . uniqid(),
            'full_name' => 'Bệnh Nhân Kiosk Checkin',
            'phone' => '0933445566',
            'dob' => '1990-05-10',
        ]);

        $specialty = Specialty::first();
        $doctor = \App\Models\Doctor::first();
        $appointment = Appointment::create([
            'appointment_code' => 'AP-KIOSK-' . uniqid(),
            'patient_profile_id' => $patient->id,
            'doctor_id' => $doctor?->id,
            'specialty_id' => $specialty?->id,
            'appointment_date' => Carbon::today()->toDateString(),
            'shift' => 'morning',
            'symptom_description' => 'Khám tổng quát Kiosk',
            'status' => Appointment::STATUS_PENDING,
        ]);

        // Checkin qua Kiosk bằng SĐT
        $response = $this->postJson(route('kiosk.checkin'), [
            'query' => '0933445566',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // Cuộc hẹn được cập nhật trạng thái confirmed
        $this->assertEquals(Appointment::STATUS_CONFIRMED, $appointment->fresh()->status);
    }

    public function test_kiosk_lookup_returning_patient(): void
    {
        $idCard = '001099' . rand(100000, 999999);
        $patient = PatientProfile::create([
            'patient_code' => 'BN-RET-' . uniqid(),
            'full_name' => 'Bệnh Nhân Tái Khám',
            'phone' => '09' . rand(10000000, 99999999),
            'dob' => '1988-12-12',
            'identity_card' => $idCard,
        ]);

        $response = $this->postJson(route('kiosk.lookup'), [
            'identifier' => $idCard,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'patient' => [
                'id' => $patient->id,
                'full_name' => 'Bệnh Nhân Tái Khám',
            ],
        ]);
    }

    public function test_kiosk_register_new_patient(): void
    {
        $specialty = Specialty::first();

        $response = $this->postJson(route('kiosk.register-new'), [
            'full_name' => 'Lê Bệnh Nhân Mới Kiosk',
            'phone' => '0977889900',
            'birth_year' => 1995,
            'gender' => 'female',
            'identity_card' => '001095012345',
            'insurance_code' => 'DN40101234567890',
            'specialty_id' => $specialty?->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('patient_profiles', [
            'phone' => '0977889900',
            'gender' => 'female',
        ]);
    }

    public function test_kiosk_print_ticket_view(): void
    {
        $patient = PatientProfile::create([
            'patient_code' => 'BN-TICKET-' . uniqid(),
            'full_name' => 'Bệnh Nhân In Vé Kiosk',
            'phone' => '0988112233',
            'dob' => '1995-07-21',
        ]);

        $specialty = Specialty::first();
        $doctor = \App\Models\Doctor::first();
        $appointment = Appointment::create([
            'appointment_code' => 'AP-TCK-' . uniqid(),
            'patient_profile_id' => $patient->id,
            'doctor_id' => $doctor?->id,
            'specialty_id' => $specialty?->id,
            'appointment_date' => Carbon::today()->toDateString(),
            'shift' => 'morning',
            'symptom_description' => 'Khám chuyên khoa Kiosk',
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        $response = $this->get(route('kiosk.ticket', $appointment));
        $response->assertStatus(200);
        $response->assertSee('BỆNH VIỆN ĐA KHOA TÂM ANH');
        $response->assertSee('Bệnh Nhân In Vé Kiosk');
        $response->assertSee('PHIẾU SỐ THỨ TỰ TIẾP NHẬN');
    }
}
