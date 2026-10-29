<?php

namespace Tests\Feature;

use App\Services\DrugInteractionService;
use Tests\TestCase;

class DrugInteractionTest extends TestCase
{
    public function test_detects_severe_aspirin_and_nsaid_interaction(): void
    {
        $meds = [
            'Aspirin 81mg',
            'Ibuprofen 400mg',
            'Paracetamol 500mg',
        ];

        $alerts = DrugInteractionService::analyze($meds);

        $this->assertNotEmpty($alerts);
        $this->assertEquals('danger', $alerts[0]['severity']);
        $this->assertStringContainsString('xuất huyết tiêu hóa', $alerts[0]['title']);
    }

    public function test_detects_paracetamol_duplicate_overdose_risk(): void
    {
        $meds = [
            'Panadol Extra 500mg',
            'Efferalgan Codein 500mg/30mg',
        ];

        $alerts = DrugInteractionService::analyze($meds);

        $this->assertNotEmpty($alerts);
        $this->assertEquals('danger', $alerts[0]['severity']);
        $this->assertStringContainsString('Trùng lặp hoạt chất Paracetamol', $alerts[0]['title']);
    }

    public function test_safe_prescriptions_produce_zero_alerts(): void
    {
        $meds = [
            'Augmentin 1g',
            'Paracetamol 500mg',
            'Berberin 100mg',
        ];

        $alerts = DrugInteractionService::analyze($meds);

        $this->assertEmpty($alerts);
    }
}
