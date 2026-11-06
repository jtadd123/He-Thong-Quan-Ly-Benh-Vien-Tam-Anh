<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorSpecialtyTest extends TestCase
{
    protected User $admin;
    protected User $doctorUser;
    protected User $patientUser;
    protected Specialty $specialty;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin_test@tamanh.vn'],
            [
                'name' => 'Admin Test',
                'password' => bcrypt('123456'),
                'role' => User::ROLE_ADMIN,
            ]
        );

        $this->doctorUser = User::firstOrCreate(
            ['email' => 'doctor_test@tamanh.vn'],
            [
                'name' => 'Bác sĩ Test',
                'password' => bcrypt('123456'),
                'role' => User::ROLE_DOCTOR,
            ]
        );

        $this->patientUser = User::firstOrCreate(
            ['email' => 'patient_test@gmail.com'],
            [
                'name' => 'Bệnh nhân Test',
                'password' => bcrypt('123456'),
                'role' => User::ROLE_PATIENT,
            ]
        );

        $this->specialty = Specialty::firstOrCreate(
            ['slug' => 'chuyen-khoa-test'],
            [
                'name' => 'Chuyên khoa Test',
                'description' => 'Mô tả chuyên khoa test',
                'icon' => 'bi bi-heart',
                'is_active' => true,
            ]
        );
    }

    public function test_admin_can_view_specialties_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.specialties.index'));
        $response->assertStatus(200);
        $response->assertSee('Quản lý Chuyên khoa');
    }

    public function test_non_admin_cannot_access_specialties_management(): void
    {
        $response = $this->actingAs($this->patientUser)->get(route('admin.specialties.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_create_new_specialty_with_auto_slug(): void
    {
        $uniqueName = 'Khoa Chẩn Đoán Hình Ảnh ' . uniqid() . '_' . rand(1000, 9999);
        $response = $this->actingAs($this->admin)->post(route('admin.specialties.store'), [
            'name' => $uniqueName,
            'description' => 'Chẩn đoán hình ảnh kỹ thuật cao',
            'icon' => 'bi bi-camera',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.specialties.index'));
        $this->assertDatabaseHas('specialties', ['name' => $uniqueName]);
    }

    public function test_admin_can_toggle_specialty_status(): void
    {
        $initialStatus = $this->specialty->is_active;

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.specialties.toggle-status', $this->specialty));

        $response->assertStatus(302);
        $this->specialty->refresh();
        $this->assertEquals(!$initialStatus, $this->specialty->is_active);
    }

    public function test_admin_cannot_delete_specialty_if_doctors_exist(): void
    {
        // Create a doctor under this specialty
        $doctor = Doctor::firstOrCreate(
            ['user_id' => $this->doctorUser->id],
            [
                'specialty_id' => $this->specialty->id,
                'qualification' => 'TS.BS',
                'consultation_fee' => 400000,
                'room_number' => 'Phòng 101',
                'is_available' => true,
            ]
        );

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.specialties.destroy', $this->specialty));

        $response->assertRedirect(route('admin.specialties.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('specialties', ['id' => $this->specialty->id]);
    }

    public function test_admin_can_view_doctors_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.doctors.index'));
        $response->assertStatus(200);
        $response->assertSee('Quản lý Bác sĩ');
    }

    public function test_admin_can_view_doctor_schedule_page(): void
    {
        $doctor = Doctor::first();
        if ($doctor) {
            $response = $this->actingAs($this->admin)->get(route('admin.doctors.schedule', $doctor));
            $response->assertStatus(200);
            $response->assertSee('Cấu hình Lịch trực Tuần');
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_admin_can_update_doctor_schedule(): void
    {
        $doctor = Doctor::first();
        if ($doctor) {
            $response = $this->actingAs($this->admin)->put(route('admin.doctors.schedule.update', $doctor), [
                'schedules' => [
                    'monday_morning' => [
                        'is_active' => '1',
                        'start_time' => '08:00',
                        'end_time' => '11:30',
                        'max_patients' => '20',
                    ],
                ],
            ]);

            $response->assertRedirect(route('admin.doctors.schedule', $doctor));
            $this->assertDatabaseHas('doctor_schedules', [
                'doctor_id' => $doctor->id,
                'day_of_week' => 'monday',
                'shift' => 'morning',
                'max_patients' => 20,
            ]);
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_admin_can_upload_avatar_for_doctor(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $doctor = Doctor::first();
        if ($doctor && $doctor->user) {
            $file = \Illuminate\Http\UploadedFile::fake()->image('avatar.jpg', 300, 300);

            $response = $this->actingAs($this->admin)->put(route('admin.doctors.update', $doctor), [
                'name' => $doctor->user->name,
                'email' => $doctor->user->email,
                'specialty_id' => $doctor->specialty_id,
                'qualification' => $doctor->qualification,
                'room_number' => $doctor->room_number,
                'consultation_fee' => $doctor->consultation_fee,
                'avatar' => $file,
                'is_available' => '1',
            ]);

            $response->assertRedirect(route('admin.doctors.index'));
            $doctor->refresh();
            $this->assertNotNull($doctor->avatar);
            \Illuminate\Support\Facades\Storage::disk('public')->assertExists($doctor->avatar);
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_public_can_view_about_page(): void
    {
        $response = $this->get(route('about'));
        $response->assertStatus(200);
        $response->assertSee('TẬN TÂM VÌ SỨC KHỎE NGƯỜI BỆNH');
    }

    public function test_public_can_view_doctors_directory(): void
    {
        $response = $this->get(route('public.doctors.index'));
        $response->assertStatus(200);
        $response->assertSee('Danh Bạ Bác Sĩ Bệnh Viện Tâm Anh');
    }

    public function test_public_can_filter_doctors_on_duty_today(): void
    {
        $response = $this->get(route('public.doctors.index', ['on_duty_today' => 1]));
        $response->assertStatus(200);
        $response->assertSee('Chỉ hiển thị bác sĩ có lịch trực hôm nay');
    }

    public function test_public_can_live_search_doctors_via_ajax(): void
    {
        $response = $this->get(route('public.doctors.index', ['search' => 'Bác sĩ']), [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);
        $response->assertStatus(200);
        $response->assertSee('Đặt lịch khám');
    }

    public function test_public_can_view_single_doctor(): void
    {
        $doctor = Doctor::where('is_available', true)->first();
        if ($doctor) {
            $response = $this->get(route('public.doctors.show', $doctor));
            $response->assertStatus(200);
            $response->assertSee($doctor->full_name_with_title);
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_public_can_view_symptom_checker_page(): void
    {
        $response = $this->get(route('public.doctors.symptom_checker'));
        $response->assertStatus(200);
        $response->assertSee('Trợ Lý Gợi Ý Chuyên Khoa & Bác Sĩ Thông Minh');
    }

    public function test_public_can_match_symptoms_via_api(): void
    {
        $response = $this->postJson(route('public.doctors.symptom_match'), [
            'symptoms' => 'Tôi bị đau thắt ngực khi gắng sức và tim đập nhanh hồi hộp',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'has_match' => true,
        ]);
        $response->assertJsonPath('specialty.name', 'Tim mạch học');
    }

    public function test_public_can_download_doctor_vcard(): void
    {
        $doctor = Doctor::where('is_available', true)->first();
        if ($doctor) {
            $response = $this->get(route('public.doctors.vcard', $doctor));
            $response->assertStatus(200);
            $response->assertHeader('content-type', 'text/vcard; charset=utf-8');
            $this->assertStringContainsString('BEGIN:VCARD', $response->getContent());
            $this->assertStringContainsString('END:VCARD', $response->getContent());
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_public_can_view_doctor_desk_card(): void
    {
        $doctor = Doctor::where('is_available', true)->first();
        if ($doctor) {
            $response = $this->get(route('public.doctors.desk_card', $doctor));
            $response->assertStatus(200);
            $response->assertSee('Bảng Tên Để Bàn');
            $response->assertSee($doctor->room_number);
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_admin_can_view_doctor_leaves_page(): void
    {
        $doctor = Doctor::first();
        if ($doctor) {
            $response = $this->actingAs($this->admin)->get(route('admin.doctors.leaves', $doctor));
            $response->assertStatus(200);
            $response->assertSee('Quản Lý Nghỉ Phép & Ủy Quyền Trực Thay');
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_admin_can_store_and_delete_doctor_leave(): void
    {
        $doctor = Doctor::first();
        if ($doctor) {
            $response = $this->actingAs($this->admin)->post(route('admin.doctors.leaves.store', $doctor), [
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(3)->toDateString(),
                'reason' => 'Tham dự Hội nghị Y khoa Quốc tế',
                'status' => 'approved',
                'notes' => 'Kiểm thử nghỉ phép tự động',
            ]);

            $response->assertRedirect(route('admin.doctors.leaves', $doctor));
            $this->assertDatabaseHas('doctor_leaves', [
                'doctor_id' => $doctor->id,
                'reason' => 'Tham dự Hội nghị Y khoa Quốc tế',
                'status' => 'approved',
            ]);

            $this->assertTrue($doctor->fresh()->isOnLeave());

            $leave = $doctor->leaves()->latest()->first();
            $deleteResponse = $this->actingAs($this->admin)->delete(route('admin.doctors.leaves.destroy', $leave));
            $deleteResponse->assertRedirect(route('admin.doctors.leaves', $doctor));
            $this->assertDatabaseMissing('doctor_leaves', ['id' => $leave->id]);
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_admin_can_view_roster_matrix_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.roster'));
        $response->assertStatus(200);
        $response->assertSee('Ma Trận Phân Ca Trực Tuần Bệnh Viện');
        $response->assertSee('Bảng Lưới Phân Bổ 7 Ngày Trong Tuần');
    }

    public function test_non_admin_cannot_access_roster(): void
    {
        $user = User::factory()->create(['role' => 'patient']);
        $response = $this->actingAs($user)->get(route('admin.roster'));
        $response->assertStatus(403);
    }

    public function test_public_can_view_doctor_print_profile(): void
    {
        $doctor = Doctor::where('is_available', true)->first();
        if ($doctor) {
            $response = $this->get(route('public.doctors.print', $doctor));
            $response->assertStatus(200);
            $response->assertSee('BẢN GIỚI THIỆU HỒ SƠ NĂNG LỰC CHUYÊN MÔN BÁC SĨ');
            $response->assertSee($doctor->full_name_with_title);
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_admin_can_view_emergency_readiness_matrix(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.specialties.emergency-readiness'));
        $response->assertStatus(200);
        $response->assertSee('Ma Trận Năng Lực Cấp Cứu & Báo Động Đỏ');
        $response->assertSee('STEMI Can thiệp 24/7');
    }

    public function test_admin_can_view_doctor_kpi_scorecard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.doctors.kpi'));
        $response->assertStatus(200);
        $response->assertSee('Bảng Điểm KPI & Năng Suất Bác Sĩ');
        $response->assertSee('Bảng Xếp Hạng Thi Đua');
    }

    public function test_admin_can_export_doctors_csv(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.doctors.export.csv'));
        $response->assertStatus(200);
        $this->assertEquals('text/csv; charset=UTF-8', $response->headers->get('content-type'));
    }

    public function test_admin_can_download_csv_template(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.doctors.import.template'));
        $response->assertStatus(200);
        $this->assertEquals('text/csv; charset=UTF-8', $response->headers->get('content-type'));
    }

    public function test_admin_can_view_doctor_licenses_and_cchn(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.doctors.licenses'));
        $response->assertStatus(200);
        $response->assertSee('Quản Lý Chứng Chỉ Hành Nghề & Giấy Phép Y Tế');
    }

    public function test_admin_can_view_workload_balancer_heatmap(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.doctors.workload-balancer'));
        $response->assertStatus(200);
        $response->assertSee('Bản Đồ Nhiệt Tải Khám & Điều Phối Công Suất');
    }

    public function test_admin_can_view_and_moderate_doctor_reviews(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.doctors.reviews'));
        $response->assertStatus(200);
        $response->assertSee('Quản Lý Đánh Giá Bác Sĩ Từ Người Bệnh');
    }

    public function test_admin_can_view_doctor_audit_logs(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.doctors.audit-logs'));
        $response->assertStatus(200);
        $response->assertSee('Nhật Ký Kiểm Toán Hồ Sơ & Lịch Trực Bác Sĩ');
    }

    public function test_public_can_view_doctor_qr_code_smart_badge(): void
    {
        $doctor = Doctor::first();
        if ($doctor) {
            $response = $this->get(route('public.doctors.qr_code', $doctor));
            $response->assertStatus(200);
            $response->assertSee('Tiếp Đón Kiosk & Buồng Khám Thông Minh', false);
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_public_can_view_clinic_wayfinding_guide(): void
    {
        $doctor = Doctor::first();
        if ($doctor) {
            $response = $this->get(route('public.doctors.wayfinding', $doctor));
            $response->assertStatus(200);
            $response->assertSee('Hệ Thống Điều Hướng Bệnh Viện Thông Minh');
            $response->assertSee('Sảnh Tiếp Đón Trung Tâm');
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_public_can_switch_bilingual_doctor_profile(): void
    {
        $doctor = Doctor::first();
        if ($doctor) {
            $doctor->schedules()->firstOrCreate([
                'day_of_week' => 'monday',
                'shift' => 'morning',
            ], [
                'start_time' => '07:30:00',
                'end_time' => '11:30:00',
                'max_patients' => 20,
                'is_active' => true,
            ]);
            $response = $this->get(route('public.doctors.show', ['doctor' => $doctor, 'lang' => 'en']));
            $response->assertStatus(200);
            $response->assertSee('English');
            $response->assertSee('Weekly Consultation Schedule');
            $response->assertSee('Monday');
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_public_can_submit_doctor_review(): void
    {
        $doctor = Doctor::first();
        if ($doctor) {
            $response = $this->post(route('public.doctors.reviews.store', $doctor), [
                'patient_name' => 'Nguyễn Bệnh Nhân Test',
                'patient_phone' => '0901234567',
                'rating' => 5,
                'comment' => 'Bác sĩ khám rất tận tâm và giải thích dễ hiểu.',
            ]);
            $response->assertRedirect();
            $this->assertDatabaseHas('doctor_reviews', [
                'doctor_id' => $doctor->id,
                'patient_name' => 'Nguyễn Bệnh Nhân Test',
                'rating' => 5,
            ]);
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_admin_can_search_doctor_by_specialty_and_room(): void
    {
        $this->actingAs($this->admin);
        $doctor = Doctor::with(['specialty', 'user'])->whereNotNull('specialty_id')->latest()->first();
        if ($doctor && $doctor->specialty) {
            $response = $this->get(route('admin.doctors.index', ['search' => $doctor->specialty->name]));
            $response->assertStatus(200);
            $response->assertSee($doctor->full_name_with_title);

            $responseJson = $this->get(route('admin.doctors.index', ['search' => $doctor->specialty->name, 'format' => 'json']));
            $responseJson->assertStatus(200);
            $responseJson->assertJsonFragment(['name' => $doctor->full_name_with_title]);
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_public_and_admin_can_search_unaccented_vietnamese(): void
    {
        $doctor = Doctor::whereHas('user', function ($q) {
            $q->where('name', 'LIKE', '%Đạt%');
        })->first();

        if ($doctor) {
            $this->actingAs($this->admin);
            $response = $this->get(route('admin.doctors.index', ['search' => 'dat', 'format' => 'json']));
            $response->assertStatus(200);
            $this->assertTrue(collect($response->json('data'))->contains('id', $doctor->id));

            $pubResponse = $this->get(route('public.doctors.index', ['search' => 'dat', 'format' => 'json']));
            $pubResponse->assertStatus(200);
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_doctor_on_leave_shows_on_leave_status_and_substitute_on_public_and_admin(): void
    {
        $doctor = Doctor::find(45) ?: Doctor::first();
        if ($doctor) {
            $subDoctor = Doctor::where('id', '!=', $doctor->id)->first();
            \App\Models\DoctorLeave::updateOrCreate([
                'doctor_id' => $doctor->id,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(2)->toDateString(),
            ], [
                'substitute_doctor_id' => $subDoctor?->id,
                'reason' => 'Nghỉ ốm / Khám sức khỏe định kỳ',
                'status' => 'approved',
            ]);

            // Test Admin list
            $this->actingAs($this->admin);
            $adminResponse = $this->get(route('admin.doctors.index'));
            $adminResponse->assertStatus(200);
            $adminResponse->assertSee('Nghỉ phép');

            // Test Public profile
            $publicResponse = $this->get(route('public.doctors.show', $doctor));
            $publicResponse->assertStatus(200);
            $publicResponse->assertSee('Đang Nghỉ Phép');
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_roster_matrix_detects_doctor_leave_anomaly_and_shows_leave_badge(): void
    {
        $doctor = Doctor::first();
        if ($doctor) {
            $today = now();
            $dayOfWeek = strtolower($today->englishDayOfWeek);
            \App\Models\DoctorSchedule::updateOrCreate([
                'doctor_id' => $doctor->id,
                'day_of_week' => $dayOfWeek,
                'shift' => 'morning',
            ], [
                'is_active' => true,
                'max_patients' => 15,
            ]);
            \App\Models\DoctorLeave::updateOrCreate([
                'doctor_id' => $doctor->id,
                'start_date' => $today->toDateString(),
                'end_date' => $today->copy()->addDays(2)->toDateString(),
            ], [
                'reason' => 'Nghỉ phép thường niên',
                'status' => 'approved',
            ]);
        }

        $this->actingAs($this->admin);
        $response = $this->get(route('admin.roster'));
        $response->assertStatus(200);
        $response->assertSee('ĐANG NGHỈ PHÉP');
        $response->assertSee('NGHỈ:');
    }

    public function test_admin_can_export_doctors_excel_table(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.doctors.export.excel'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
        $response->assertSee('HỆ THỐNG BỆNH VIỆN ĐA KHOA TÂM ANH');
    }

    public function test_admin_can_download_excel_template(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.doctors.import.template.excel'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
    }

    public function test_admin_can_import_doctors_from_csv_or_excel(): void
    {
        $this->actingAs($this->admin);
        $csvContent = "\xEF\xBB\xBFsep=,\r\n\"Họ và Tên\",Email,\"Mật Khẩu\",\"Mã Chuyên Khoa\",\"Học Vị\",\"Phòng Khám\",\"Giá Khám\"\r\n\"BS Bác Sĩ Mới Import\",bs.moiimport@tamanh.vn,123456,1,TS.BS,\"Phòng 101 - Tòa A\",400000\r\n";
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('mau_bac_si.csv', $csvContent);

        $response = $this->post(route('admin.doctors.import.csv'), [
            'csv_file' => $file,
        ]);
        $response->assertRedirect(route('admin.doctors.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'email' => 'bs.moiimport@tamanh.vn',
        ]);
    }

    public function test_admin_can_renew_doctor_cchn_license(): void
    {
        $this->actingAs($this->admin);
        $doctor = Doctor::first();
        if ($doctor) {
            $response = $this->patch(route('admin.doctors.licenses.update', $doctor), [
                'cchn_number' => '009999/BYT-CCHN',
                'cchn_issuer' => 'Bộ Y Tế',
                'cchn_issued_date' => now()->toDateString(),
                'cchn_expiry_date' => now()->addYears(5)->toDateString(),
                'cme_hours' => 48,
            ]);
            $response->assertRedirect(route('admin.doctors.licenses'));
            $this->assertDatabaseHas('doctors', [
                'id' => $doctor->id,
                'cchn_number' => '009999/BYT-CCHN',
            ]);
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_admin_can_toggle_emergency_readiness_code_red(): void
    {
        $this->actingAs($this->admin);
        $specialty = Specialty::first();
        if ($specialty) {
            $oldStatus = (bool)$specialty->is_code_red_supported;
            $response = $this->patch(route('admin.specialties.emergency-toggle', $specialty));
            $response->assertRedirect();
            $this->assertEquals(!$oldStatus, (bool)$specialty->fresh()->is_code_red_supported);
        } else {
            $this->assertTrue(true);
        }
    }
}
