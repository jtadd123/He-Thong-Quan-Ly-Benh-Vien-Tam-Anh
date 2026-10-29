<?php

namespace Tests\Feature;

use App\Models\DietRegimen;
use App\Models\NutritionAssessment;
use App\Models\PatientProfile;
use App\Models\User;
use Tests\TestCase;

class ClinicalNutritionTest extends TestCase
{
    protected User $receptionistUser;
    protected PatientProfile $patientProfile;
    protected DietRegimen $diabetesRegimen;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receptionistUser = User::where('role', User::ROLE_RECEPTIONIST)->first()
            ?? User::factory()->create(['role' => User::ROLE_RECEPTIONIST]);

        $patientUser = User::factory()->create([
            'role' => User::ROLE_PATIENT,
            'email' => 'pat_nut_' . uniqid() . '@tamanh.vn',
        ]);

        $this->patientProfile = PatientProfile::create([
            'user_id' => $patientUser->id,
            'patient_code' => 'BN-' . date('Ymd') . '-' . uniqid(),
            'full_name' => 'Hoàng Kim Ngân',
            'dob' => '1988-06-25',
            'gender' => 'female',
            'phone' => '0933998877',
        ]);

        $this->diabetesRegimen = DietRegimen::firstOrCreate(
            ['code' => 'DIET-DIABETES'],
            [
                'title' => 'Chế độ ăn cho người Đái tháo đường',
                'target_disease' => 'Đái tháo đường type 2',
                'daily_calorie_target' => 1600,
                'foods_recommended' => 'Gạo lứt, ức gà, rau xanh',
                'foods_restricted' => 'Mật ong, hoa quả sấy',
                'foods_avoided' => 'Đường kính, nước ngọt có ga',
            ]
        );
    }

    public function test_receptionist_can_view_nutrition_dashboard_and_regimens(): void
    {
        $response = $this->actingAs($this->receptionistUser)->get(route('reception.nutrition.index'));

        $response->assertStatus(200);
        $response->assertSee('Khoa Dinh Dưỡng');
        $response->assertSee('Thư Viện Phác Đồ');
    }

    public function test_can_assess_patient_nutrition_and_calculate_bmi_tdee(): void
    {
        // Chiều cao 160cm, Cân nặng 65kg -> BMI = 65 / (1.6 * 1.6) = 25.39 -> 25.4 (Béo phì độ I)
        $response = $this->actingAs($this->receptionistUser)->post(
            route('reception.nutrition.store'),
            [
                'patient_profile_id' => $this->patientProfile->id,
                'height_cm' => 160,
                'weight_kg' => 65,
                'diet_regimen_id' => $this->diabetesRegimen->id,
                'custom_instructions' => 'Hạn chế tinh bột nhanh, chia nhỏ 5 bữa ăn/ngày, đi bộ 30 phút mỗi tối',
            ]
        );

        $assessment = NutritionAssessment::where('patient_profile_id', $this->patientProfile->id)->first();
        $this->assertNotNull($assessment);
        $this->assertStringStartsWith('NUT-', $assessment->assessment_code);
        $this->assertEquals(25.4, (float)$assessment->bmi);
        $this->assertEquals('Béo phì độ I', $assessment->bmi_category);
        $this->assertGreaterThan(1200, $assessment->tdee_kcal);
        $this->assertEquals(2275, $assessment->water_intake_ml); // 65 * 35 = 2275ml

        $response->assertRedirect(route('reception.nutrition.show', $assessment));
        $response->assertSessionHas('success');
    }

    public function test_can_render_personalized_diet_plan_sheet_a4(): void
    {
        $assessment = NutritionAssessment::create([
            'assessment_code' => NutritionAssessment::generateAssessmentCode(),
            'patient_profile_id' => $this->patientProfile->id,
            'height_cm' => 165,
            'weight_kg' => 58,
            'bmi' => 21.3,
            'bmi_category' => 'Thể trạng Bình thường',
            'tdee_kcal' => 1750,
            'water_intake_ml' => 2000,
            'diet_regimen_id' => $this->diabetesRegimen->id,
            'custom_instructions' => 'Giữ vững chế độ ăn lành mạnh hiện tại',
            'assessed_at' => now(),
        ]);

        $response = $this->actingAs($this->receptionistUser)->get(
            route('reception.nutrition.print-plan', $assessment)
        );

        $response->assertStatus(200);
        $response->assertSee('HƯỚNG DẪN THỰC ĐƠN DINH DƯỠNG ĐIỀU TRỊ');
        $response->assertSee('Hoàng Kim Ngân');
        $response->assertSee('THỰC PHẨM NÊN DÙNG');
    }
}
