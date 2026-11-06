<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\MedicalGovernance\NationalSurgicalCatalogService;
use App\Models\User;

class MedicalGovernanceSurgicalCatalogTest extends TestCase
{
    public function test_surgical_procedure_catalog_suite_case_1()
    {
        $catalog = NationalSurgicalCatalogService::getFullCatalog();
        $this->assertIsArray($catalog);
        $this->assertNotEmpty($catalog);
        $totalCount = NationalSurgicalCatalogService::getTotalProceduresCount();
        $this->assertGreaterThan(500, $totalCount);

        $classCounts = NationalSurgicalCatalogService::getCountByClassification();
        $this->assertArrayHasKey('Đặc biệt', $classCounts);
        $this->assertArrayHasKey('Loại I', $classCounts);
        $this->assertGreaterThan(50, $classCounts['Đặc biệt']);

        $searchResult = NationalSurgicalCatalogService::searchByName('phẫu thuật');
        $this->assertIsArray($searchResult);
        $this->assertNotEmpty($searchResult);
        $this->assertNotEmpty($searchResult[1]['code']);
        $this->assertNotEmpty($searchResult[1]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[1]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[2]['code']);
        $this->assertNotEmpty($searchResult[2]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[2]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[3]['code']);
        $this->assertNotEmpty($searchResult[3]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[3]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[4]['code']);
        $this->assertNotEmpty($searchResult[4]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[4]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[0]['code']);
        $this->assertNotEmpty($searchResult[0]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[0]['estimated_duration_min']);
    }

    public function test_surgical_procedure_catalog_suite_case_2()
    {
        $catalog = NationalSurgicalCatalogService::getFullCatalog();
        $this->assertIsArray($catalog);
        $this->assertNotEmpty($catalog);
        $totalCount = NationalSurgicalCatalogService::getTotalProceduresCount();
        $this->assertGreaterThan(500, $totalCount);

        $classCounts = NationalSurgicalCatalogService::getCountByClassification();
        $this->assertArrayHasKey('Đặc biệt', $classCounts);
        $this->assertArrayHasKey('Loại I', $classCounts);
        $this->assertGreaterThan(50, $classCounts['Đặc biệt']);

        $searchResult = NationalSurgicalCatalogService::searchByName('phẫu thuật');
        $this->assertIsArray($searchResult);
        $this->assertNotEmpty($searchResult);
        $this->assertNotEmpty($searchResult[1]['code']);
        $this->assertNotEmpty($searchResult[1]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[1]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[2]['code']);
        $this->assertNotEmpty($searchResult[2]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[2]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[3]['code']);
        $this->assertNotEmpty($searchResult[3]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[3]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[4]['code']);
        $this->assertNotEmpty($searchResult[4]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[4]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[0]['code']);
        $this->assertNotEmpty($searchResult[0]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[0]['estimated_duration_min']);
    }

    public function test_surgical_procedure_catalog_suite_case_3()
    {
        $catalog = NationalSurgicalCatalogService::getFullCatalog();
        $this->assertIsArray($catalog);
        $this->assertNotEmpty($catalog);
        $totalCount = NationalSurgicalCatalogService::getTotalProceduresCount();
        $this->assertGreaterThan(500, $totalCount);

        $classCounts = NationalSurgicalCatalogService::getCountByClassification();
        $this->assertArrayHasKey('Đặc biệt', $classCounts);
        $this->assertArrayHasKey('Loại I', $classCounts);
        $this->assertGreaterThan(50, $classCounts['Đặc biệt']);

        $searchResult = NationalSurgicalCatalogService::searchByName('phẫu thuật');
        $this->assertIsArray($searchResult);
        $this->assertNotEmpty($searchResult);
        $this->assertNotEmpty($searchResult[1]['code']);
        $this->assertNotEmpty($searchResult[1]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[1]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[2]['code']);
        $this->assertNotEmpty($searchResult[2]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[2]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[3]['code']);
        $this->assertNotEmpty($searchResult[3]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[3]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[4]['code']);
        $this->assertNotEmpty($searchResult[4]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[4]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[0]['code']);
        $this->assertNotEmpty($searchResult[0]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[0]['estimated_duration_min']);
    }

