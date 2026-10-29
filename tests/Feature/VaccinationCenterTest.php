<?php

namespace Tests\Feature;

use App\Models\PatientProfile;
use App\Models\User;
use App\Models\VaccinationRecord;
use App\Models\Vaccine;
use Carbon\Carbon;
use Tests\TestCase;

class VaccinationCenterTest extends TestCase
{
    protected User $receptionistUser;
    protected User $patientUser;
    protected PatientProfile $patientProfile;
    protected Vaccine $vaccine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receptionistUser = User::where('role', User::ROLE_RECEPTIONIST)->first()
            ?? User::factory()->create(['role' => User::ROLE_RECEPTIONIST]);

        $this->patientUser = User::factory()->create([
            'role' => User::ROLE_PATIENT,
            'email' => 'vac_' . uniqid() . '@tamanh.vn',
        ]);

        $this->patientProfile = PatientProfile::create([
            'user_id' => $this->patientUser->id,
            'patient_code' => 'BN-' . date('Ymd') . '-' . uniqid(),
            'full_name' => 'Nguyễn Đăng Quang',
            'dob' => '2018-05-10',
            'gender' => 'male',
            'phone' => '0912987654',
        ]);

        $this->vaccine = Vaccine::where('code', 'VAC-VAXIGRIP')->first()
            ?? Vaccine::create([
                'name' => 'Vaxigrip Tetra (Cúm Tứ Giá)',
                'code' => 'VAC-TEST-' . uniqid(),
                'disease_prevented' => 'Phòng 4 chủng cúm mùa',
                'manufacturer' => 'Sanofi',
                'origin_country' => 'Pháp',
                'standard_dosage' => '0.5ml',
                'total_doses' => 1,
                'interval_days_between_doses' => 365,
                'price' => 350000,
            ]);
    }

    public function test_receptionist_can_view_vaccination_dashboard(): void
    {
        $response = $this->actingAs($this->receptionistUser)->get(route('reception.vaccinations.index'));

        $response->assertStatus(200);
        $response->assertSee('Trung Tâm Tiêm Chủng');
        $response->assertSee('Đăng Ký Mũi Tiêm Mới');
    }

    public function test_can_administer_vaccine_with_calculated_next_dose(): void
    {
        $response = $this->actingAs($this->receptionistUser)->post(
            route('reception.vaccinations.store'),
            [
                'patient_profile_id' => $this->patientProfile->id,
                'vaccine_id' => $this->vaccine->id,
                'dose_number' => 1,
                'batch_number' => 'LOT-202610-TEST',
                'injection_site' => 'Cơ delta cánh tay trái',
                'body_temperature' => 36.6,
                'screening_notes' => 'Không sốt, đủ điều kiện tiêm chủng',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $record = VaccinationRecord::where('patient_profile_id', $this->patientProfile->id)->first();
        $this->assertNotNull($record);
        $this->assertStringStartsWith('VX-', $record->record_code);
        $this->assertEquals(VaccinationRecord::STATUS_OBSERVING, $record->observation_status);
        $this->assertEquals(36.6, $record->body_temperature);
    }

    public function test_can_clear_30_minute_post_vaccination_observation(): void
    {
        $record = VaccinationRecord::create([
            'record_code' => VaccinationRecord::generateRecordCode(),
            'patient_profile_id' => $this->patientProfile->id,
            'vaccine_id' => $this->vaccine->id,
            'batch_number' => 'LOT-TEST-99',
            'administered_at' => Carbon::now()->subMinutes(35),
            'observation_status' => VaccinationRecord::STATUS_OBSERVING,
        ]);

        $response = $this->actingAs($this->receptionistUser)->post(
            route('reception.vaccinations.clear', $record),
            ['notes' => 'Không sốt, không dị ứng, vết tiêm khô']
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $record->refresh();
        $this->assertEquals(VaccinationRecord::STATUS_CLEARED, $record->observation_status);
        $this->assertNotNull($record->observation_cleared_at);
    }

    public function test_can_render_bilingual_vaccination_certificate(): void
    {
        $record = VaccinationRecord::create([
            'record_code' => VaccinationRecord::generateRecordCode(),
            'patient_profile_id' => $this->patientProfile->id,
            'vaccine_id' => $this->vaccine->id,
            'batch_number' => 'LOT-TEST-CERT',
            'administered_at' => Carbon::now(),
            'observation_status' => VaccinationRecord::STATUS_CLEARED,
        ]);

        $response = $this->actingAs($this->receptionistUser)->get(
            route('reception.vaccinations.certificate', $record)
        );

        $response->assertStatus(200);
        $response->assertSee('GIẤY XÁC NHẬN TIÊM CHỦNG NGOẠI TRÚ');
        $response->assertSee('CERTIFICATE OF VACCINATION OR PROPHYLAXIS');
        $response->assertSee($this->patientProfile->full_name);
        $response->assertSee($this->vaccine->name);
    }
}
