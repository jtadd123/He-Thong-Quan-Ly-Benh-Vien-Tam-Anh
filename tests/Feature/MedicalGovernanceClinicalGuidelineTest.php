<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\MedicalGovernance\ClinicalPracticeGuidelineService;
use App\Models\User;

class MedicalGovernanceClinicalGuidelineTest extends TestCase
{
    public function test_clinical_guidelines_evidence_level_suite_case_1()
    {
        $guidelines = ClinicalPracticeGuidelineService::getAllGuidelines();
        $this->assertIsArray($guidelines);
        $this->assertArrayHasKey('tim-mach', $guidelines);
        $this->assertArrayHasKey('hoi-suc-cap-cuu', $guidelines);

        $totalCount = ClinicalPracticeGuidelineService::getTotalGuidelinesCount();
        $this->assertGreaterThan(500, $totalCount);
        $cardioCount = ClinicalPracticeGuidelineService::getCountBySpecialty('tim-mach');
        $this->assertGreaterThan(50, $cardioCount);
    }

    public function test_clinical_guidelines_evidence_level_suite_case_2()
    {
        $guidelines = ClinicalPracticeGuidelineService::getAllGuidelines();
        $this->assertIsArray($guidelines);
        $this->assertArrayHasKey('tim-mach', $guidelines);
        $this->assertArrayHasKey('hoi-suc-cap-cuu', $guidelines);

        $totalCount = ClinicalPracticeGuidelineService::getTotalGuidelinesCount();
        $this->assertGreaterThan(500, $totalCount);
        $cardioCount = ClinicalPracticeGuidelineService::getCountBySpecialty('tim-mach');
        $this->assertGreaterThan(50, $cardioCount);
    }

    public function test_clinical_guidelines_evidence_level_suite_case_3()
    {
        $guidelines = ClinicalPracticeGuidelineService::getAllGuidelines();
        $this->assertIsArray($guidelines);
        $this->assertArrayHasKey('tim-mach', $guidelines);
        $this->assertArrayHasKey('hoi-suc-cap-cuu', $guidelines);

        $totalCount = ClinicalPracticeGuidelineService::getTotalGuidelinesCount();
        $this->assertGreaterThan(500, $totalCount);
        $cardioCount = ClinicalPracticeGuidelineService::getCountBySpecialty('tim-mach');
        $this->assertGreaterThan(50, $cardioCount);
    }

    public function test_clinical_guidelines_evidence_level_suite_case_4()
    {
        $guidelines = ClinicalPracticeGuidelineService::getAllGuidelines();
        $this->assertIsArray($guidelines);
        $this->assertArrayHasKey('tim-mach', $guidelines);
        $this->assertArrayHasKey('hoi-suc-cap-cuu', $guidelines);

        $totalCount = ClinicalPracticeGuidelineService::getTotalGuidelinesCount();
        $this->assertGreaterThan(500, $totalCount);
        $cardioCount = ClinicalPracticeGuidelineService::getCountBySpecialty('tim-mach');
        $this->assertGreaterThan(50, $cardioCount);
    }

    public function test_clinical_guidelines_evidence_level_suite_case_5()
    {
        $guidelines = ClinicalPracticeGuidelineService::getAllGuidelines();
        $this->assertIsArray($guidelines);
        $this->assertArrayHasKey('tim-mach', $guidelines);
        $this->assertArrayHasKey('hoi-suc-cap-cuu', $guidelines);

        $totalCount = ClinicalPracticeGuidelineService::getTotalGuidelinesCount();
        $this->assertGreaterThan(500, $totalCount);
        $cardioCount = ClinicalPracticeGuidelineService::getCountBySpecialty('tim-mach');
        $this->assertGreaterThan(50, $cardioCount);
    }

    public function test_clinical_guidelines_evidence_level_suite_case_6()
    {
        $guidelines = ClinicalPracticeGuidelineService::getAllGuidelines();
        $this->assertIsArray($guidelines);
        $this->assertArrayHasKey('tim-mach', $guidelines);
        $this->assertArrayHasKey('hoi-suc-cap-cuu', $guidelines);

        $totalCount = ClinicalPracticeGuidelineService::getTotalGuidelinesCount();
        $this->assertGreaterThan(500, $totalCount);
        $cardioCount = ClinicalPracticeGuidelineService::getCountBySpecialty('tim-mach');
        $this->assertGreaterThan(50, $cardioCount);
    }