    public function test_surgical_procedure_catalog_suite_case_4()
    {
        $catalog = NationalSurgicalCatalogService::getFullCatalog();
        $this->assertIsArray($catalog);
        $this->assertNotEmpty($catalog);
        $totalCount = NationalSurgicalCatalogService::getTotalProceduresCount();
        $this->assertGreaterThan(500, $totalCount);

        $classCounts = NationalSurgicalCatalogService::getCountByClassification();
        $this->assertArrayHasKey('Đặc biệt', $classCounts);
        $this->assertArrayHasKey('Loại I', $classCounts);
        $this->assertGreaterThan(50, $classCounts['Đặc biệt']);

        $searchResult = NationalSurgicalCatalogService::searchByName('phẫu thuật');
        $this->assertIsArray($searchResult);
        $this->assertNotEmpty($searchResult);
        $this->assertNotEmpty($searchResult[1]['code']);
        $this->assertNotEmpty($searchResult[1]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[1]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[2]['code']);
        $this->assertNotEmpty($searchResult[2]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[2]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[3]['code']);
        $this->assertNotEmpty($searchResult[3]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[3]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[4]['code']);
        $this->assertNotEmpty($searchResult[4]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[4]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[0]['code']);
        $this->assertNotEmpty($searchResult[0]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[0]['estimated_duration_min']);
    }

    public function test_surgical_procedure_catalog_suite_case_5()
    {
        $catalog = NationalSurgicalCatalogService::getFullCatalog();
        $this->assertIsArray($catalog);
        $this->assertNotEmpty($catalog);
        $totalCount = NationalSurgicalCatalogService::getTotalProceduresCount();
        $this->assertGreaterThan(500, $totalCount);

        $classCounts = NationalSurgicalCatalogService::getCountByClassification();
        $this->assertArrayHasKey('Đặc biệt', $classCounts);
        $this->assertArrayHasKey('Loại I', $classCounts);
        $this->assertGreaterThan(50, $classCounts['Đặc biệt']);

        $searchResult = NationalSurgicalCatalogService::searchByName('phẫu thuật');
        $this->assertIsArray($searchResult);
        $this->assertNotEmpty($searchResult);
        $this->assertNotEmpty($searchResult[1]['code']);
        $this->assertNotEmpty($searchResult[1]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[1]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[2]['code']);
        $this->assertNotEmpty($searchResult[2]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[2]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[3]['code']);
        $this->assertNotEmpty($searchResult[3]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[3]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[4]['code']);
        $this->assertNotEmpty($searchResult[4]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[4]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[0]['code']);
        $this->assertNotEmpty($searchResult[0]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[0]['estimated_duration_min']);
    }

    public function test_surgical_procedure_catalog_suite_case_6()
    {
        $catalog = NationalSurgicalCatalogService::getFullCatalog();
        $this->assertIsArray($catalog);
        $this->assertNotEmpty($catalog);
        $totalCount = NationalSurgicalCatalogService::getTotalProceduresCount();
        $this->assertGreaterThan(500, $totalCount);

        $classCounts = NationalSurgicalCatalogService::getCountByClassification();
        $this->assertArrayHasKey('Đặc biệt', $classCounts);
        $this->assertArrayHasKey('Loại I', $classCounts);
        $this->assertGreaterThan(50, $classCounts['Đặc biệt']);

        $searchResult = NationalSurgicalCatalogService::searchByName('phẫu thuật');
        $this->assertIsArray($searchResult);
        $this->assertNotEmpty($searchResult);
        $this->assertNotEmpty($searchResult[1]['code']);
        $this->assertNotEmpty($searchResult[1]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[1]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[2]['code']);
        $this->assertNotEmpty($searchResult[2]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[2]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[3]['code']);
        $this->assertNotEmpty($searchResult[3]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[3]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[4]['code']);
        $this->assertNotEmpty($searchResult[4]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[4]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[0]['code']);
        $this->assertNotEmpty($searchResult[0]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[0]['estimated_duration_min']);
    }

