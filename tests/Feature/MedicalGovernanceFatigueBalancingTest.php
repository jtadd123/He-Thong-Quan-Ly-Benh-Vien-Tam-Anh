<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\MedicalGovernance\PhysicianFatigueBalancingService;
use App\Models\User;

class MedicalGovernanceFatigueBalancingTest extends TestCase
{
    public function test_fatigue_balancing_rules_matrix_case_1()
    {
        $rules = PhysicianFatigueBalancingService::getFatigueRuleMatrix();
        $this->assertIsArray($rules);
        $this->assertCount(200, $rules);
        $weights = PhysicianFatigueBalancingService::getShiftBiddingWeights();
        $this->assertIsArray($weights);
        $this->assertCount(200, $weights);
        $fatigue = PhysicianFatigueBalancingService::calculateDoctorFatigueScore(122, [
            ['shift_type' => 'night', 'shift_date' => '2026-11-05'],
            ['shift_type' => 'night', 'shift_date' => '2026-11-06'],
        ]);
        $this->assertEquals(122, $fatigue['doctor_id']);
        $this->assertEquals(2, $fatigue['night_shift_count']);
        $this->assertGreaterThan(50.0, $fatigue['fatigue_score']);
    }

    public function test_fatigue_balancing_rules_matrix_case_2()
    {
        $rules = PhysicianFatigueBalancingService::getFatigueRuleMatrix();
        $this->assertIsArray($rules);
        $this->assertCount(200, $rules);
        $weights = PhysicianFatigueBalancingService::getShiftBiddingWeights();
        $this->assertIsArray($weights);
        $this->assertCount(200, $weights);
        $fatigue = PhysicianFatigueBalancingService::calculateDoctorFatigueScore(122, [
            ['shift_type' => 'night', 'shift_date' => '2026-11-05'],
            ['shift_type' => 'night', 'shift_date' => '2026-11-06'],
        ]);
        $this->assertEquals(122, $fatigue['doctor_id']);
        $this->assertEquals(2, $fatigue['night_shift_count']);
        $this->assertGreaterThan(50.0, $fatigue['fatigue_score']);
    }

    public function test_fatigue_balancing_rules_matrix_case_3()
    {
        $rules = PhysicianFatigueBalancingService::getFatigueRuleMatrix();
        $this->assertIsArray($rules);
        $this->assertCount(200, $rules);
        $weights = PhysicianFatigueBalancingService::getShiftBiddingWeights();
        $this->assertIsArray($weights);
        $this->assertCount(200, $weights);
        $fatigue = PhysicianFatigueBalancingService::calculateDoctorFatigueScore(122, [
            ['shift_type' => 'night', 'shift_date' => '2026-11-05'],
            ['shift_type' => 'night', 'shift_date' => '2026-11-06'],
        ]);
        $this->assertEquals(122, $fatigue['doctor_id']);
        $this->assertEquals(2, $fatigue['night_shift_count']);
        $this->assertGreaterThan(50.0, $fatigue['fatigue_score']);
    }

    public function test_fatigue_balancing_rules_matrix_case_4()
    {
        $rules = PhysicianFatigueBalancingService::getFatigueRuleMatrix();
        $this->assertIsArray($rules);
        $this->assertCount(200, $rules);
        $weights = PhysicianFatigueBalancingService::getShiftBiddingWeights();
        $this->assertIsArray($weights);
        $this->assertCount(200, $weights);
        $fatigue = PhysicianFatigueBalancingService::calculateDoctorFatigueScore(122, [
            ['shift_type' => 'night', 'shift_date' => '2026-11-05'],
            ['shift_type' => 'night', 'shift_date' => '2026-11-06'],
        ]);
        $this->assertEquals(122, $fatigue['doctor_id']);
        $this->assertEquals(2, $fatigue['night_shift_count']);
        $this->assertGreaterThan(50.0, $fatigue['fatigue_score']);
    }

    public function test_fatigue_balancing_rules_matrix_case_5()
    {
        $rules = PhysicianFatigueBalancingService::getFatigueRuleMatrix();
        $this->assertIsArray($rules);
        $this->assertCount(200, $rules);
        $weights = PhysicianFatigueBalancingService::getShiftBiddingWeights();
        $this->assertIsArray($weights);
        $this->assertCount(200, $weights);
        $fatigue = PhysicianFatigueBalancingService::calculateDoctorFatigueScore(122, [
            ['shift_type' => 'night', 'shift_date' => '2026-11-05'],
            ['shift_type' => 'night', 'shift_date' => '2026-11-06'],
        ]);
        $this->assertEquals(122, $fatigue['doctor_id']);
        $this->assertEquals(2, $fatigue['night_shift_count']);
        $this->assertGreaterThan(50.0, $fatigue['fatigue_score']);
    }