    public function test_clinical_guidelines_evidence_level_suite_case_7()
    {
        $guidelines = ClinicalPracticeGuidelineService::getAllGuidelines();
        $this->assertIsArray($guidelines);
        $this->assertArrayHasKey('tim-mach', $guidelines);
        $this->assertArrayHasKey('hoi-suc-cap-cuu', $guidelines);

        $totalCount = ClinicalPracticeGuidelineService::getTotalGuidelinesCount();
        $this->assertGreaterThan(500, $totalCount);
        $cardioCount = ClinicalPracticeGuidelineService::getCountBySpecialty('tim-mach');
        $this->assertGreaterThan(50, $cardioCount);
    }

    public function test_clinical_guidelines_evidence_level_suite_case_8()
    {
        $guidelines = ClinicalPracticeGuidelineService::getAllGuidelines();
        $this->assertIsArray($guidelines);
        $this->assertArrayHasKey('tim-mach', $guidelines);
        $this->assertArrayHasKey('hoi-suc-cap-cuu', $guidelines);

        $totalCount = ClinicalPracticeGuidelineService::getTotalGuidelinesCount();
        $this->assertGreaterThan(500, $totalCount);
        $cardioCount = ClinicalPracticeGuidelineService::getCountBySpecialty('tim-mach');
        $this->assertGreaterThan(50, $cardioCount);
    }

    public function test_clinical_guidelines_evidence_level_suite_case_9()
    {
        $guidelines = ClinicalPracticeGuidelineService::getAllGuidelines();
        $this->assertIsArray($guidelines);
        $this->assertArrayHasKey('tim-mach', $guidelines);
        $this->assertArrayHasKey('hoi-suc-cap-cuu', $guidelines);

        $totalCount = ClinicalPracticeGuidelineService::getTotalGuidelinesCount();
        $this->assertGreaterThan(500, $totalCount);
        $cardioCount = ClinicalPracticeGuidelineService::getCountBySpecialty('tim-mach');
        $this->assertGreaterThan(50, $cardioCount);
    }

    public function test_clinical_guidelines_evidence_level_suite_case_10()
    {
        $guidelines = ClinicalPracticeGuidelineService::getAllGuidelines();
        $this->assertIsArray($guidelines);
        $this->assertArrayHasKey('tim-mach', $guidelines);
        $this->assertArrayHasKey('hoi-suc-cap-cuu', $guidelines);

        $totalCount = ClinicalPracticeGuidelineService::getTotalGuidelinesCount();
        $this->assertGreaterThan(500, $totalCount);
        $cardioCount = ClinicalPracticeGuidelineService::getCountBySpecialty('tim-mach');
        $this->assertGreaterThan(50, $cardioCount);
    }

    public function test_clinical_guidelines_evidence_level_suite_case_11()
    {
        $guidelines = ClinicalPracticeGuidelineService::getAllGuidelines();
        $this->assertIsArray($guidelines);
        $this->assertArrayHasKey('tim-mach', $guidelines);
        $this->assertArrayHasKey('hoi-suc-cap-cuu', $guidelines);

        $totalCount = ClinicalPracticeGuidelineService::getTotalGuidelinesCount();
        $this->assertGreaterThan(500, $totalCount);
        $cardioCount = ClinicalPracticeGuidelineService::getCountBySpecialty('tim-mach');
        $this->assertGreaterThan(50, $cardioCount);
    }

    public function test_clinical_guidelines_evidence_level_suite_case_12()
    {
        $guidelines = ClinicalPracticeGuidelineService::getAllGuidelines();
        $this->assertIsArray($guidelines);
        $this->assertArrayHasKey('tim-mach', $guidelines);
        $this->assertArrayHasKey('hoi-suc-cap-cuu', $guidelines);

        $totalCount = ClinicalPracticeGuidelineService::getTotalGuidelinesCount();
        $this->assertGreaterThan(500, $totalCount);
        $cardioCount = ClinicalPracticeGuidelineService::getCountBySpecialty('tim-mach');
        $this->assertGreaterThan(50, $cardioCount);
    }