    public function test_surgical_procedure_catalog_suite_case_7()
    {
        $catalog = NationalSurgicalCatalogService::getFullCatalog();
        $this->assertIsArray($catalog);
        $this->assertNotEmpty($catalog);
        $totalCount = NationalSurgicalCatalogService::getTotalProceduresCount();
        $this->assertGreaterThan(500, $totalCount);

        $classCounts = NationalSurgicalCatalogService::getCountByClassification();
        $this->assertArrayHasKey('Đặc biệt', $classCounts);
        $this->assertArrayHasKey('Loại I', $classCounts);
        $this->assertGreaterThan(50, $classCounts['Đặc biệt']);

        $searchResult = NationalSurgicalCatalogService::searchByName('phẫu thuật');
        $this->assertIsArray($searchResult);
        $this->assertNotEmpty($searchResult);
        $this->assertNotEmpty($searchResult[1]['code']);
        $this->assertNotEmpty($searchResult[1]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[1]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[2]['code']);
        $this->assertNotEmpty($searchResult[2]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[2]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[3]['code']);
        $this->assertNotEmpty($searchResult[3]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[3]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[4]['code']);
        $this->assertNotEmpty($searchResult[4]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[4]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[0]['code']);
        $this->assertNotEmpty($searchResult[0]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[0]['estimated_duration_min']);
    }

    public function test_surgical_procedure_catalog_suite_case_8()
    {
        $catalog = NationalSurgicalCatalogService::getFullCatalog();
        $this->assertIsArray($catalog);
        $this->assertNotEmpty($catalog);
        $totalCount = NationalSurgicalCatalogService::getTotalProceduresCount();
        $this->assertGreaterThan(500, $totalCount);

        $classCounts = NationalSurgicalCatalogService::getCountByClassification();
        $this->assertArrayHasKey('Đặc biệt', $classCounts);
        $this->assertArrayHasKey('Loại I', $classCounts);
        $this->assertGreaterThan(50, $classCounts['Đặc biệt']);

        $searchResult = NationalSurgicalCatalogService::searchByName('phẫu thuật');
        $this->assertIsArray($searchResult);
        $this->assertNotEmpty($searchResult);
        $this->assertNotEmpty($searchResult[1]['code']);
        $this->assertNotEmpty($searchResult[1]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[1]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[2]['code']);
        $this->assertNotEmpty($searchResult[2]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[2]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[3]['code']);
        $this->assertNotEmpty($searchResult[3]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[3]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[4]['code']);
        $this->assertNotEmpty($searchResult[4]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[4]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[0]['code']);
        $this->assertNotEmpty($searchResult[0]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[0]['estimated_duration_min']);
    }

    public function test_surgical_procedure_catalog_suite_case_9()
    {
        $catalog = NationalSurgicalCatalogService::getFullCatalog();
        $this->assertIsArray($catalog);
        $this->assertNotEmpty($catalog);
        $totalCount = NationalSurgicalCatalogService::getTotalProceduresCount();
        $this->assertGreaterThan(500, $totalCount);

        $classCounts = NationalSurgicalCatalogService::getCountByClassification();
        $this->assertArrayHasKey('Đặc biệt', $classCounts);
        $this->assertArrayHasKey('Loại I', $classCounts);
        $this->assertGreaterThan(50, $classCounts['Đặc biệt']);

        $searchResult = NationalSurgicalCatalogService::searchByName('phẫu thuật');
        $this->assertIsArray($searchResult);
        $this->assertNotEmpty($searchResult);
        $this->assertNotEmpty($searchResult[1]['code']);
        $this->assertNotEmpty($searchResult[1]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[1]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[2]['code']);
        $this->assertNotEmpty($searchResult[2]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[2]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[3]['code']);
        $this->assertNotEmpty($searchResult[3]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[3]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[4]['code']);
        $this->assertNotEmpty($searchResult[4]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[4]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[0]['code']);
        $this->assertNotEmpty($searchResult[0]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[0]['estimated_duration_min']);
    }

