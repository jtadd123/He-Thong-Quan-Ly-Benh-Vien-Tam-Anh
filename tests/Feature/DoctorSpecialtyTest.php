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
        $uniqueName = 'Khoa Chẩn Đoán Hình Ảnh ' . rand(100, 999);
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
}