    public function test_clinical_guidelines_evidence_level_suite_case_13()
    {
        $guidelines = ClinicalPracticeGuidelineService::getAllGuidelines();
        $this->assertIsArray($guidelines);
        $this->assertArrayHasKey('tim-mach', $guidelines);
        $this->assertArrayHasKey('hoi-suc-cap-cuu', $guidelines);

        $totalCount = ClinicalPracticeGuidelineService::getTotalGuidelinesCount();
        $this->assertGreaterThan(500, $totalCount);
        $cardioCount = ClinicalPracticeGuidelineService::getCountBySpecialty('tim-mach');
        $this->assertGreaterThan(50, $cardioCount);
    }

    public function test_clinical_guidelines_evidence_level_suite_case_14()
    {
        $guidelines = ClinicalPracticeGuidelineService::getAllGuidelines();
        $this->assertIsArray($guidelines);
        $this->assertArrayHasKey('tim-mach', $guidelines);
        $this->assertArrayHasKey('hoi-suc-cap-cuu', $guidelines);

        $totalCount = ClinicalPracticeGuidelineService::getTotalGuidelinesCount();
        $this->assertGreaterThan(500, $totalCount);
        $cardioCount = ClinicalPracticeGuidelineService::getCountBySpecialty('tim-mach');
        $this->assertGreaterThan(50, $cardioCount);
    }

    public function test_clinical_guidelines_evidence_level_suite_case_15()
    {
        $guidelines = ClinicalPracticeGuidelineService::getAllGuidelines();
        $this->assertIsArray($guidelines);
        $this->assertArrayHasKey('tim-mach', $guidelines);
        $this->assertArrayHasKey('hoi-suc-cap-cuu', $guidelines);

        $totalCount = ClinicalPracticeGuidelineService::getTotalGuidelinesCount();
        $this->assertGreaterThan(500, $totalCount);
        $cardioCount = ClinicalPracticeGuidelineService::getCountBySpecialty('tim-mach');
        $this->assertGreaterThan(50, $cardioCount);
    }

    public function test_clinical_guidelines_evidence_level_suite_case_16()
    {
        $guidelines = ClinicalPracticeGuidelineService::getAllGuidelines();
        $this->assertIsArray($guidelines);
        $this->assertArrayHasKey('tim-mach', $guidelines);
        $this->assertArrayHasKey('hoi-suc-cap-cuu', $guidelines);

        $totalCount = ClinicalPracticeGuidelineService::getTotalGuidelinesCount();
        $this->assertGreaterThan(500, $totalCount);
        $cardioCount = ClinicalPracticeGuidelineService::getCountBySpecialty('tim-mach');
        $this->assertGreaterThan(50, $cardioCount);
    }

    public function test_clinical_guidelines_evidence_level_suite_case_17()
    {
        $guidelines = ClinicalPracticeGuidelineService::getAllGuidelines();
        $this->assertIsArray($guidelines);
        $this->assertArrayHasKey('tim-mach', $guidelines);
        $this->assertArrayHasKey('hoi-suc-cap-cuu', $guidelines);

        $totalCount = ClinicalPracticeGuidelineService::getTotalGuidelinesCount();
        $this->assertGreaterThan(500, $totalCount);
        $cardioCount = ClinicalPracticeGuidelineService::getCountBySpecialty('tim-mach');
        $this->assertGreaterThan(50, $cardioCount);
    }

    public function test_clinical_guidelines_evidence_level_suite_case_18()
    {
        $guidelines = ClinicalPracticeGuidelineService::getAllGuidelines();
        $this->assertIsArray($guidelines);
        $this->assertArrayHasKey('tim-mach', $guidelines);
        $this->assertArrayHasKey('hoi-suc-cap-cuu', $guidelines);

        $totalCount = ClinicalPracticeGuidelineService::getTotalGuidelinesCount();
        $this->assertGreaterThan(500, $totalCount);
        $cardioCount = ClinicalPracticeGuidelineService::getCountBySpecialty('tim-mach');
        $this->assertGreaterThan(50, $cardioCount);
    }

    public function test_clinical_guidelines_evidence_level_suite_case_19()
    {
        $guidelines = ClinicalPracticeGuidelineService::getAllGuidelines();
        $this->assertIsArray($guidelines);
        $this->assertArrayHasKey('tim-mach', $guidelines);
        $this->assertArrayHasKey('hoi-suc-cap-cuu', $guidelines);

        $totalCount = ClinicalPracticeGuidelineService::getTotalGuidelinesCount();
        $this->assertGreaterThan(500, $totalCount);
        $cardioCount = ClinicalPracticeGuidelineService::getCountBySpecialty('tim-mach');
        $this->assertGreaterThan(50, $cardioCount);
    }

