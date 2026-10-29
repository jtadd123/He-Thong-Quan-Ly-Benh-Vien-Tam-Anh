<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\PatientProfile;
use App\Models\Specialty;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class PatientSafetyClinicalTest extends TestCase
{
    protected User $receptionistUser;
    protected User $doctorUser;
    protected Doctor $doctor;
    protected Specialty $specialty;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receptionistUser = User::where('role', User::ROLE_RECEPTIONIST)->first()
            ?? User::factory()->create(['role' => User::ROLE_RECEPTIONIST]);

        $this->doctorUser = User::where('role', User::ROLE_DOCTOR)->first()
            ?? User::factory()->create(['role' => User::ROLE_DOCTOR]);

        $this->doctor = Doctor::where('user_id', $this->doctorUser->id)->first()
            ?? Doctor::factory()->create(['user_id' => $this->doctorUser->id]);

        $this->specialty = Specialty::first() ?? Specialty::factory()->create();
    }

    public function test_receptionist_can_create_patient_with_blood_group_and_allergies(): void
    {
        $response = $this->actingAs($this->receptionistUser)->post(
            route('reception.patients.store'),
            [
                'full_name' => 'Trần Thị Ngọc An Toàn',
                'dob' => '1995-04-12',
                'gender' => 'female',
                'phone' => '0912999888',
                'identity_card' => '079195009999',
                'blood_group' => 'O+',
                'allergies' => 'Dị ứng Penicillin và Amoxicillin, thức ăn có vỏ (tôm cua)',
                'medical_history' => 'Viêm xoang mãn tính, mổ ruột thừa 2018',
            ]
        );

        $response->assertRedirect();

        $patient = PatientProfile::where('phone', '0912999888')->first();
        $this->assertNotNull($patient);
        $this->assertEquals('O+', $patient->blood_group);
        $this->assertEquals('Dị ứng Penicillin và Amoxicillin, thức ăn có vỏ (tôm cua)', $patient->allergies);
        $this->assertTrue($patient->hasAllergies());
        $this->assertTrue($patient->hasMedicalHistory());
    }

    public function test_doctor_examine_screen_displays_red_allergy_alert_banner(): void
    {
        $allergicPatient = PatientProfile::create([
            'patient_code' => PatientProfile::generatePatientCode(),
            'full_name' => 'Lê Bị Dị Ứng Nặng',
            'dob' => '1992-06-15',
            'gender' => 'female',
            'phone' => '0988777666',
            'blood_group' => 'AB-',
            'allergies' => 'Dị ứng kháng sinh Cephalosporin và Aspirin',
        ]);

        $appointment = Appointment::create([
            'appointment_code' => 'AP-TEST-ALLERGY-' . rand(1000, 9999),
            'patient_profile_id' => $allergicPatient->id,
            'doctor_id' => $this->doctor->id,
            'specialty_id' => $this->specialty->id,
            'appointment_date' => Carbon::tomorrow()->addDays(9),
            'shift' => 'morning',
            'queue_number' => 66,
            'status' => Appointment::STATUS_CONFIRMED,
            'symptom_description' => 'Test dị ứng',
        ]);

        $response = $this->actingAs($this->doctorUser)->get(
            route('doctor.examine', $appointment)
        );

        $response->assertStatus(200);
        $response->assertSee('BÁO ĐỘNG DỊ ỨNG');
        $response->assertSee('Dị ứng kháng sinh Cephalosporin và Aspirin');
        $response->assertSee('AB-');
    }

    public function test_patient_print_card_displays_blood_group_and_allergy_warning(): void
    {
        $patient = PatientProfile::create([
            'patient_code' => PatientProfile::generatePatientCode(),
            'full_name' => 'Ngô Thị Cảnh Báo',
            'dob' => '1988-11-20',
            'gender' => 'female',
            'phone' => '0977666555',
            'blood_group' => 'B+',
            'allergies' => 'Dị ứng Paracetamol',
        ]);

        $response = $this->actingAs($this->receptionistUser)->get(
            route('reception.patients.print-card', $patient)
        );

        $response->assertStatus(200);
        $response->assertSee('B+');
        $response->assertSee('DỊ ỨNG: Dị ứng Paracetamol');
    }

    public function test_patient_vital_rejects_systolic_less_than_or_equal_diastolic(): void
    {
        $patient = PatientProfile::first();

        // Thử nhập huyết áp tâm thu nhỏ hơn tâm trương (70/120 mmHg - vô lý thực tế y khoa)
        $response = $this->actingAs($this->receptionistUser)->post(
            route('reception.patients.vitals.store', $patient),
            [
                'systolic_bp' => 70,
                'diastolic_bp' => 120,
                'heart_rate' => 80,
            ]
        );

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Huyết áp tâm thu (70 mmHg) phải lớn hơn huyết áp tâm trương (120 mmHg)', session('error'));
    }

    public function test_code_generation_is_strictly_unique(): void
    {
        $code1 = PatientProfile::generatePatientCode();
        $this->assertMatchesRegularExpression('/^BN-\d{8}-\d{4}$/', $code1);

        $p1 = PatientProfile::create([
            'patient_code' => $code1,
            'full_name' => 'Bệnh Nhân Kiểm Thử Mã 1',
            'dob' => '1990-01-01',
            'gender' => 'male',
            'phone' => '0912' . rand(100000, 999999),
        ]);

        $code2 = PatientProfile::generatePatientCode();
        $this->assertNotEquals($code1, $code2);
        $this->assertMatchesRegularExpression('/^BN-\d{8}-\d{4}$/', $code2);
    }
}
