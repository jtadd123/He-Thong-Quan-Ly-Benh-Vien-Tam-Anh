<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\PatientProfile;
use App\Models\PatientVital;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class MewsEarlyWarningTest extends TestCase
{
    public function test_mews_calculation_low_risk_green(): void
    {
        // HA: 120/80, Mạch: 75, Nhiệt: 36.8, SpO2: 98 -> 0 điểm (An toàn)
        $mews = PatientVital::calculateMews(120, 75, 36.8, 98);
        $this->assertEquals(0, $mews['score']);
        $this->assertEquals('success', $mews['color']);
        $this->assertFalse($mews['is_emergency']);
    }

    public function test_mews_calculation_medium_risk_yellow(): void
    {
        // HA: 95 (1đ), Mạch: 105 (1đ), Nhiệt: 38.6 (2đ), SpO2: 96 (0đ) -> 4 điểm (Trung bình)
        $mews = PatientVital::calculateMews(95, 105, 38.6, 96);
        $this->assertEquals(4, $mews['score']);
        $this->assertEquals('warning', $mews['color']);
        $this->assertFalse($mews['is_emergency']);
    }

    public function test_mews_calculation_high_risk_emergency_red(): void
    {
        // HA: 65 tụt sâu (3đ), Mạch: 135 rất nhanh (3đ), Nhiệt: 39.0 (2đ), SpO2: 88 suy hô hấp (3đ) -> 11 điểm (Cấp cứu nguy cấp)
        $mews = PatientVital::calculateMews(65, 135, 39.0, 88);
        $this->assertGreaterThanOrEqual(5, $mews['score']);
        $this->assertEquals('danger', $mews['color']);
        $this->assertTrue($mews['is_emergency']);
    }

    public function test_patient_vital_model_mews_attributes(): void
    {
        $patient = PatientProfile::create([
            'patient_code' => 'BN-MEWS-' . uniqid(),
            'full_name' => 'Bệnh Nhân Cấp Cứu Test',
            'phone' => '0911223344',
            'dob' => '1985-05-15',
        ]);

        $vital = PatientVital::create([
            'patient_profile_id' => $patient->id,
            'recorded_at' => Carbon::now(),
            'systolic_bp' => 70, // 3 điểm
            'diastolic_bp' => 40,
            'heart_rate' => 135, // 3 điểm
            'temperature' => 39.2, // 2 điểm
            'spo2' => 88, // 3 điểm -> Tổng 11 điểm
        ]);

        $this->assertEquals(11, $vital->mews_score);
        $this->assertTrue($vital->mews_classification['is_emergency']);
    }
}
