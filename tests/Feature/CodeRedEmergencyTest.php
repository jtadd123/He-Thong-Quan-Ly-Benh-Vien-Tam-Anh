<?php

namespace Tests\Feature;

use App\Models\EmergencyCodeRed;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class CodeRedEmergencyTest extends TestCase
{
    protected User $receptionistUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receptionistUser = User::where('role', User::ROLE_RECEPTIONIST)->first()
            ?? User::factory()->create(['role' => User::ROLE_RECEPTIONIST]);
    }

    public function test_receptionist_can_view_code_red_dashboard(): void
    {
        $response = $this->actingAs($this->receptionistUser)->get(route('reception.code-red.index'));

        $response->assertStatus(200);
        $response->assertSee('Báo Động Đỏ Toàn Viện');
        $response->assertSee('Kích Hoạt Báo Động Đỏ');
    }

    public function test_can_activate_code_red_emergency(): void
    {
        $response = $this->actingAs($this->receptionistUser)->post(
            route('reception.code-red.store'),
            [
                'patient_name' => 'Vũ Thị Minh Nguyệt',
                'gender' => 'female',
                'estimated_age' => '62 tuổi',
                'ambulance_unit' => 'Xe Cấp Cứu 115 Tâm Anh Số 02',
                'paramedic_in_charge' => 'BS. Trần Văn Hùng',
                'paramedic_phone' => '0988776655',
                'eta_minutes' => 8,
                'emergency_type' => EmergencyCodeRed::TYPE_STROKE,
                'clinical_condition' => 'Yếu liệt nửa người trái, thất ngôn đột ngột giờ thứ 1',
                'blood_pressure' => '180/100',
                'pulse' => '95',
                'spo2' => '96',
                'glasgow' => '13/15',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $codeRed = EmergencyCodeRed::where('patient_name', 'Vũ Thị Minh Nguyệt')->first();
        $this->assertNotNull($codeRed);
        $this->assertStringStartsWith('CR-', $codeRed->code_red_number);
        $this->assertEquals(EmergencyCodeRed::STATUS_EN_ROUTE, $codeRed->status);
        $this->assertEquals('stroke', $codeRed->emergency_type);
        $this->assertEquals(8, $codeRed->eta_minutes);
    }

    public function test_can_track_door_to_intervention_time_and_resolve(): void
    {
        $codeRed = EmergencyCodeRed::create([
            'code_red_number' => EmergencyCodeRed::generateCodeRedNumber(),
            'patient_name' => 'Ngô Văn Thành',
            'ambulance_unit' => 'Xe Cấp Cứu 115',
            'emergency_type' => EmergencyCodeRed::TYPE_STEMI,
            'clinical_condition' => 'Đau thắt ngực dữ dội, ST chênh lên V1-V4',
            'activated_by_user_id' => $this->receptionistUser->id,
            'activated_at' => Carbon::now()->subMinutes(40),
            'status' => EmergencyCodeRed::STATUS_EN_ROUTE,
        ]);

        // 1. Xe cập cổng viện (Door Time)
        $this->actingAs($this->receptionistUser)->post(route('reception.code-red.mark-arrived', $codeRed));
        $codeRed->refresh();
        $this->assertEquals(EmergencyCodeRed::STATUS_ARRIVED, $codeRed->status);
        $this->assertNotNull($codeRed->arrived_at);

        // 2. Bắt đầu can thiệp nong mạch (Intervention Time)
        $this->actingAs($this->receptionistUser)->post(route('reception.code-red.mark-intervention', $codeRed));
        $codeRed->refresh();
        $this->assertEquals(EmergencyCodeRed::STATUS_IN_INTERVENTION, $codeRed->status);
        $this->assertNotNull($codeRed->intervention_started_at);

        // 3. Giải tỏa báo động đỏ
        $this->actingAs($this->receptionistUser)->post(
            route('reception.code-red.resolve', $codeRed),
            ['outcome_notes' => 'Tái thông mạch vành thành công, đặt 1 stent phủ thuốc']
        );
        $codeRed->refresh();
        $this->assertEquals(EmergencyCodeRed::STATUS_RESOLVED, $codeRed->status);
        $this->assertNotNull($codeRed->resolved_at);
        $this->assertStringContainsString('Tái thông mạch vành thành công', $codeRed->outcome_notes);
    }
}
