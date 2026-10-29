<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\PatientProfile;
use App\Models\Specialty;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class SmartAudioQueueCallerTest extends TestCase
{
    protected User $receptionist;
    protected PatientProfile $patient;
    protected Doctor $doctor;
    protected Appointment $appointment;
    protected MedicalRecord $record;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receptionist = User::where('role', User::ROLE_RECEPTIONIST)->first()
            ?? User::factory()->create([
                'email' => 'rec_audio_' . uniqid() . '@tamanh.vn',
                'role' => User::ROLE_RECEPTIONIST,
            ]);

        $this->patient = PatientProfile::first() ?? PatientProfile::create([
            'user_id' => $this->receptionist->id,
            'patient_code' => 'BN' . rand(100000, 999999),
            'full_name' => 'Nguyễn Văn Audio Caller',
            'dob' => '1992-05-20',
            'gender' => 'male',
            'phone' => '0933445566',
        ]);

        $specialty = Specialty::first() ?? Specialty::create([
            'name' => 'Khoa Khám Bệnh Sảnh',
            'slug' => 'khoa-kham-benh-sanh',
            'description' => 'Sảnh tiếp nhận',
            'is_active' => true,
        ]);

        $this->doctor = Doctor::first() ?? Doctor::create([
            'user_id' => User::factory()->create(['role' => User::ROLE_DOCTOR])->id,
            'specialty_id' => $specialty->id,
            'title' => 'BS.CKI',
            'qualification' => 'Bác sĩ Khám bệnh',
            'room_number' => 'P102',
            'is_available' => true,
        ]);

        $this->appointment = Appointment::create([
            'appointment_code' => 'AP-AUDIO-' . uniqid(),
            'patient_profile_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'specialty_id' => $specialty->id,
            'appointment_date' => Carbon::today(),
            'shift' => 'morning',
            'status' => Appointment::STATUS_CONFIRMED,
            'symptom_description' => 'Đến khám thường quy',
        ]);

        $this->record = MedicalRecord::create([
            'record_code' => 'MR-AUDIO-' . uniqid(),
            'appointment_id' => $this->appointment->id,
            'patient_profile_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'symptoms' => 'Cảm cúm nhẹ',
            'diagnosis' => 'Viêm mũi họng cấp',
        ]);
    }

    public function test_receptionist_can_dispatch_audio_call_to_reception_desk(): void
    {
        $response = $this->actingAs($this->receptionist)
            ->postJson(route('reception.appointments.call-audio', $this->appointment), [
                'counter_number' => 'Quầy 03',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'patient_name' => $this->patient->full_name,
            'counter' => 'Quầy 03',
        ]);

        $this->assertStringContainsString('Quầy 03', $response->json('speech_text'));
        $this->assertStringContainsString($this->patient->full_name, $response->json('speech_text'));

        $this->assertNotNull($this->appointment->fresh()->called_at);
        $this->assertEquals('Quầy 03', $this->appointment->fresh()->counter_number);
    }

    public function test_pharmacist_can_dispatch_audio_call_to_dispensing_counter(): void
    {
        $response = $this->actingAs($this->receptionist)
            ->postJson(route('reception.pharmacy.call-audio', $this->record), [
                'counter_number' => 'Quầy Dược 02',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'patient_name' => $this->patient->full_name,
            'record_code' => $this->record->record_code,
            'counter' => 'Quầy Dược 02',
        ]);

        $this->assertStringContainsString('Quầy Dược 02', $response->json('speech_text'));
        $this->assertStringContainsString($this->record->record_code, $response->json('speech_text'));
    }

    public function test_guest_cannot_trigger_audio_calls(): void
    {
        $response = $this->postJson(route('reception.appointments.call-audio', $this->appointment));
        $response->assertStatus(401);
    }
}