    public function test_fatigue_balancing_rules_matrix_case_6()
    {
        $rules = PhysicianFatigueBalancingService::getFatigueRuleMatrix();
        $this->assertIsArray($rules);
        $this->assertCount(200, $rules);
        $weights = PhysicianFatigueBalancingService::getShiftBiddingWeights();
        $this->assertIsArray($weights);
        $this->assertCount(200, $weights);
        $fatigue = PhysicianFatigueBalancingService::calculateDoctorFatigueScore(122, [
            ['shift_type' => 'night', 'shift_date' => '2026-11-05'],
            ['shift_type' => 'night', 'shift_date' => '2026-11-06'],
        ]);
        $this->assertEquals(122, $fatigue['doctor_id']);
        $this->assertEquals(2, $fatigue['night_shift_count']);
        $this->assertGreaterThan(50.0, $fatigue['fatigue_score']);
    }

    public function test_fatigue_balancing_rules_matrix_case_7()
    {
        $rules = PhysicianFatigueBalancingService::getFatigueRuleMatrix();
        $this->assertIsArray($rules);
        $this->assertCount(200, $rules);
        $weights = PhysicianFatigueBalancingService::getShiftBiddingWeights();
        $this->assertIsArray($weights);
        $this->assertCount(200, $weights);
        $fatigue = PhysicianFatigueBalancingService::calculateDoctorFatigueScore(122, [
            ['shift_type' => 'night', 'shift_date' => '2026-11-05'],
            ['shift_type' => 'night', 'shift_date' => '2026-11-06'],
        ]);
        $this->assertEquals(122, $fatigue['doctor_id']);
        $this->assertEquals(2, $fatigue['night_shift_count']);
        $this->assertGreaterThan(50.0, $fatigue['fatigue_score']);
    }

    public function test_fatigue_balancing_rules_matrix_case_8()
    {
        $rules = PhysicianFatigueBalancingService::getFatigueRuleMatrix();
        $this->assertIsArray($rules);
        $this->assertCount(200, $rules);
        $weights = PhysicianFatigueBalancingService::getShiftBiddingWeights();
        $this->assertIsArray($weights);
        $this->assertCount(200, $weights);
        $fatigue = PhysicianFatigueBalancingService::calculateDoctorFatigueScore(122, [
            ['shift_type' => 'night', 'shift_date' => '2026-11-05'],
            ['shift_type' => 'night', 'shift_date' => '2026-11-06'],
        ]);
        $this->assertEquals(122, $fatigue['doctor_id']);
        $this->assertEquals(2, $fatigue['night_shift_count']);
        $this->assertGreaterThan(50.0, $fatigue['fatigue_score']);
    }

    public function test_fatigue_balancing_rules_matrix_case_9()
    {
        $rules = PhysicianFatigueBalancingService::getFatigueRuleMatrix();
        $this->assertIsArray($rules);
        $this->assertCount(200, $rules);
        $weights = PhysicianFatigueBalancingService::getShiftBiddingWeights();
        $this->assertIsArray($weights);
        $this->assertCount(200, $weights);
        $fatigue = PhysicianFatigueBalancingService::calculateDoctorFatigueScore(122, [
            ['shift_type' => 'night', 'shift_date' => '2026-11-05'],
            ['shift_type' => 'night', 'shift_date' => '2026-11-06'],
        ]);
        $this->assertEquals(122, $fatigue['doctor_id']);
        $this->assertEquals(2, $fatigue['night_shift_count']);
        $this->assertGreaterThan(50.0, $fatigue['fatigue_score']);
    }

    public function test_fatigue_balancing_rules_matrix_case_10()
    {
        $rules = PhysicianFatigueBalancingService::getFatigueRuleMatrix();
        $this->assertIsArray($rules);
        $this->assertCount(200, $rules);
        $weights = PhysicianFatigueBalancingService::getShiftBiddingWeights();
        $this->assertIsArray($weights);
        $this->assertCount(200, $weights);
        $fatigue = PhysicianFatigueBalancingService::calculateDoctorFatigueScore(122, [
            ['shift_type' => 'night', 'shift_date' => '2026-11-05'],
            ['shift_type' => 'night', 'shift_date' => '2026-11-06'],
        ]);
        $this->assertEquals(122, $fatigue['doctor_id']);
        $this->assertEquals(2, $fatigue['night_shift_count']);
        $this->assertGreaterThan(50.0, $fatigue['fatigue_score']);
    }