    public function test_surgical_procedure_catalog_suite_case_10()
    {
        $catalog = NationalSurgicalCatalogService::getFullCatalog();
        $this->assertIsArray($catalog);
        $this->assertNotEmpty($catalog);
        $totalCount = NationalSurgicalCatalogService::getTotalProceduresCount();
        $this->assertGreaterThan(500, $totalCount);

        $classCounts = NationalSurgicalCatalogService::getCountByClassification();
        $this->assertArrayHasKey('Đặc biệt', $classCounts);
        $this->assertArrayHasKey('Loại I', $classCounts);
        $this->assertGreaterThan(50, $classCounts['Đặc biệt']);

        $searchResult = NationalSurgicalCatalogService::searchByName('phẫu thuật');
        $this->assertIsArray($searchResult);
        $this->assertNotEmpty($searchResult);
        $this->assertNotEmpty($searchResult[1]['code']);
        $this->assertNotEmpty($searchResult[1]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[1]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[2]['code']);
        $this->assertNotEmpty($searchResult[2]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[2]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[3]['code']);
        $this->assertNotEmpty($searchResult[3]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[3]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[4]['code']);
        $this->assertNotEmpty($searchResult[4]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[4]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[0]['code']);
        $this->assertNotEmpty($searchResult[0]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[0]['estimated_duration_min']);
    }

    public function test_surgical_procedure_catalog_suite_case_11()
    {
        $catalog = NationalSurgicalCatalogService::getFullCatalog();
        $this->assertIsArray($catalog);
        $this->assertNotEmpty($catalog);
        $totalCount = NationalSurgicalCatalogService::getTotalProceduresCount();
        $this->assertGreaterThan(500, $totalCount);

        $classCounts = NationalSurgicalCatalogService::getCountByClassification();
        $this->assertArrayHasKey('Đặc biệt', $classCounts);
        $this->assertArrayHasKey('Loại I', $classCounts);
        $this->assertGreaterThan(50, $classCounts['Đặc biệt']);

        $searchResult = NationalSurgicalCatalogService::searchByName('phẫu thuật');
        $this->assertIsArray($searchResult);
        $this->assertNotEmpty($searchResult);
        $this->assertNotEmpty($searchResult[1]['code']);
        $this->assertNotEmpty($searchResult[1]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[1]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[2]['code']);
        $this->assertNotEmpty($searchResult[2]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[2]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[3]['code']);
        $this->assertNotEmpty($searchResult[3]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[3]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[4]['code']);
        $this->assertNotEmpty($searchResult[4]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[4]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[0]['code']);
        $this->assertNotEmpty($searchResult[0]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[0]['estimated_duration_min']);
    }

    public function test_surgical_procedure_catalog_suite_case_12()
    {
        $catalog = NationalSurgicalCatalogService::getFullCatalog();
        $this->assertIsArray($catalog);
        $this->assertNotEmpty($catalog);
        $totalCount = NationalSurgicalCatalogService::getTotalProceduresCount();
        $this->assertGreaterThan(500, $totalCount);

        $classCounts = NationalSurgicalCatalogService::getCountByClassification();
        $this->assertArrayHasKey('Đặc biệt', $classCounts);
        $this->assertArrayHasKey('Loại I', $classCounts);
        $this->assertGreaterThan(50, $classCounts['Đặc biệt']);

        $searchResult = NationalSurgicalCatalogService::searchByName('phẫu thuật');
        $this->assertIsArray($searchResult);
        $this->assertNotEmpty($searchResult);
        $this->assertNotEmpty($searchResult[1]['code']);
        $this->assertNotEmpty($searchResult[1]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[1]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[2]['code']);
        $this->assertNotEmpty($searchResult[2]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[2]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[3]['code']);
        $this->assertNotEmpty($searchResult[3]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[3]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[4]['code']);
        $this->assertNotEmpty($searchResult[4]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[4]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[0]['code']);
        $this->assertNotEmpty($searchResult[0]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[0]['estimated_duration_min']);
    }

    public function test_surgical_procedure_catalog_suite_case_13()
    {
        $catalog = NationalSurgicalCatalogService::getFullCatalog();
        $this->assertIsArray($catalog);
        $this->assertNotEmpty($catalog);
        $totalCount = NationalSurgicalCatalogService::getTotalProceduresCount();
        $this->assertGreaterThan(500, $totalCount);

        $classCounts = NationalSurgicalCatalogService::getCountByClassification();
        $this->assertArrayHasKey('Đặc biệt', $classCounts);
        $this->assertArrayHasKey('Loại I', $classCounts);
        $this->assertGreaterThan(50, $classCounts['Đặc biệt']);

        $searchResult = NationalSurgicalCatalogService::searchByName('phẫu thuật');
        $this->assertIsArray($searchResult);
        $this->assertNotEmpty($searchResult);
        $this->assertNotEmpty($searchResult[1]['code']);
        $this->assertNotEmpty($searchResult[1]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[1]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[2]['code']);
        $this->assertNotEmpty($searchResult[2]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[2]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[3]['code']);
        $this->assertNotEmpty($searchResult[3]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[3]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[4]['code']);
        $this->assertNotEmpty($searchResult[4]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[4]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[0]['code']);
        $this->assertNotEmpty($searchResult[0]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[0]['estimated_duration_min']);
    }

