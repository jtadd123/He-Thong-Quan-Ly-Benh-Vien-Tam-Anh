<?php

namespace Tests\Feature;

use App\Services\AllergyCrossReactionService;
use Tests\TestCase;

class AllergyCrossReactionTest extends TestCase
{
    public function test_detects_cross_allergy_penicillin_and_cephalosporin(): void
    {
        $patientAllergies = 'Dị ứng nặng với Penicillin tiêm, từng nổi mề đay khó thở';
        $medications = [
            ['name' => 'Cefalexin 500mg', 'active_ingredient' => 'Cefalexin'],
            ['name' => 'Paracetamol 500mg', 'active_ingredient' => 'Paracetamol'],
        ];

        $result = AllergyCrossReactionService::checkAllergies($patientAllergies, $medications);

        $this->assertTrue($result['has_critical_allergy']);
        $this->assertGreaterThanOrEqual(1, $result['alerts_count']);
        $this->assertStringContainsString('dị ứng chéo Cephalosporin', $result['alerts'][0]['title']);
        $this->assertEquals('danger', $result['alerts'][0]['severity']);
    }

    public function test_detects_cross_allergy_aspirin_and_nsaid(): void
    {
        $patientAllergies = 'Aspirin, dị ứng thức ăn hải sản';
        $medications = [
            ['name' => 'Ibuprofen 400mg', 'active_ingredient' => 'Ibuprofen'],
        ];

        $result = AllergyCrossReactionService::checkAllergies($patientAllergies, $medications);

        $this->assertTrue($result['has_critical_allergy']);
        $this->assertStringContainsString('NSAID', $result['alerts'][0]['title']);
        $this->assertStringContainsString('co thắt phế quản', $result['alerts'][0]['explanation']);
    }

    public function test_detects_cross_allergy_sulfonamide_and_sulfonylurea(): void
    {
        $patientAllergies = 'Bactrim, Sulfamethoxazole';
        $medications = [
            ['name' => 'Diamicron MR 30mg', 'active_ingredient' => 'Gliclazide'],
            ['name' => 'Furosemid 40mg', 'active_ingredient' => 'Furosemide'],
        ];

        $result = AllergyCrossReactionService::checkAllergies($patientAllergies, $medications);

        $this->assertTrue($result['has_critical_allergy']);
        $this->assertStringContainsString('Sulfonamide', $result['alerts'][0]['title']);
    }

    public function test_direct_paracetamol_allergy_alert(): void
    {
        $patientAllergies = 'Paracetamol, Panadol';
        $medications = [
            ['name' => 'Efferalgan 500mg', 'active_ingredient' => 'Paracetamol'],
        ];

        $result = AllergyCrossReactionService::checkAllergies($patientAllergies, $medications);

        $this->assertTrue($result['has_critical_allergy']);
        $this->assertStringContainsString('Dị ứng trực tiếp', $result['alerts'][0]['title']);
    }

    public function test_safe_patient_and_medications_produce_no_alerts(): void
    {
        $patientAllergies = 'Không có tiền sử dị ứng thuốc';
        $medications = [
            ['name' => 'Amoxicillin 500mg', 'active_ingredient' => 'Amoxicillin'],
            ['name' => 'Paracetamol 500mg', 'active_ingredient' => 'Paracetamol'],
        ];

        $result = AllergyCrossReactionService::checkAllergies($patientAllergies, $medications);

        $this->assertFalse($result['has_critical_allergy']);
        $this->assertEquals(0, $result['alerts_count']);
        $this->assertEmpty($result['alerts']);
    }
}
