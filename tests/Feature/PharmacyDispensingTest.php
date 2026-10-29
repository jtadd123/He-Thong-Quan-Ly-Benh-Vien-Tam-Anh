<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\PatientProfile;
use App\Models\Prescription;
use App\Models\Specialty;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class PharmacyDispensingTest extends TestCase
{
    protected User $receptionistUser;
    protected User $patientUser;
    protected Doctor $doctor;
    protected Specialty $specialty;
    protected PatientProfile $patientProfile;
    protected Appointment $appointment;
    protected MedicalRecord $record;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receptionistUser = User::where('role', User::ROLE_RECEPTIONIST)->first()
            ?? User::factory()->create(['role' => User::ROLE_RECEPTIONIST]);

        $this->patientUser = User::where('role', User::ROLE_PATIENT)->first()
            ?? User::factory()->create(['role' => User::ROLE_PATIENT]);

        $doctorUser = User::where('role', User::ROLE_DOCTOR)->first()
            ?? User::factory()->create(['role' => User::ROLE_DOCTOR]);

        $this->doctor = Doctor::where('user_id', $doctorUser->id)->first()
            ?? Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $this->specialty = Specialty::first() ?? Specialty::factory()->create();

        $this->patientProfile = PatientProfile::first() ?? PatientProfile::factory()->create();

        $this->appointment = Appointment::create([
            'appointment_code' => 'AP-TEST-RX-' . uniqid(),
            'patient_profile_id' => $this->patientProfile->id,
            'doctor_id' => $this->doctor->id,
            'specialty_id' => $this->specialty->id,
            'appointment_date' => Carbon::today(),
            'shift' => 'morning',
            'queue_number' => 88,
            'status' => Appointment::STATUS_COMPLETED,
            'symptom_description' => 'Test đau đầu chóng mặt',
        ]);

        $this->record = MedicalRecord::create([
            'record_code' => MedicalRecord::generateRecordCode(),
            'appointment_id' => $this->appointment->id,
            'patient_profile_id' => $this->patientProfile->id,
            'doctor_id' => $this->doctor->id,
            'symptoms' => 'Triệu chứng kiểm tra nhà thuốc',
            'diagnosis' => 'Rối loạn tiền đình nhẹ',
        ]);

        Prescription::create([
            'medical_record_id' => $this->record->id,
            'medication_name' => 'Tanganil 500mg',
            'dosage' => '1 viên/lần',
            'frequency' => 'Ngày 2 lần sau ăn',
            'duration_days' => 5,
            'instructions' => 'Uống sau bữa ăn sáng và tối',
        ]);
    }

    public function test_receptionist_can_view_pharmacy_queue(): void
    {
        $response = $this->actingAs($this->receptionistUser)->get(route('reception.pharmacy.index'));

        $response->assertStatus(200);
        $response->assertSee('Quầy Dược & Cấp Phát Thuốc Ngoại Trú');
        $response->assertSee($this->patientProfile->full_name);
        $response->assertSee('Tanganil 500mg');
        $response->assertSee('CHỜ PHÁT THUỐC');
    }

    public function test_receptionist_can_confirm_dispensing_prescription(): void
    {
        $response = $this->actingAs($this->receptionistUser)->post(
            route('reception.pharmacy.dispense', $this->record),
            [
                'dispense_notes' => 'Đã giao 1 hộp Tanganil và hướng dẫn uống sau ăn.',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->record->refresh();
        $this->assertNotNull($this->record->dispensed_at);
        $this->assertEquals($this->receptionistUser->id, $this->record->dispensed_by_user_id);
        $this->assertEquals('Đã giao 1 hộp Tanganil và hướng dẫn uống sau ăn.', $this->record->dispense_notes);
        $this->assertTrue($this->record->isDispensed());
    }

    public function test_receptionist_can_undo_dispense(): void
    {
        $this->record->update([
            'dispensed_at' => Carbon::now(),
            'dispensed_by_user_id' => $this->receptionistUser->id,
            'dispense_notes' => 'Đã phát',
        ]);

        $response = $this->actingAs($this->receptionistUser)->post(
            route('reception.pharmacy.undo', $this->record)
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->record->refresh();
        $this->assertNull($this->record->dispensed_at);
        $this->assertNull($this->record->dispensed_by_user_id);
        $this->assertFalse($this->record->isDispensed());
    }

    public function test_receptionist_can_view_and_print_drug_label(): void
    {
        $response = $this->actingAs($this->receptionistUser)->get(
            route('reception.pharmacy.label', $this->record)
        );

        $response->assertStatus(200);
        $response->assertSee('NHÀ THUỐC BỆNH VIỆN');
        $response->assertSee($this->record->record_code);
        $response->assertSee($this->patientProfile->full_name);
        $response->assertSee('Tanganil 500mg');
        $response->assertSee('Uống sau bữa ăn sáng và tối');
    }

    public function test_patient_cannot_access_pharmacy_dispensing_desk(): void
    {
        $response = $this->actingAs($this->patientUser)->get(route('reception.pharmacy.index'));

        // Expect 403 Forbidden due to role:receptionist,admin middleware
        $response->assertStatus(403);
    }
}
