<?php

namespace Tests\Feature;

use App\Models\BloodTransfusion;
use App\Models\BloodUnit;
use App\Models\PatientProfile;
use App\Models\User;
use Tests\TestCase;

class BloodTransfusionSafetyTest extends TestCase
{
    protected User $receptionistUser;
    protected User $nurse1;
    protected User $nurse2;
    protected PatientProfile $patientProfile;
    protected BloodUnit $unitO;
    protected BloodUnit $unitA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receptionistUser = User::where('role', User::ROLE_RECEPTIONIST)->first()
            ?? User::factory()->create(['role' => User::ROLE_RECEPTIONIST]);

        $this->nurse1 = User::factory()->create([
            'role' => User::ROLE_RECEPTIONIST,
            'name' => 'ĐD. Trần Thị Lan',
            'email' => 'nurse1_' . uniqid() . '@tamanh.vn',
        ]);

        $this->nurse2 = User::factory()->create([
            'role' => User::ROLE_RECEPTIONIST,
            'name' => 'ĐD. Lê Văn Hùng',
            'email' => 'nurse2_' . uniqid() . '@tamanh.vn',
        ]);

        $patientUser = User::factory()->create([
            'role' => User::ROLE_PATIENT,
            'email' => 'pat_bld_' . uniqid() . '@tamanh.vn',
        ]);

        $this->patientProfile = PatientProfile::create([
            'user_id' => $patientUser->id,
            'patient_code' => 'BN-' . date('Ymd') . '-' . uniqid(),
            'full_name' => 'Nguyễn Đình Trọng',
            'dob' => '1992-09-18',
            'gender' => 'male',
            'phone' => '0966554433',
        ]);

        $this->unitO = BloodUnit::create([
            'unit_code' => BloodUnit::generateUnitCode(),
            'blood_type_abo' => 'O',
            'blood_type_rh' => 'positive',
            'product_type' => BloodUnit::PRODUCT_PRBC,
            'volume_ml' => 350,
            'collection_date' => now()->subDays(5),
            'expiry_date' => now()->addDays(30),
            'status' => BloodUnit::STATUS_IN_STOCK,
        ]);

        $this->unitA = BloodUnit::create([
            'unit_code' => BloodUnit::generateUnitCode(),
            'blood_type_abo' => 'A',
            'blood_type_rh' => 'positive',
            'product_type' => BloodUnit::PRODUCT_PRBC,
            'volume_ml' => 250,
            'collection_date' => now()->subDays(3),
            'expiry_date' => now()->addDays(32),
            'status' => BloodUnit::STATUS_IN_STOCK,
        ]);
    }

    public function test_receptionist_can_view_blood_bank_and_transfusion_dashboard(): void
    {
        $response = $this->actingAs($this->receptionistUser)->get(route('reception.blood-transfusions.index'));

        $response->assertStatus(200);
        $response->assertSee('An Toàn Truyền Máu');
        $response->assertSee('Tổng Trong Kho');
    }

    public function test_system_blocks_incompatible_abo_blood_transfusion(): void
    {
        // Bệnh nhân nhóm O nhưng chỉ định truyền túi máu nhóm A -> Bất đồng ABO!
        $response = $this->actingAs($this->receptionistUser)->post(
            route('reception.blood-transfusions.store'),
            [
                'patient_profile_id' => $this->patientProfile->id,
                'blood_unit_id' => $this->unitA->id,
                'indication' => 'Thiếu máu cấp',
                'patient_abo' => 'O', // Nhóm O
                'patient_rh' => 'positive',
                'nurse_user_id_1' => $this->nurse1->id,
                'nurse_user_id_2' => $this->nurse2->id,
            ]
        );

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('blood_transfusions', [
            'blood_unit_id' => $this->unitA->id,
        ]);
    }

    public function test_can_prepare_transfusion_with_dual_nurse_sign_off_when_compatible(): void
    {
        // Bệnh nhân nhóm A truyền túi máu nhóm O (tương thích an toàn)
        $response = $this->actingAs($this->receptionistUser)->post(
            route('reception.blood-transfusions.store'),
            [
                'patient_profile_id' => $this->patientProfile->id,
                'blood_unit_id' => $this->unitO->id,
                'indication' => 'Xuất huyết tiêu hóa do loét dạ dày',
                'patient_abo' => 'A',
                'patient_rh' => 'positive',
                'nurse_user_id_1' => $this->nurse1->id,
                'nurse_user_id_2' => $this->nurse2->id,
                'blood_pressure' => '115/75',
                'heart_rate' => '84',
                'temperature' => '36.9',
            ]
        );

        $transfusion = BloodTransfusion::where('patient_profile_id', $this->patientProfile->id)->first();
        $this->assertNotNull($transfusion);
        $this->assertStringStartsWith('TRF-', $transfusion->transfusion_code);
        $this->assertTrue((bool)$transfusion->is_crossmatch_compatible);
        $this->assertEquals($this->nurse1->id, $transfusion->nurse_user_id_1);
        $this->assertEquals($this->nurse2->id, $transfusion->nurse_user_id_2);

        $response->assertRedirect(route('reception.blood-transfusions.show', $transfusion));
        $response->assertSessionHas('success');
    }

    public function test_can_transition_transfusion_monitoring_lifecycle(): void
    {
        $transfusion = BloodTransfusion::create([
            'transfusion_code' => BloodTransfusion::generateTransfusionCode(),
            'patient_profile_id' => $this->patientProfile->id,
            'blood_unit_id' => $this->unitO->id,
            'indication' => 'Thiếu máu mạn',
            'patient_abo' => 'O',
            'patient_rh' => 'positive',
            'is_crossmatch_compatible' => true,
            'crossmatch_notes' => 'Hòa hợp',
            'nurse_user_id_1' => $this->nurse1->id,
            'nurse_user_id_2' => $this->nurse2->id,
            'status' => BloodTransfusion::STATUS_PREPARED,
        ]);

        // 1. Bắt đầu truyền
        $startRes = $this->actingAs($this->receptionistUser)->post(
            route('reception.blood-transfusions.start', $transfusion)
        );
        $startRes->assertRedirect();
        $transfusion->refresh();
        $this->assertEquals(BloodTransfusion::STATUS_TRANSFUSING, $transfusion->status);
        $this->assertNotNull($transfusion->started_at);

        // 2. Ghi nhận sinh hiệu phút 15
        $vitalsRes = $this->actingAs($this->receptionistUser)->post(
            route('reception.blood-transfusions.vitals', $transfusion),
            [
                'bp_15' => '120/80',
                'hr_15' => '82',
                'temp_15' => '37.0',
            ]
        );
        $vitalsRes->assertRedirect();
        $transfusion->refresh();
        $this->assertNotNull($transfusion->vitals_15min);
        $this->assertEquals('120/80', $transfusion->vitals_15min['blood_pressure']);

        // 3. Hoàn tất truyền an toàn
        $compRes = $this->actingAs($this->receptionistUser)->post(
            route('reception.blood-transfusions.complete', $transfusion)
        );
        $compRes->assertRedirect();
        $transfusion->refresh();
        $this->assertEquals(BloodTransfusion::STATUS_COMPLETED, $transfusion->status);
        $this->assertNotNull($transfusion->completed_at);
    }
}
