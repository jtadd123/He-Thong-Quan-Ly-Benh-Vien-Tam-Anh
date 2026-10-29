<?php

namespace Tests\Feature;

use App\Models\EquipmentLoan;
use App\Models\MobilityEquipment;
use App\Models\User;
use Tests\TestCase;

class MobilityEquipmentTest extends TestCase
{
    protected User $receptionistUser;
    protected MobilityEquipment $wheelchair;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receptionistUser = User::where('role', User::ROLE_RECEPTIONIST)->first()
            ?? User::factory()->create(['role' => User::ROLE_RECEPTIONIST]);

        $this->wheelchair = MobilityEquipment::create([
            'device_code' => 'XL-TEST-' . strtoupper(substr(uniqid(), -5)),
            'name' => 'Xe lăn cơ tiêu chuẩn Test',
            'device_type' => MobilityEquipment::TYPE_WHEELCHAIR,
            'current_location' => 'Sảnh Tầng 1',
            'status' => MobilityEquipment::STATUS_AVAILABLE,
        ]);
    }

    public function test_receptionist_can_view_mobility_equipments_dashboard(): void
    {
        $response = $this->actingAs($this->receptionistUser)->get(route('reception.mobility.index'));

        $response->assertStatus(200);
        $response->assertSee('Quản Lý Xe Lăn');
        $response->assertSee('Sẵn Sàng');
    }

    public function test_can_borrow_wheelchair_and_update_status_to_in_use(): void
    {
        $response = $this->actingAs($this->receptionistUser)->post(
            route('reception.mobility.borrow'),
            [
                'mobility_equipment_id' => $this->wheelchair->id,
                'borrower_name' => 'Lê Thanh Tùng',
                'borrower_phone' => '0988776655',
                'id_card_held' => '001095012345',
                'destination_department' => 'Khoa Chẩn Đoán Hình Ảnh P.108',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->wheelchair->refresh();
        $this->assertEquals(MobilityEquipment::STATUS_IN_USE, $this->wheelchair->status);
        $this->assertEquals('Khoa Chẩn Đoán Hình Ảnh P.108', $this->wheelchair->current_location);

        $loan = EquipmentLoan::where('mobility_equipment_id', $this->wheelchair->id)->latest()->first();
        $this->assertNotNull($loan);
        $this->assertStringStartsWith('LN-', $loan->loan_code);
        $this->assertEquals(EquipmentLoan::STATUS_BORROWED, $loan->status);
    }

    public function test_cannot_borrow_already_in_use_wheelchair(): void
    {
        $this->wheelchair->update(['status' => MobilityEquipment::STATUS_IN_USE]);

        $response = $this->actingAs($this->receptionistUser)->post(
            route('reception.mobility.borrow'),
            [
                'mobility_equipment_id' => $this->wheelchair->id,
                'borrower_name' => 'Phạm Văn Nam',
                'borrower_phone' => '0912345678',
                'destination_department' => 'Khoa Khám Bệnh',
            ]
        );

        $response->assertSessionHas('error');
    }

    public function test_can_return_wheelchair_and_complete_infection_control_disinfection(): void
    {
        $this->wheelchair->update(['status' => MobilityEquipment::STATUS_IN_USE]);

        $loan = EquipmentLoan::create([
            'loan_code' => EquipmentLoan::generateLoanCode(),
            'mobility_equipment_id' => $this->wheelchair->id,
            'borrower_name' => 'Lê Thanh Tùng',
            'borrower_phone' => '0988776655',
            'destination_department' => 'Khoa Khám Bệnh',
            'borrowed_at' => now()->subHour(),
            'status' => EquipmentLoan::STATUS_BORROWED,
        ]);

        // 1. Trả xe và chuyển sang buồng khử khuẩn
        $returnRes = $this->actingAs($this->receptionistUser)->post(
            route('reception.mobility.return', $loan),
            ['disinfection_notes' => 'Bệnh nhân trả nguyên vẹn tại cửa sảnh']
        );
        $returnRes->assertRedirect();
        $returnRes->assertSessionHas('success');

        $loan->refresh();
        $this->assertEquals(EquipmentLoan::STATUS_RETURNED, $loan->status);
        $this->assertNotNull($loan->returned_at);

        $this->wheelchair->refresh();
        $this->assertEquals(MobilityEquipment::STATUS_DISINFECTING, $this->wheelchair->status);

        // 2. Nghiệm thu hoàn tất khử khuẩn chống nhiễm khuẩn bệnh viện
        $disinfectRes = $this->actingAs($this->receptionistUser)->post(
            route('reception.mobility.disinfect', $this->wheelchair)
        );
        $disinfectRes->assertRedirect();
        $disinfectRes->assertSessionHas('success');

        $this->wheelchair->refresh();
        $this->assertEquals(MobilityEquipment::STATUS_AVAILABLE, $this->wheelchair->status);
        $this->assertNotNull($this->wheelchair->last_disinfected_at);
        $this->assertEquals($this->receptionistUser->id, $this->wheelchair->last_disinfected_by_user_id);
    }
}