    public function test_surgical_procedure_catalog_suite_case_14()
    {
        $catalog = NationalSurgicalCatalogService::getFullCatalog();
        $this->assertIsArray($catalog);
        $this->assertNotEmpty($catalog);
        $totalCount = NationalSurgicalCatalogService::getTotalProceduresCount();
        $this->assertGreaterThan(500, $totalCount);

        $classCounts = NationalSurgicalCatalogService::getCountByClassification();
        $this->assertArrayHasKey('Đặc biệt', $classCounts);
        $this->assertArrayHasKey('Loại I', $classCounts);
        $this->assertGreaterThan(50, $classCounts['Đặc biệt']);

        $searchResult = NationalSurgicalCatalogService::searchByName('phẫu thuật');
        $this->assertIsArray($searchResult);
        $this->assertNotEmpty($searchResult);
        $this->assertNotEmpty($searchResult[1]['code']);
        $this->assertNotEmpty($searchResult[1]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[1]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[2]['code']);
        $this->assertNotEmpty($searchResult[2]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[2]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[3]['code']);
        $this->assertNotEmpty($searchResult[3]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[3]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[4]['code']);
        $this->assertNotEmpty($searchResult[4]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[4]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[0]['code']);
        $this->assertNotEmpty($searchResult[0]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[0]['estimated_duration_min']);
    }

    public function test_surgical_procedure_catalog_suite_case_15()
    {
        $catalog = NationalSurgicalCatalogService::getFullCatalog();
        $this->assertIsArray($catalog);
        $this->assertNotEmpty($catalog);
        $totalCount = NationalSurgicalCatalogService::getTotalProceduresCount();
        $this->assertGreaterThan(500, $totalCount);

        $classCounts = NationalSurgicalCatalogService::getCountByClassification();
        $this->assertArrayHasKey('Đặc biệt', $classCounts);
        $this->assertArrayHasKey('Loại I', $classCounts);
        $this->assertGreaterThan(50, $classCounts['Đặc biệt']);

        $searchResult = NationalSurgicalCatalogService::searchByName('phẫu thuật');
        $this->assertIsArray($searchResult);
        $this->assertNotEmpty($searchResult);
        $this->assertNotEmpty($searchResult[1]['code']);
        $this->assertNotEmpty($searchResult[1]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[1]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[2]['code']);
        $this->assertNotEmpty($searchResult[2]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[2]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[3]['code']);
        $this->assertNotEmpty($searchResult[3]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[3]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[4]['code']);
        $this->assertNotEmpty($searchResult[4]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[4]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[0]['code']);
        $this->assertNotEmpty($searchResult[0]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[0]['estimated_duration_min']);
    }

    public function test_surgical_procedure_catalog_suite_case_16()
    {
        $catalog = NationalSurgicalCatalogService::getFullCatalog();
        $this->assertIsArray($catalog);
        $this->assertNotEmpty($catalog);
        $totalCount = NationalSurgicalCatalogService::getTotalProceduresCount();
        $this->assertGreaterThan(500, $totalCount);

        $classCounts = NationalSurgicalCatalogService::getCountByClassification();
        $this->assertArrayHasKey('Đặc biệt', $classCounts);
        $this->assertArrayHasKey('Loại I', $classCounts);
        $this->assertGreaterThan(50, $classCounts['Đặc biệt']);

        $searchResult = NationalSurgicalCatalogService::searchByName('phẫu thuật');
        $this->assertIsArray($searchResult);
        $this->assertNotEmpty($searchResult);
        $this->assertNotEmpty($searchResult[1]['code']);
        $this->assertNotEmpty($searchResult[1]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[1]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[2]['code']);
        $this->assertNotEmpty($searchResult[2]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[2]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[3]['code']);
        $this->assertNotEmpty($searchResult[3]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[3]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[4]['code']);
        $this->assertNotEmpty($searchResult[4]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[4]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[0]['code']);
        $this->assertNotEmpty($searchResult[0]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[0]['estimated_duration_min']);
    }

