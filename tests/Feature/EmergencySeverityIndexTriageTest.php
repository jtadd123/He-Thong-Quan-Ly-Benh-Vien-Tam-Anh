<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\PatientProfile;
use App\Models\PatientVital;
use App\Models\Specialty;
use App\Models\User;
use App\Services\EmergencyTriageService;
use Carbon\Carbon;
use Tests\TestCase;

class EmergencySeverityIndexTriageTest extends TestCase
{
    protected User $receptionist;
    protected PatientProfile $patient;
    protected Doctor $doctor;
    protected Specialty $specialty;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receptionist = User::where('role', User::ROLE_RECEPTIONIST)->first()
            ?? User::factory()->create([
                'email' => 'rec_esi_' . uniqid() . '@tamanh.vn',
                'role' => User::ROLE_RECEPTIONIST,
            ]);

        $this->patient = PatientProfile::first() ?? PatientProfile::create([
            'user_id' => $this->receptionist->id,
            'patient_code' => 'BN' . rand(100000, 999999),
            'full_name' => 'Lê Thị Triage Test',
            'dob' => '1980-01-01',
            'gender' => 'female',
            'phone' => '0988776655',
        ]);

        $this->specialty = Specialty::first() ?? Specialty::create([
            'name' => 'Cấp Cứu Tổng Hợp',
            'slug' => 'cap-cuu-tong-hop',
            'description' => 'Khoa Cấp Cứu',
            'is_active' => true,
        ]);

        $this->doctor = Doctor::first() ?? Doctor::create([
            'user_id' => User::factory()->create(['role' => User::ROLE_DOCTOR])->id,
            'specialty_id' => $this->specialty->id,
            'title' => 'BS. Cấp Cứu',
            'qualification' => 'Chuyên khoa Cấp cứu',
            'room_number' => 'CC01',
            'is_available' => true,
        ]);
    }

    public function test_classifies_esi_level_1_for_life_threatening_signs(): void
    {
        // 1. Dấu hiệu hôn mê hoặc ngưng thở
        $result = EmergencyTriageService::classify($this->patient, 'Bệnh nhân hôn mê sâu, tím tái toàn thân');
        $this->assertEquals(1, $result['level']);
        $this->assertEquals(0, $result['max_wait_minutes']);
        $this->assertTrue($result['is_urgent']);

        // 2. MEWS >= 7
        $vital = new PatientVital([
            'systolic_bp' => 60,
            'heart_rate' => 140,
            'respiratory_rate' => 35,
            'temperature' => 39.8,
            'spo2' => 85,
        ]);
        $this->assertGreaterThanOrEqual(7, $vital->mews_score);

        $resultVital = EmergencyTriageService::classify($this->patient, 'Mệt lả', $vital);
        $this->assertEquals(1, $resultVital['level']);
    }

    public function test_classifies_esi_level_2_for_emergent_high_risk_cases(): void
    {
        // Đau ngực cấp nghi nhồi máu cơ tim
        $result = EmergencyTriageService::classify($this->patient, 'Đau ngực dữ dội, vã mồ hôi khó thở');
        $this->assertEquals(2, $result['level']);
        $this->assertEquals(10, $result['max_wait_minutes']);
        $this->assertTrue($result['is_urgent']);
    }

    public function test_classifies_esi_level_3_for_elderly_or_acute_conditions(): void
    {
        $elderlyUser = User::factory()->create([
            'email' => 'elderly_' . uniqid() . '@tamanh.vn',
            'role' => User::ROLE_PATIENT,
        ]);

        $elderlyPatient = PatientProfile::create([
            'user_id' => $elderlyUser->id,
            'patient_code' => 'BN' . rand(100000, 999999),
            'full_name' => 'Cụ Ông Cao Tuổi',
            'dob' => Carbon::today()->subYears(85),
            'gender' => 'male',
            'phone' => '0911223344',
        ]);

        $result = EmergencyTriageService::classify($elderlyPatient, 'Sốt cao, mệt nhiều, chóng mặt');
        $this->assertEquals(3, $result['level']);
        $this->assertEquals(30, $result['max_wait_minutes']);
    }

    public function test_classifies_esi_level_5_for_routine_consultations(): void
    {
        $result = EmergencyTriageService::classify($this->patient, 'Tái khám huyết áp định kỳ, lấy thuốc hàng tháng');
        $this->assertEquals(5, $result['level']);
        $this->assertFalse($result['is_urgent']);
    }

    public function test_appointment_model_renders_esi_badge_and_ordering(): void
    {
        $appCritical = Appointment::create([
            'appointment_code' => 'AP-ESI1-' . uniqid(),
            'patient_profile_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'specialty_id' => $this->specialty->id,
            'appointment_date' => Carbon::today(),
            'shift' => 'morning',
            'status' => Appointment::STATUS_CONFIRMED,
            'symptom_description' => 'Hôn mê',
            'esi_level' => 1,
        ]);

        $appRoutine = Appointment::create([
            'appointment_code' => 'AP-ESI5-' . uniqid(),
            'patient_profile_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'specialty_id' => $this->specialty->id,
            'appointment_date' => Carbon::today(),
            'shift' => 'morning',
            'status' => Appointment::STATUS_CONFIRMED,
            'symptom_description' => 'Khám tổng quát',
            'esi_level' => 5,
        ]);

        $this->assertStringContainsString('Cấp 1: Hồi Sức', $appCritical->esi_badge);
        $this->assertStringContainsString('bg-danger', $appCritical->esi_badge);

        $orderedList = Appointment::whereIn('id', [$appCritical->id, $appRoutine->id])
            ->priorityEsiOrder()
            ->pluck('id')
            ->toArray();

        $this->assertEquals([$appCritical->id, $appRoutine->id], $orderedList, 'Ca ESI 1 phải xếp trước ca ESI 5');
    }
}