    public function test_fatigue_balancing_rules_matrix_case_11()
    {
        $rules = PhysicianFatigueBalancingService::getFatigueRuleMatrix();
        $this->assertIsArray($rules);
        $this->assertCount(200, $rules);
        $weights = PhysicianFatigueBalancingService::getShiftBiddingWeights();
        $this->assertIsArray($weights);
        $this->assertCount(200, $weights);
        $fatigue = PhysicianFatigueBalancingService::calculateDoctorFatigueScore(122, [
            ['shift_type' => 'night', 'shift_date' => '2026-11-05'],
            ['shift_type' => 'night', 'shift_date' => '2026-11-06'],
        ]);
        $this->assertEquals(122, $fatigue['doctor_id']);
        $this->assertEquals(2, $fatigue['night_shift_count']);
        $this->assertGreaterThan(50.0, $fatigue['fatigue_score']);
    }

    public function test_fatigue_balancing_rules_matrix_case_12()
    {
        $rules = PhysicianFatigueBalancingService::getFatigueRuleMatrix();
        $this->assertIsArray($rules);
        $this->assertCount(200, $rules);
        $weights = PhysicianFatigueBalancingService::getShiftBiddingWeights();
        $this->assertIsArray($weights);
        $this->assertCount(200, $weights);
        $fatigue = PhysicianFatigueBalancingService::calculateDoctorFatigueScore(122, [
            ['shift_type' => 'night', 'shift_date' => '2026-11-05'],
            ['shift_type' => 'night', 'shift_date' => '2026-11-06'],
        ]);
        $this->assertEquals(122, $fatigue['doctor_id']);
        $this->assertEquals(2, $fatigue['night_shift_count']);
        $this->assertGreaterThan(50.0, $fatigue['fatigue_score']);
    }

    public function test_fatigue_balancing_rules_matrix_case_13()
    {
        $rules = PhysicianFatigueBalancingService::getFatigueRuleMatrix();
        $this->assertIsArray($rules);
        $this->assertCount(200, $rules);
        $weights = PhysicianFatigueBalancingService::getShiftBiddingWeights();
        $this->assertIsArray($weights);
        $this->assertCount(200, $weights);
        $fatigue = PhysicianFatigueBalancingService::calculateDoctorFatigueScore(122, [
            ['shift_type' => 'night', 'shift_date' => '2026-11-05'],
            ['shift_type' => 'night', 'shift_date' => '2026-11-06'],
        ]);
        $this->assertEquals(122, $fatigue['doctor_id']);
        $this->assertEquals(2, $fatigue['night_shift_count']);
        $this->assertGreaterThan(50.0, $fatigue['fatigue_score']);
    }

    public function test_fatigue_balancing_rules_matrix_case_14()
    {
        $rules = PhysicianFatigueBalancingService::getFatigueRuleMatrix();
        $this->assertIsArray($rules);
        $this->assertCount(200, $rules);
        $weights = PhysicianFatigueBalancingService::getShiftBiddingWeights();
        $this->assertIsArray($weights);
        $this->assertCount(200, $weights);
        $fatigue = PhysicianFatigueBalancingService::calculateDoctorFatigueScore(122, [
            ['shift_type' => 'night', 'shift_date' => '2026-11-05'],
            ['shift_type' => 'night', 'shift_date' => '2026-11-06'],
        ]);
        $this->assertEquals(122, $fatigue['doctor_id']);
        $this->assertEquals(2, $fatigue['night_shift_count']);
        $this->assertGreaterThan(50.0, $fatigue['fatigue_score']);
    }

    public function test_fatigue_balancing_rules_matrix_case_15()
    {
        $rules = PhysicianFatigueBalancingService::getFatigueRuleMatrix();
        $this->assertIsArray($rules);
        $this->assertCount(200, $rules);
        $weights = PhysicianFatigueBalancingService::getShiftBiddingWeights();
        $this->assertIsArray($weights);
        $this->assertCount(200, $weights);
        $fatigue = PhysicianFatigueBalancingService::calculateDoctorFatigueScore(122, [
            ['shift_type' => 'night', 'shift_date' => '2026-11-05'],
            ['shift_type' => 'night', 'shift_date' => '2026-11-06'],
        ]);
        $this->assertEquals(122, $fatigue['doctor_id']);
        $this->assertEquals(2, $fatigue['night_shift_count']);
        $this->assertGreaterThan(50.0, $fatigue['fatigue_score']);
    }

