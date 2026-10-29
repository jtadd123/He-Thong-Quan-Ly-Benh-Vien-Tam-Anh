<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\PatientProfile;
use App\Models\Specialty;
use App\Models\User;
use App\Services\HealthInsuranceService;
use Carbon\Carbon;
use Tests\TestCase;

class HealthInsuranceTest extends TestCase
{
    public function test_validate_bhyt_card_format(): void
    {
        // Hợp lệ: 2 chữ cái + 1 số mức hưởng (1-5) + 2 số mã tỉnh + 10 số BHXH = đúng 15 ký tự
        $this->assertTrue(HealthInsuranceService::validate('DN4010123456789'));
        $this->assertTrue(HealthInsuranceService::validate('CC1010123456789'));
        $this->assertTrue(HealthInsuranceService::validate('TE1010123456789'));
        $this->assertTrue(HealthInsuranceService::validate('HT3010123456789'));

        // Không hợp lệ
        $this->assertFalse(HealthInsuranceService::validate('INVALID'));
        $this->assertFalse(HealthInsuranceService::validate('DN6010123456789')); // Mức hưởng 6 không tồn tại
        $this->assertFalse(HealthInsuranceService::validate('DN4010123456')); // Thiếu ký tự
    }

    public function test_calculate_bhyt_copayment_correct_route(): void
    {
        $fee = 350000;

        // Trẻ em dưới 6 tuổi (TE 100%)
        $teResult = HealthInsuranceService::calculate('TE1010123456789', $fee, true);
        $this->assertTrue($teResult['is_valid']);
        $this->assertEquals(100, $teResult['benefit_rate_pct']);
        $this->assertEquals(350000, $teResult['insurance_paid_amount']);
        $this->assertEquals(0, $teResult['patient_copay_amount']);

        // Hưu trí (HT mức 3 = 95%)
        $htResult = HealthInsuranceService::calculate('HT3010123456789', $fee, true);
        $this->assertTrue($htResult['is_valid']);
        $this->assertEquals(95, $htResult['benefit_rate_pct']);
        $this->assertEquals(332500, $htResult['insurance_paid_amount']);
        $this->assertEquals(17500, $htResult['patient_copay_amount']);

        // Doanh nghiệp (DN mức 4 = 80%)
        $dnResult = HealthInsuranceService::calculate('DN4010123456789', $fee, true);
        $this->assertTrue($dnResult['is_valid']);
        $this->assertEquals(80, $dnResult['benefit_rate_pct']);
        $this->assertEquals(280000, $dnResult['insurance_paid_amount']);
        $this->assertEquals(70000, $dnResult['patient_copay_amount']);
    }

    public function test_calculate_bhyt_copayment_out_of_route(): void
    {
        $fee = 350000;

        // Khám ngoại trú trái tuyến: BHYT chi trả 0%, bệnh nhân thanh toán 100%
        $outResult = HealthInsuranceService::calculate('DN4010123456789', $fee, false);
        $this->assertTrue($outResult['is_valid']);
        $this->assertEquals(0, $outResult['benefit_rate_pct']);
        $this->assertEquals(0, $outResult['insurance_paid_amount']);
        $this->assertEquals(350000, $outResult['patient_copay_amount']);
    }

    public function test_print_receipt_with_bhyt_copayment(): void
    {
        $receptionist = User::where('role', 'receptionist')->first();
        if (!$receptionist) {
            $receptionist = User::factory()->create(['role' => 'receptionist']);
        }

        $patient = PatientProfile::create([
            'patient_code' => 'BN-BHYT-' . uniqid(),
            'full_name' => 'Nguyễn BHYT Test',
            'phone' => '0988776655',
            'dob' => '1990-01-01',
            'insurance_code' => 'DN4010123456789',
        ]);

        $specialty = Specialty::first();
        $doctor = \App\Models\Doctor::first();
        $appointment = Appointment::create([
            'appointment_code' => 'AP-BHYT-' . uniqid(),
            'patient_profile_id' => $patient->id,
            'doctor_id' => $doctor?->id,
            'specialty_id' => $specialty?->id,
            'appointment_date' => Carbon::today()->toDateString(),
            'shift' => 'morning',
            'symptom_description' => 'Khám ngoại trú theo bảo hiểm y tế',
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($receptionist)
            ->get(route('reception.appointments.receipt', [$appointment, 'route' => 'correct']));

        $response->assertStatus(200);
        $response->assertSee('DN4010123456789');
        $response->assertSee('Quỹ BHYT chi trả');
        $response->assertSee('Người bệnh cùng chi trả');
    }
}