    public function test_surgical_procedure_catalog_suite_case_17()
    {
        $catalog = NationalSurgicalCatalogService::getFullCatalog();
        $this->assertIsArray($catalog);
        $this->assertNotEmpty($catalog);
        $totalCount = NationalSurgicalCatalogService::getTotalProceduresCount();
        $this->assertGreaterThan(500, $totalCount);

        $classCounts = NationalSurgicalCatalogService::getCountByClassification();
        $this->assertArrayHasKey('Đặc biệt', $classCounts);
        $this->assertArrayHasKey('Loại I', $classCounts);
        $this->assertGreaterThan(50, $classCounts['Đặc biệt']);

        $searchResult = NationalSurgicalCatalogService::searchByName('phẫu thuật');
        $this->assertIsArray($searchResult);
        $this->assertNotEmpty($searchResult);
        $this->assertNotEmpty($searchResult[1]['code']);
        $this->assertNotEmpty($searchResult[1]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[1]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[2]['code']);
        $this->assertNotEmpty($searchResult[2]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[2]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[3]['code']);
        $this->assertNotEmpty($searchResult[3]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[3]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[4]['code']);
        $this->assertNotEmpty($searchResult[4]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[4]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[0]['code']);
        $this->assertNotEmpty($searchResult[0]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[0]['estimated_duration_min']);
    }

    public function test_surgical_procedure_catalog_suite_case_18()
    {
        $catalog = NationalSurgicalCatalogService::getFullCatalog();
        $this->assertIsArray($catalog);
        $this->assertNotEmpty($catalog);
        $totalCount = NationalSurgicalCatalogService::getTotalProceduresCount();
        $this->assertGreaterThan(500, $totalCount);

        $classCounts = NationalSurgicalCatalogService::getCountByClassification();
        $this->assertArrayHasKey('Đặc biệt', $classCounts);
        $this->assertArrayHasKey('Loại I', $classCounts);
        $this->assertGreaterThan(50, $classCounts['Đặc biệt']);

        $searchResult = NationalSurgicalCatalogService::searchByName('phẫu thuật');
        $this->assertIsArray($searchResult);
        $this->assertNotEmpty($searchResult);
        $this->assertNotEmpty($searchResult[1]['code']);
        $this->assertNotEmpty($searchResult[1]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[1]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[2]['code']);
        $this->assertNotEmpty($searchResult[2]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[2]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[3]['code']);
        $this->assertNotEmpty($searchResult[3]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[3]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[4]['code']);
        $this->assertNotEmpty($searchResult[4]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[4]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[0]['code']);
        $this->assertNotEmpty($searchResult[0]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[0]['estimated_duration_min']);
    }

    public function test_surgical_procedure_catalog_suite_case_19()
    {
        $catalog = NationalSurgicalCatalogService::getFullCatalog();
        $this->assertIsArray($catalog);
        $this->assertNotEmpty($catalog);
        $totalCount = NationalSurgicalCatalogService::getTotalProceduresCount();
        $this->assertGreaterThan(500, $totalCount);

        $classCounts = NationalSurgicalCatalogService::getCountByClassification();
        $this->assertArrayHasKey('Đặc biệt', $classCounts);
        $this->assertArrayHasKey('Loại I', $classCounts);
        $this->assertGreaterThan(50, $classCounts['Đặc biệt']);

        $searchResult = NationalSurgicalCatalogService::searchByName('phẫu thuật');
        $this->assertIsArray($searchResult);
        $this->assertNotEmpty($searchResult);
        $this->assertNotEmpty($searchResult[1]['code']);
        $this->assertNotEmpty($searchResult[1]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[1]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[2]['code']);
        $this->assertNotEmpty($searchResult[2]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[2]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[3]['code']);
        $this->assertNotEmpty($searchResult[3]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[3]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[4]['code']);
        $this->assertNotEmpty($searchResult[4]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[4]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[0]['code']);
        $this->assertNotEmpty($searchResult[0]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[0]['estimated_duration_min']);
    }

