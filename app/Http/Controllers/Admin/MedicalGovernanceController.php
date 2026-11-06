<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MedicalGovernance\NationalSurgicalCatalogService;
use App\Services\MedicalGovernance\ClinicalPracticeGuidelineService;
use App\Services\MedicalGovernance\PhysicianCmeAssessmentService;
use App\Services\MedicalGovernance\PhysicianResearchRepositoryService;
use App\Services\MedicalGovernance\PhysicianFatigueBalancingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicalGovernanceController extends Controller
{
    /**
     * Dashboard tổng quan Hệ sinh thái Quản trị Y khoa & Đội ngũ Bác sĩ Tâm Anh
     */
    public function index(): View
    {
        $surgicalCount = NationalSurgicalCatalogService::getTotalProceduresCount();
        $classCounts = NationalSurgicalCatalogService::getCountByClassification();
        $guidelinesCount = ClinicalPracticeGuidelineService::getTotalGuidelinesCount();
        $cmeCasesCount = PhysicianCmeAssessmentService::getTotalCasesCount();
        $researchCount = PhysicianResearchRepositoryService::getTotalStudiesCount();
        $fatigueRules = count(PhysicianFatigueBalancingService::getFatigueRuleMatrix());

        return view('admin.medical_governance.index', compact(
            'surgicalCount',
            'classCounts',
            'guidelinesCount',
            'cmeCasesCount',
            'researchCount',
            'fatigueRules'
        ));
    }

    /**
     * Tra cứu Danh mục Kỹ thuật Phẫu thuật theo Thông tư 50/BYT
     */
    public function surgicalCatalog(Request $request): View
    {
        $keyword = $request->query('q', '');
        $procedures = !empty($keyword)
            ? NationalSurgicalCatalogService::searchByName($keyword)
            : array_slice(NationalSurgicalCatalogService::getFullCatalog()['tim-mach']['procedures'] ?? [], 0, 50);

        return view('admin.medical_governance.surgical_catalog', compact('procedures', 'keyword'));
    }

    /**
     * Trung tâm Đào tạo Y khoa Liên tục (CME Hub) & Ngân hàng Tình huống Lâm sàng
     */
    public function cmeHub(): View
    {
        $allCases = PhysicianCmeAssessmentService::getAllAssessmentCases();
        $totalVignettes = PhysicianCmeAssessmentService::getTotalCasesCount();
        $totalModules = PhysicianCmeAssessmentService::getTotalModulesCount();

        return view('admin.medical_governance.cme_hub', compact('allCases', 'totalVignettes', 'totalModules'));
    }

    /**
     * Kho Phác đồ Chẩn đoán & Điều trị Y khoa (CPG Library)
     */
    public function guidelines(): View
    {
        $allGuidelines = ClinicalPracticeGuidelineService::getAllGuidelines();
        $totalCount = ClinicalPracticeGuidelineService::getTotalGuidelinesCount();

        return view('admin.medical_governance.clinical_guidelines', compact('allGuidelines', 'totalCount'));
    }

    /**
     * Kho Công trình Nghiên cứu Y học & Thử nghiệm Lâm sàng Bác sĩ Tâm Anh
     */
    public function research(): View
    {
        $studies = PhysicianResearchRepositoryService::getTopImpactPublications(50);
        $totalCount = PhysicianResearchRepositoryService::getTotalStudiesCount();

        return view('admin.medical_governance.research_repository', compact('studies', 'totalCount'));
    }
}