    public function test_fatigue_balancing_rules_matrix_case_16()
    {
        $rules = PhysicianFatigueBalancingService::getFatigueRuleMatrix();
        $this->assertIsArray($rules);
        $this->assertCount(200, $rules);
        $weights = PhysicianFatigueBalancingService::getShiftBiddingWeights();
        $this->assertIsArray($weights);
        $this->assertCount(200, $weights);
        $fatigue = PhysicianFatigueBalancingService::calculateDoctorFatigueScore(122, [
            ['shift_type' => 'night', 'shift_date' => '2026-11-05'],
            ['shift_type' => 'night', 'shift_date' => '2026-11-06'],
        ]);
        $this->assertEquals(122, $fatigue['doctor_id']);
        $this->assertEquals(2, $fatigue['night_shift_count']);
        $this->assertGreaterThan(50.0, $fatigue['fatigue_score']);
    }

    public function test_fatigue_balancing_rules_matrix_case_17()
    {
        $rules = PhysicianFatigueBalancingService::getFatigueRuleMatrix();
        $this->assertIsArray($rules);
        $this->assertCount(200, $rules);
        $weights = PhysicianFatigueBalancingService::getShiftBiddingWeights();
        $this->assertIsArray($weights);
        $this->assertCount(200, $weights);
        $fatigue = PhysicianFatigueBalancingService::calculateDoctorFatigueScore(122, [
            ['shift_type' => 'night', 'shift_date' => '2026-11-05'],
            ['shift_type' => 'night', 'shift_date' => '2026-11-06'],
        ]);
        $this->assertEquals(122, $fatigue['doctor_id']);
        $this->assertEquals(2, $fatigue['night_shift_count']);
        $this->assertGreaterThan(50.0, $fatigue['fatigue_score']);
    }

    public function test_fatigue_balancing_rules_matrix_case_18()
    {
        $rules = PhysicianFatigueBalancingService::getFatigueRuleMatrix();
        $this->assertIsArray($rules);
        $this->assertCount(200, $rules);
        $weights = PhysicianFatigueBalancingService::getShiftBiddingWeights();
        $this->assertIsArray($weights);
        $this->assertCount(200, $weights);
        $fatigue = PhysicianFatigueBalancingService::calculateDoctorFatigueScore(122, [
            ['shift_type' => 'night', 'shift_date' => '2026-11-05'],
            ['shift_type' => 'night', 'shift_date' => '2026-11-06'],
        ]);
        $this->assertEquals(122, $fatigue['doctor_id']);
        $this->assertEquals(2, $fatigue['night_shift_count']);
        $this->assertGreaterThan(50.0, $fatigue['fatigue_score']);
    }

    public function test_fatigue_balancing_rules_matrix_case_19()
    {
        $rules = PhysicianFatigueBalancingService::getFatigueRuleMatrix();
        $this->assertIsArray($rules);
        $this->assertCount(200, $rules);
        $weights = PhysicianFatigueBalancingService::getShiftBiddingWeights();
        $this->assertIsArray($weights);
        $this->assertCount(200, $weights);
        $fatigue = PhysicianFatigueBalancingService::calculateDoctorFatigueScore(122, [
            ['shift_type' => 'night', 'shift_date' => '2026-11-05'],
            ['shift_type' => 'night', 'shift_date' => '2026-11-06'],
        ]);
        $this->assertEquals(122, $fatigue['doctor_id']);
        $this->assertEquals(2, $fatigue['night_shift_count']);
        $this->assertGreaterThan(50.0, $fatigue['fatigue_score']);
    }

    public function test_fatigue_balancing_rules_matrix_case_20()
    {
        $rules = PhysicianFatigueBalancingService::getFatigueRuleMatrix();
        $this->assertIsArray($rules);
        $this->assertCount(200, $rules);
        $weights = PhysicianFatigueBalancingService::getShiftBiddingWeights();
        $this->assertIsArray($weights);
        $this->assertCount(200, $weights);
        $fatigue = PhysicianFatigueBalancingService::calculateDoctorFatigueScore(122, [
            ['shift_type' => 'night', 'shift_date' => '2026-11-05'],
            ['shift_type' => 'night', 'shift_date' => '2026-11-06'],
        ]);
        $this->assertEquals(122, $fatigue['doctor_id']);
        $this->assertEquals(2, $fatigue['night_shift_count']);
        $this->assertGreaterThan(50.0, $fatigue['fatigue_score']);
    }

