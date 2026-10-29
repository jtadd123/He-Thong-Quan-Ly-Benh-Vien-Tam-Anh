<?php

namespace Tests\Feature;

use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabService;
use App\Models\PatientProfile;
use App\Models\User;
use Tests\TestCase;

class LabOrderManagementTest extends TestCase
{
    protected User $receptionistUser;
    protected User $patientUser;
    protected PatientProfile $patientProfile;
    protected LabService $cbcService;
    protected LabService $potassiumService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receptionistUser = User::where('role', User::ROLE_RECEPTIONIST)->first()
            ?? User::factory()->create(['role' => User::ROLE_RECEPTIONIST]);

        $this->patientUser = User::factory()->create([
            'role' => User::ROLE_PATIENT,
            'email' => 'pat_lab_' . uniqid() . '@tamanh.vn',
        ]);

        $this->patientProfile = PatientProfile::create([
            'user_id' => $this->patientUser->id,
            'patient_code' => 'BN-' . date('Ymd') . '-' . uniqid(),
            'full_name' => 'Vũ Thị Hạnh',
            'dob' => '1985-04-12',
            'gender' => 'female',
            'phone' => '0977223344',
        ]);

        $this->cbcService = LabService::firstOrCreate(
            ['code' => 'XN-CBC'],
            [
                'name' => 'Tổng phân tích tế bào máu ngoại vi',
                'category' => 'laboratory',
                'sample_type' => 'Máu toàn phần',
                'price' => 150000,
            ]
        );

        $this->potassiumService = LabService::firstOrCreate(
            ['code' => 'XN-KALI'],
            [
                'name' => 'Điện giải đồ máu (Kali K+)',
                'category' => 'laboratory',
                'normal_range' => '3.5 - 5.0',
                'unit' => 'mmol/L',
                'price' => 120000,
                'panic_low' => 2.5,
                'panic_high' => 6.5,
            ]
        );
    }

    public function test_receptionist_can_view_lab_orders_dashboard(): void
    {
        $response = $this->actingAs($this->receptionistUser)->get(route('reception.lab-orders.index'));

        $response->assertStatus(200);
        $response->assertSee('Trung Tâm Cận Lâm Sàng');
        $response->assertSee('Tạo Phiếu Chỉ Định Mới');
    }

    public function test_receptionist_can_create_lab_order(): void
    {
        $response = $this->actingAs($this->receptionistUser)->post(
            route('reception.lab-orders.store'),
            [
                'patient_profile_id' => $this->patientProfile->id,
                'clinical_diagnosis' => 'Sốt kéo dài 3 ngày, mệt mỏi, theo dõi nhiễm trùng huyết',
                'service_ids' => [$this->cbcService->id, $this->potassiumService->id],
            ]
        );

        $order = LabOrder::where('patient_profile_id', $this->patientProfile->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertStringStartsWith('ORD-', $order->order_code);
        $this->assertEquals(270000, (float)$order->total_amount);
        $this->assertCount(2, $order->items);

        $response->assertRedirect(route('reception.lab-orders.show', $order));
        $response->assertSessionHas('success');
    }

    public function test_can_record_lab_result_and_trigger_critical_panic_alert(): void
    {
        $order = LabOrder::create([
            'order_code' => LabOrder::generateOrderCode(),
            'patient_profile_id' => $this->patientProfile->id,
            'clinical_diagnosis' => 'Suy tim mất bù cấp',
            'status' => LabOrder::STATUS_PENDING,
            'total_amount' => 120000,
            'ordered_at' => now(),
        ]);

        $item = LabOrderItem::create([
            'lab_order_id' => $order->id,
            'lab_service_id' => $this->potassiumService->id,
            'result_status' => LabOrderItem::RESULT_NORMAL,
        ]);

        // Nhập kết quả Kali máu 7.2 mmol/L (nguy cơ ngừng tim đe dọa tính mạng)
        $response = $this->actingAs($this->receptionistUser)->post(
            route('reception.lab-orders.items.update', $item),
            [
                'result_value' => '7.2',
                'technician_notes' => 'Mẫu máu không vỡ hồng cầu, đã chạy lặp lại 2 lần kết quả tương đương',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('warning');

        $item->refresh();
        $this->assertEquals('7.2', $item->result_value);
        $this->assertEquals(7.2, $item->result_number);
        $this->assertTrue((bool)$item->is_panic_alert, 'Hệ thống phải tự động bật báo động đỏ khi Kali vượt ngưỡng sinh tử 6.5');
        $this->assertEquals(LabOrderItem::RESULT_CRITICAL_PANIC, $item->result_status);
        $this->assertNotNull($item->completed_at);

        $order->refresh();
        $this->assertEquals(LabOrder::STATUS_COMPLETED, $order->status);
    }

    public function test_can_render_official_lab_result_report_a4(): void
    {
        $order = LabOrder::create([
            'order_code' => LabOrder::generateOrderCode(),
            'patient_profile_id' => $this->patientProfile->id,
            'clinical_diagnosis' => 'Khám tổng quát định kỳ',
            'status' => LabOrder::STATUS_COMPLETED,
            'total_amount' => 150000,
            'ordered_at' => now(),
            'completed_at' => now(),
        ]);

        LabOrderItem::create([
            'lab_order_id' => $order->id,
            'lab_service_id' => $this->cbcService->id,
            'result_value' => 'WBC: 6.8 G/L; RBC: 4.8 T/L',
            'result_status' => LabOrderItem::RESULT_NORMAL,
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($this->receptionistUser)->get(
            route('reception.lab-orders.report', $order)
        );

        $response->assertStatus(200);
        $response->assertSee('PHIẾU KẾT QUẢ CẬN LÂM SÀNG');
        $response->assertSee('BỆNH VIỆN ĐA KHOA TÂM ANH');
        $response->assertSee('Vũ Thị Hạnh');
    }
}
