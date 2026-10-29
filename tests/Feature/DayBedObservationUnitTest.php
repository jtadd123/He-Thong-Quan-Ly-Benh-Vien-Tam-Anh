<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\DayBed;
use App\Models\DayBedAdmission;
use App\Models\Doctor;
use App\Models\IvInfusion;
use App\Models\PatientProfile;
use App\Models\Specialty;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class DayBedObservationUnitTest extends TestCase
{
    protected User $receptionistUser;
    protected User $patientUser;
    protected PatientProfile $patientProfile;
    protected DayBed $bed;
    protected Doctor $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receptionistUser = User::where('role', User::ROLE_RECEPTIONIST)->first()
            ?? User::factory()->create(['role' => User::ROLE_RECEPTIONIST]);

        $this->patientUser = User::factory()->create([
            'role' => User::ROLE_PATIENT,
            'email' => 'daybed_' . uniqid() . '@tamanh.vn',
        ]);

        $this->patientProfile = PatientProfile::create([
            'user_id' => $this->patientUser->id,
            'patient_code' => 'BN-' . date('Ymd') . '-' . uniqid(),
            'full_name' => 'Trần Văn Mạnh',
            'dob' => '1988-11-20',
            'gender' => 'male',
            'phone' => '0987654321',
        ]);

        $this->doctor = Doctor::first() ?? Doctor::factory()->create();

        $this->bed = DayBed::create([
            'room_name' => 'Phòng Lưu Ban Ngày 102',
            'bed_code' => 'TEST-BED-' . uniqid(),
            'bed_type' => DayBed::TYPE_INFUSION,
            'status' => DayBed::STATUS_AVAILABLE,
            'hourly_rate' => 50000,
        ]);
    }

    public function test_receptionist_can_view_day_bed_interactive_map(): void
    {
        $response = $this->actingAs($this->receptionistUser)->get(route('reception.day-beds.index'));

        $response->assertStatus(200);
        $response->assertSee('Sơ Đồ Giường Ngoại Trú');
        $response->assertSee($this->bed->bed_code);
        $response->assertSee($this->bed->room_name);
    }

    public function test_can_admit_patient_to_available_bed(): void
    {
        $response = $this->actingAs($this->receptionistUser)->post(
            route('reception.day-beds.admit'),
            [
                'day_bed_id' => $this->bed->id,
                'patient_profile_id' => $this->patientProfile->id,
                'attending_doctor_id' => $this->doctor->id,
                'attending_nurse_name' => 'ĐD. Lê Thị Mai',
                'admission_reason' => 'Sốt xuất huyết Dengue ngày 3, truyền bù dịch điện giải',
                'stay_hours' => 4,
                'fluid_name' => 'Natri Clorid 0.9% 500ml',
                'volume_ml' => 500,
                'drip_rate_drops_per_min' => 40,
            ]
        );

        $response->assertRedirect(route('reception.day-beds.index'));
        $response->assertSessionHas('success');

        $this->bed->refresh();
        $this->assertEquals(DayBed::STATUS_OCCUPIED, $this->bed->status);

        $admission = DayBedAdmission::where('day_bed_id', $this->bed->id)->latest()->first();
        $this->assertNotNull($admission);
        $this->assertEquals($this->patientProfile->id, $admission->patient_profile_id);
        $this->assertEquals(DayBedAdmission::STATUS_ACTIVE, $admission->status);

        // Kiểm tra đã tự động tạo lượt truyền dịch
        $this->assertDatabaseHas('iv_infusions', [
            'day_bed_admission_id' => $admission->id,
            'fluid_name' => 'Natri Clorid 0.9% 500ml',
            'volume_ml' => 500,
            'status' => IvInfusion::STATUS_RUNNING,
        ]);
    }

    public function test_cannot_admit_patient_to_already_occupied_bed(): void
    {
        // Set bed to occupied
        $this->bed->update(['status' => DayBed::STATUS_OCCUPIED]);

        $response = $this->actingAs($this->receptionistUser)->post(
            route('reception.day-beds.admit'),
            [
                'day_bed_id' => $this->bed->id,
                'patient_profile_id' => $this->patientProfile->id,
                'admission_reason' => 'Test lỗi trùng giường',
            ]
        );

        $response->assertSessionHas('error');
    }

    public function test_can_discharge_patient_and_mark_bed_for_cleaning(): void
    {
        $admission = DayBedAdmission::create([
            'admission_code' => DayBedAdmission::generateAdmissionCode(),
            'day_bed_id' => $this->bed->id,
            'patient_profile_id' => $this->patientProfile->id,
            'admission_reason' => 'Truyền dịch hạ sốt',
            'admitted_at' => Carbon::now()->subHours(2),
            'status' => DayBedAdmission::STATUS_ACTIVE,
        ]);
        $this->bed->update(['status' => DayBed::STATUS_OCCUPIED]);

        $response = $this->actingAs($this->receptionistUser)->post(
            route('reception.day-beds.discharge', $admission),
            ['discharge_notes' => 'Sinh hiệu ổn, hết sốt cho về']
        );

        $response->assertRedirect(route('reception.day-beds.index'));

        $admission->refresh();
        $this->assertEquals(DayBedAdmission::STATUS_DISCHARGED, $admission->status);
        $this->assertNotNull($admission->discharged_at);

        $this->bed->refresh();
        $this->assertEquals(DayBed::STATUS_CLEANING, $this->bed->status);

        // Báo khử trùng xong
        $cleanResponse = $this->actingAs($this->receptionistUser)->post(
            route('reception.day-beds.cleaning-done', $this->bed)
        );
        $cleanResponse->assertRedirect();

        $this->bed->refresh();
        $this->assertEquals(DayBed::STATUS_AVAILABLE, $this->bed->status);
    }
}
