<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\PatientNotification;
use App\Models\PatientProfile;
use App\Models\Specialty;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class PatientJourneyAndNotificationsTest extends TestCase
{
    protected User $receptionistUser;
    protected User $doctorUser;
    protected User $patientUser;
    protected Doctor $doctor;
    protected Specialty $specialty;
    protected PatientProfile $patientProfile;
    protected Appointment $appointment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receptionistUser = User::where('role', User::ROLE_RECEPTIONIST)->first()
            ?? User::factory()->create(['role' => User::ROLE_RECEPTIONIST]);

        $this->doctorUser = User::where('role', User::ROLE_DOCTOR)->first()
            ?? User::factory()->create(['role' => User::ROLE_DOCTOR]);

        $this->patientUser = User::factory()->create([
            'role' => User::ROLE_PATIENT,
            'email' => 'journey_' . uniqid() . '@tamanh.vn',
        ]);

        $this->doctor = Doctor::where('user_id', $this->doctorUser->id)->first()
            ?? Doctor::factory()->create(['user_id' => $this->doctorUser->id]);

        $this->specialty = Specialty::first() ?? Specialty::factory()->create();

        $this->patientProfile = PatientProfile::create([
            'user_id' => $this->patientUser->id,
            'patient_code' => 'BN-' . date('Ymd') . '-' . uniqid(),
            'full_name' => 'Nguyễn Thị Thu Hà',
            'dob' => '1992-06-15',
            'gender' => 'female',
            'phone' => '0912345678',
        ]);

        $this->appointment = Appointment::create([
            'appointment_code' => 'AP-JOURNEY-' . uniqid(),
            'patient_profile_id' => $this->patientProfile->id,
            'doctor_id' => $this->doctor->id,
            'specialty_id' => $this->specialty->id,
            'appointment_date' => Carbon::today(),
            'shift' => 'morning',
            'queue_number' => 88,
            'status' => Appointment::STATUS_CONFIRMED,
            'symptom_description' => 'Khám đau đầu chóng mặt',
        ]);
    }

    public function test_public_user_can_view_realtime_patient_journey(): void
    {
        $response = $this->get(route('public.journey.show', $this->appointment->appointment_code));

        $response->assertStatus(200);
        $response->assertSee('Hành Trình Khám Chữa Bệnh');
        $response->assertSee($this->patientProfile->full_name);
        $response->assertSee($this->appointment->appointment_code);
        $expectedQueue = str_pad($this->appointment->queue_number, 3, '0', STR_PAD_LEFT);
        $response->assertSee($expectedQueue);
        $response->assertSee('Tiến Trình Khám Chi Tiết');
    }

    public function test_receptionist_can_dispatch_queue_alert_notification(): void
    {
        $response = $this->actingAs($this->receptionistUser)->post(
            route('reception.notifications.alert', $this->appointment)
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('patient_notifications', [
            'appointment_id' => $this->appointment->id,
            'notification_type' => PatientNotification::TYPE_QUEUE_ALERT,
            'recipient_phone' => '0912345678',
            'status' => PatientNotification::STATUS_SENT,
        ]);
    }

    public function test_receptionist_can_dispatch_prescription_ready_alert(): void
    {
        $response = $this->actingAs($this->receptionistUser)->post(
            route('reception.notifications.prescription-alert', $this->appointment)
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('patient_notifications', [
            'appointment_id' => $this->appointment->id,
            'notification_type' => PatientNotification::TYPE_PRESCRIPTION_READY,
            'recipient_phone' => '0912345678',
        ]);
    }

    public function test_receptionist_can_view_notification_hub_log(): void
    {
        // Tạo một thông báo mẫu
        PatientNotification::create([
            'appointment_id' => $this->appointment->id,
            'patient_profile_id' => $this->patientProfile->id,
            'channel' => PatientNotification::CHANNEL_ZALO_ZNS,
            'notification_type' => PatientNotification::TYPE_QUEUE_ALERT,
            'recipient_phone' => '0912345678',
            'title' => 'Test Notification Hub',
            'message_content' => 'Nội dung thông báo kiểm thử',
            'status' => PatientNotification::STATUS_SENT,
            'sent_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->receptionistUser)->get(route('reception.notifications.index'));

        $response->assertStatus(200);
        $response->assertSee('Trung Tâm Thông Báo Đa Kênh');
        $response->assertSee('0912345678');
        $response->assertSee('Test Notification Hub');
    }
}