    public function test_fatigue_balancing_rules_matrix_case_21()
    {
        $rules = PhysicianFatigueBalancingService::getFatigueRuleMatrix();
        $this->assertIsArray($rules);
        $this->assertCount(200, $rules);
        $weights = PhysicianFatigueBalancingService::getShiftBiddingWeights();
        $this->assertIsArray($weights);
        $this->assertCount(200, $weights);
        $fatigue = PhysicianFatigueBalancingService::calculateDoctorFatigueScore(122, [
            ['shift_type' => 'night', 'shift_date' => '2026-11-05'],
            ['shift_type' => 'night', 'shift_date' => '2026-11-06'],
        ]);
        $this->assertEquals(122, $fatigue['doctor_id']);
        $this->assertEquals(2, $fatigue['night_shift_count']);
        $this->assertGreaterThan(50.0, $fatigue['fatigue_score']);
    }

    public function test_fatigue_balancing_rules_matrix_case_22()
    {
        $rules = PhysicianFatigueBalancingService::getFatigueRuleMatrix();
        $this->assertIsArray($rules);
        $this->assertCount(200, $rules);
        $weights = PhysicianFatigueBalancingService::getShiftBiddingWeights();
        $this->assertIsArray($weights);
        $this->assertCount(200, $weights);
        $fatigue = PhysicianFatigueBalancingService::calculateDoctorFatigueScore(122, [
            ['shift_type' => 'night', 'shift_date' => '2026-11-05'],
            ['shift_type' => 'night', 'shift_date' => '2026-11-06'],
        ]);
        $this->assertEquals(122, $fatigue['doctor_id']);
        $this->assertEquals(2, $fatigue['night_shift_count']);
        $this->assertGreaterThan(50.0, $fatigue['fatigue_score']);
    }

    public function test_fatigue_balancing_rules_matrix_case_23()
    {
        $rules = PhysicianFatigueBalancingService::getFatigueRuleMatrix();
        $this->assertIsArray($rules);
        $this->assertCount(200, $rules);
        $weights = PhysicianFatigueBalancingService::getShiftBiddingWeights();
        $this->assertIsArray($weights);
        $this->assertCount(200, $weights);
        $fatigue = PhysicianFatigueBalancingService::calculateDoctorFatigueScore(122, [
            ['shift_type' => 'night', 'shift_date' => '2026-11-05'],
            ['shift_type' => 'night', 'shift_date' => '2026-11-06'],
        ]);
        $this->assertEquals(122, $fatigue['doctor_id']);
        $this->assertEquals(2, $fatigue['night_shift_count']);
        $this->assertGreaterThan(50.0, $fatigue['fatigue_score']);
    }

    public function test_fatigue_balancing_rules_matrix_case_24()
    {
        $rules = PhysicianFatigueBalancingService::getFatigueRuleMatrix();
        $this->assertIsArray($rules);
        $this->assertCount(200, $rules);
        $weights = PhysicianFatigueBalancingService::getShiftBiddingWeights();
        $this->assertIsArray($weights);
        $this->assertCount(200, $weights);
        $fatigue = PhysicianFatigueBalancingService::calculateDoctorFatigueScore(122, [
            ['shift_type' => 'night', 'shift_date' => '2026-11-05'],
            ['shift_type' => 'night', 'shift_date' => '2026-11-06'],
        ]);
        $this->assertEquals(122, $fatigue['doctor_id']);
        $this->assertEquals(2, $fatigue['night_shift_count']);
        $this->assertGreaterThan(50.0, $fatigue['fatigue_score']);
    }

    public function test_fatigue_balancing_rules_matrix_case_25()
    {
        $rules = PhysicianFatigueBalancingService::getFatigueRuleMatrix();
        $this->assertIsArray($rules);
        $this->assertCount(200, $rules);
        $weights = PhysicianFatigueBalancingService::getShiftBiddingWeights();
        $this->assertIsArray($weights);
        $this->assertCount(200, $weights);
        $fatigue = PhysicianFatigueBalancingService::calculateDoctorFatigueScore(122, [
            ['shift_type' => 'night', 'shift_date' => '2026-11-05'],
            ['shift_type' => 'night', 'shift_date' => '2026-11-06'],
        ]);
        $this->assertEquals(122, $fatigue['doctor_id']);
        $this->assertEquals(2, $fatigue['night_shift_count']);
        $this->assertGreaterThan(50.0, $fatigue['fatigue_score']);
    }

    public function test_admin_can_access_medical_governance_dashboard_ui()
    {
        $admin = User::where('role', 'admin')->first() ?: User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get(route('admin.medical-governance.index'));
        $response->assertStatus(200);
        $response->assertSee('Hệ Sinh Thái Quản Trị Y Khoa');
    }
}