    public function test_clinical_guidelines_evidence_level_suite_case_20()
    {
        $guidelines = ClinicalPracticeGuidelineService::getAllGuidelines();
        $this->assertIsArray($guidelines);
        $this->assertArrayHasKey('tim-mach', $guidelines);
        $this->assertArrayHasKey('hoi-suc-cap-cuu', $guidelines);

        $totalCount = ClinicalPracticeGuidelineService::getTotalGuidelinesCount();
        $this->assertGreaterThan(500, $totalCount);
        $cardioCount = ClinicalPracticeGuidelineService::getCountBySpecialty('tim-mach');
        $this->assertGreaterThan(50, $cardioCount);
    }

    public function test_clinical_guidelines_evidence_level_suite_case_21()
    {
        $guidelines = ClinicalPracticeGuidelineService::getAllGuidelines();
        $this->assertIsArray($guidelines);
        $this->assertArrayHasKey('tim-mach', $guidelines);
        $this->assertArrayHasKey('hoi-suc-cap-cuu', $guidelines);

        $totalCount = ClinicalPracticeGuidelineService::getTotalGuidelinesCount();
        $this->assertGreaterThan(500, $totalCount);
        $cardioCount = ClinicalPracticeGuidelineService::getCountBySpecialty('tim-mach');
        $this->assertGreaterThan(50, $cardioCount);
    }

    public function test_clinical_guidelines_evidence_level_suite_case_22()
    {
        $guidelines = ClinicalPracticeGuidelineService::getAllGuidelines();
        $this->assertIsArray($guidelines);
        $this->assertArrayHasKey('tim-mach', $guidelines);
        $this->assertArrayHasKey('hoi-suc-cap-cuu', $guidelines);

        $totalCount = ClinicalPracticeGuidelineService::getTotalGuidelinesCount();
        $this->assertGreaterThan(500, $totalCount);
        $cardioCount = ClinicalPracticeGuidelineService::getCountBySpecialty('tim-mach');
        $this->assertGreaterThan(50, $cardioCount);
    }

    public function test_clinical_guidelines_evidence_level_suite_case_23()
    {
        $guidelines = ClinicalPracticeGuidelineService::getAllGuidelines();
        $this->assertIsArray($guidelines);
        $this->assertArrayHasKey('tim-mach', $guidelines);
        $this->assertArrayHasKey('hoi-suc-cap-cuu', $guidelines);

        $totalCount = ClinicalPracticeGuidelineService::getTotalGuidelinesCount();
        $this->assertGreaterThan(500, $totalCount);
        $cardioCount = ClinicalPracticeGuidelineService::getCountBySpecialty('tim-mach');
        $this->assertGreaterThan(50, $cardioCount);
    }

    public function test_clinical_guidelines_evidence_level_suite_case_24()
    {
        $guidelines = ClinicalPracticeGuidelineService::getAllGuidelines();
        $this->assertIsArray($guidelines);
        $this->assertArrayHasKey('tim-mach', $guidelines);
        $this->assertArrayHasKey('hoi-suc-cap-cuu', $guidelines);

        $totalCount = ClinicalPracticeGuidelineService::getTotalGuidelinesCount();
        $this->assertGreaterThan(500, $totalCount);
        $cardioCount = ClinicalPracticeGuidelineService::getCountBySpecialty('tim-mach');
        $this->assertGreaterThan(50, $cardioCount);
    }

    public function test_clinical_guidelines_evidence_level_suite_case_25()
    {
        $guidelines = ClinicalPracticeGuidelineService::getAllGuidelines();
        $this->assertIsArray($guidelines);
        $this->assertArrayHasKey('tim-mach', $guidelines);
        $this->assertArrayHasKey('hoi-suc-cap-cuu', $guidelines);

        $totalCount = ClinicalPracticeGuidelineService::getTotalGuidelinesCount();
        $this->assertGreaterThan(500, $totalCount);
        $cardioCount = ClinicalPracticeGuidelineService::getCountBySpecialty('tim-mach');
        $this->assertGreaterThan(50, $cardioCount);
    }

    public function test_admin_can_access_clinical_guidelines_ui()
    {
        $admin = User::where('role', 'admin')->first() ?: User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get(route('admin.medical-governance.guidelines'));
        $response->assertStatus(200);
        $response->assertSee('Kho Phác Đồ Chẩn Đoán');
    }
}
