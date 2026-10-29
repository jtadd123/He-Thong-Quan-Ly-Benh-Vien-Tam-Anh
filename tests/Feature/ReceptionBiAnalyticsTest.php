<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\PatientInvoice;
use App\Models\PatientProfile;
use App\Models\Specialty;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class ReceptionBiAnalyticsTest extends TestCase
{
    protected User $receptionist;
    protected User $patientUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receptionist = User::where('role', User::ROLE_RECEPTIONIST)->first()
            ?? User::factory()->create([
                'email' => 'rec_bi_' . uniqid() . '@tamanh.vn',
                'role' => User::ROLE_RECEPTIONIST,
            ]);

        $this->patientUser = User::where('role', User::ROLE_PATIENT)->first()
            ?? User::factory()->create([
                'email' => 'patient_bi_' . uniqid() . '@tamanh.vn',
                'role' => User::ROLE_PATIENT,
            ]);
    }

    public function test_receptionist_can_view_analytics_dashboard(): void
    {
        $response = $this->actingAs($this->receptionist)
            ->get(route('reception.analytics.index'));

        $response->assertStatus(200);
        $response->assertSee('Trung Tâm Giám Sát Hiệu Suất');
        $response->assertSee('Thời Gian Chờ Trung Bình');
        $response->assertSee('Phân Luồng Khẩn Cấp ESI');
        $response->assertSee('Lưu Lượng Bệnh Nhân Đến Khám Theo Khung Giờ');
    }

    public function test_analytics_supports_different_time_periods(): void
    {
        // 1. Period Today
        $responseToday = $this->actingAs($this->receptionist)
            ->get(route('reception.analytics.index', ['period' => 'today']));
        $responseToday->assertStatus(200);

        // 2. Period 30 Days
        $response30Days = $this->actingAs($this->receptionist)
            ->get(route('reception.analytics.index', ['period' => '30days']));
        $response30Days->assertStatus(200);
    }

    public function test_unauthorized_user_cannot_access_analytics(): void
    {
        // Khách chưa đăng nhập
        $this->get(route('reception.analytics.index'))->assertRedirect(route('login'));

        // Bệnh nhân không có quyền xem báo cáo điều hành
        $this->actingAs($this->patientUser)
            ->get(route('reception.analytics.index'))
            ->assertStatus(403);
    }
}