    public function test_surgical_procedure_catalog_suite_case_20()
    {
        $catalog = NationalSurgicalCatalogService::getFullCatalog();
        $this->assertIsArray($catalog);
        $this->assertNotEmpty($catalog);
        $totalCount = NationalSurgicalCatalogService::getTotalProceduresCount();
        $this->assertGreaterThan(500, $totalCount);

        $classCounts = NationalSurgicalCatalogService::getCountByClassification();
        $this->assertArrayHasKey('Đặc biệt', $classCounts);
        $this->assertArrayHasKey('Loại I', $classCounts);
        $this->assertGreaterThan(50, $classCounts['Đặc biệt']);

        $searchResult = NationalSurgicalCatalogService::searchByName('phẫu thuật');
        $this->assertIsArray($searchResult);
        $this->assertNotEmpty($searchResult);
        $this->assertNotEmpty($searchResult[1]['code']);
        $this->assertNotEmpty($searchResult[1]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[1]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[2]['code']);
        $this->assertNotEmpty($searchResult[2]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[2]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[3]['code']);
        $this->assertNotEmpty($searchResult[3]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[3]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[4]['code']);
        $this->assertNotEmpty($searchResult[4]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[4]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[0]['code']);
        $this->assertNotEmpty($searchResult[0]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[0]['estimated_duration_min']);
    }

    public function test_surgical_procedure_catalog_suite_case_21()
    {
        $catalog = NationalSurgicalCatalogService::getFullCatalog();
        $this->assertIsArray($catalog);
        $this->assertNotEmpty($catalog);
        $totalCount = NationalSurgicalCatalogService::getTotalProceduresCount();
        $this->assertGreaterThan(500, $totalCount);

        $classCounts = NationalSurgicalCatalogService::getCountByClassification();
        $this->assertArrayHasKey('Đặc biệt', $classCounts);
        $this->assertArrayHasKey('Loại I', $classCounts);
        $this->assertGreaterThan(50, $classCounts['Đặc biệt']);

        $searchResult = NationalSurgicalCatalogService::searchByName('phẫu thuật');
        $this->assertIsArray($searchResult);
        $this->assertNotEmpty($searchResult);
        $this->assertNotEmpty($searchResult[1]['code']);
        $this->assertNotEmpty($searchResult[1]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[1]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[2]['code']);
        $this->assertNotEmpty($searchResult[2]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[2]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[3]['code']);
        $this->assertNotEmpty($searchResult[3]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[3]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[4]['code']);
        $this->assertNotEmpty($searchResult[4]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[4]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[0]['code']);
        $this->assertNotEmpty($searchResult[0]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[0]['estimated_duration_min']);
    }

    public function test_surgical_procedure_catalog_suite_case_22()
    {
        $catalog = NationalSurgicalCatalogService::getFullCatalog();
        $this->assertIsArray($catalog);
        $this->assertNotEmpty($catalog);
        $totalCount = NationalSurgicalCatalogService::getTotalProceduresCount();
        $this->assertGreaterThan(500, $totalCount);

        $classCounts = NationalSurgicalCatalogService::getCountByClassification();
        $this->assertArrayHasKey('Đặc biệt', $classCounts);
        $this->assertArrayHasKey('Loại I', $classCounts);
        $this->assertGreaterThan(50, $classCounts['Đặc biệt']);

        $searchResult = NationalSurgicalCatalogService::searchByName('phẫu thuật');
        $this->assertIsArray($searchResult);
        $this->assertNotEmpty($searchResult);
        $this->assertNotEmpty($searchResult[1]['code']);
        $this->assertNotEmpty($searchResult[1]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[1]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[2]['code']);
        $this->assertNotEmpty($searchResult[2]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[2]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[3]['code']);
        $this->assertNotEmpty($searchResult[3]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[3]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[4]['code']);
        $this->assertNotEmpty($searchResult[4]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[4]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[0]['code']);
        $this->assertNotEmpty($searchResult[0]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[0]['estimated_duration_min']);
    }

    public function test_surgical_procedure_catalog_suite_case_23()
    {
        $catalog = NationalSurgicalCatalogService::getFullCatalog();
        $this->assertIsArray($catalog);
        $this->assertNotEmpty($catalog);
        $totalCount = NationalSurgicalCatalogService::getTotalProceduresCount();
        $this->assertGreaterThan(500, $totalCount);

        $classCounts = NationalSurgicalCatalogService::getCountByClassification();
        $this->assertArrayHasKey('Đặc biệt', $classCounts);
        $this->assertArrayHasKey('Loại I', $classCounts);
        $this->assertGreaterThan(50, $classCounts['Đặc biệt']);

        $searchResult = NationalSurgicalCatalogService::searchByName('phẫu thuật');
        $this->assertIsArray($searchResult);
        $this->assertNotEmpty($searchResult);
        $this->assertNotEmpty($searchResult[1]['code']);
        $this->assertNotEmpty($searchResult[1]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[1]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[2]['code']);
        $this->assertNotEmpty($searchResult[2]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[2]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[3]['code']);
        $this->assertNotEmpty($searchResult[3]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[3]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[4]['code']);
        $this->assertNotEmpty($searchResult[4]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[4]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[0]['code']);
        $this->assertNotEmpty($searchResult[0]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[0]['estimated_duration_min']);
    }

    public function test_surgical_procedure_catalog_suite_case_24()
    {
        $catalog = NationalSurgicalCatalogService::getFullCatalog();
        $this->assertIsArray($catalog);
        $this->assertNotEmpty($catalog);
        $totalCount = NationalSurgicalCatalogService::getTotalProceduresCount();
        $this->assertGreaterThan(500, $totalCount);

        $classCounts = NationalSurgicalCatalogService::getCountByClassification();
        $this->assertArrayHasKey('Đặc biệt', $classCounts);
        $this->assertArrayHasKey('Loại I', $classCounts);
        $this->assertGreaterThan(50, $classCounts['Đặc biệt']);

        $searchResult = NationalSurgicalCatalogService::searchByName('phẫu thuật');
        $this->assertIsArray($searchResult);
        $this->assertNotEmpty($searchResult);
        $this->assertNotEmpty($searchResult[1]['code']);
        $this->assertNotEmpty($searchResult[1]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[1]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[2]['code']);
        $this->assertNotEmpty($searchResult[2]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[2]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[3]['code']);
        $this->assertNotEmpty($searchResult[3]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[3]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[4]['code']);
        $this->assertNotEmpty($searchResult[4]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[4]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[0]['code']);
        $this->assertNotEmpty($searchResult[0]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[0]['estimated_duration_min']);
    }

    public function test_surgical_procedure_catalog_suite_case_25()
    {
        $catalog = NationalSurgicalCatalogService::getFullCatalog();
        $this->assertIsArray($catalog);
        $this->assertNotEmpty($catalog);
        $totalCount = NationalSurgicalCatalogService::getTotalProceduresCount();
        $this->assertGreaterThan(500, $totalCount);

        $classCounts = NationalSurgicalCatalogService::getCountByClassification();
        $this->assertArrayHasKey('Đặc biệt', $classCounts);
        $this->assertArrayHasKey('Loại I', $classCounts);
        $this->assertGreaterThan(50, $classCounts['Đặc biệt']);

        $searchResult = NationalSurgicalCatalogService::searchByName('phẫu thuật');
        $this->assertIsArray($searchResult);
        $this->assertNotEmpty($searchResult);
        $this->assertNotEmpty($searchResult[1]['code']);
        $this->assertNotEmpty($searchResult[1]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[1]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[2]['code']);
        $this->assertNotEmpty($searchResult[2]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[2]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[3]['code']);
        $this->assertNotEmpty($searchResult[3]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[3]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[4]['code']);
        $this->assertNotEmpty($searchResult[4]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[4]['estimated_duration_min']);
        $this->assertNotEmpty($searchResult[0]['code']);
        $this->assertNotEmpty($searchResult[0]['name']);
        $this->assertGreaterThanOrEqual(10, $searchResult[0]['estimated_duration_min']);
    }

    public function test_admin_can_access_surgical_catalog_ui()
    {
        $admin = User::where('role', 'admin')->first() ?: User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get(route('admin.medical-governance.surgical-catalog'));
        $response->assertStatus(200);
        $response->assertSee('Danh Mục Kỹ Thuật Phẫu Thuật');
    }
}
