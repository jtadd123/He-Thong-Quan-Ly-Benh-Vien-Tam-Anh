<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\MedicationDelivery;
use App\Models\PatientProfile;
use App\Models\Prescription;
use App\Models\Specialty;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class MedicationDeliveryTest extends TestCase
{
    protected User $receptionistUser;
    protected User $doctorUser;
    protected Doctor $doctor;
    protected PatientProfile $patientProfile;
    protected MedicalRecord $medicalRecord;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receptionistUser = User::where('role', User::ROLE_RECEPTIONIST)->first()
            ?? User::factory()->create(['role' => User::ROLE_RECEPTIONIST]);

        $this->doctorUser = User::factory()->create([
            'role' => User::ROLE_DOCTOR,
            'email' => 'doc_deliv_' . uniqid() . '@tamanh.vn',
        ]);

        $specialty = Specialty::first() ?? Specialty::create([
            'name' => 'Nội Tiết - Đái Tháo Đường',
            'code' => 'NT-DBD-' . uniqid(),
            'description' => 'Chuyên khoa Nội tiết BVĐK Tâm Anh',
        ]);

        $this->doctor = Doctor::create([
            'user_id' => $this->doctorUser->id,
            'specialty_id' => $specialty->id,
            'qualification' => 'BS.CKII',
            'consultation_fee' => 500000,
            'room_number' => 'P.302',
            'is_available' => true,
        ]);

        $patientUser = User::factory()->create([
            'role' => User::ROLE_PATIENT,
            'email' => 'pat_deliv_' . uniqid() . '@tamanh.vn',
        ]);

        $this->patientProfile = PatientProfile::create([
            'user_id' => $patientUser->id,
            'patient_code' => 'BN-' . date('Ymd') . '-' . uniqid(),
            'full_name' => 'Trần Thị Mai',
            'dob' => '1965-08-20',
            'gender' => 'female',
            'phone' => '0938123456',
            'address' => '108 Hoàng Như Tiếp, Bồ Đề, Long Biên, Hà Nội',
        ]);

        $appointment = \App\Models\Appointment::create([
            'appointment_code' => 'AP-DELIV-' . uniqid(),
            'patient_profile_id' => $this->patientProfile->id,
            'doctor_id' => $this->doctor->id,
            'specialty_id' => $specialty->id,
            'appointment_date' => Carbon::today(),
            'shift' => 'morning',
            'queue_number' => 12,
            'status' => \App\Models\Appointment::STATUS_COMPLETED,
            'symptom_description' => 'Khám định kỳ tiểu đường',
        ]);

        $this->medicalRecord = MedicalRecord::create([
            'record_code' => MedicalRecord::generateRecordCode(),
            'appointment_id' => $appointment->id,
            'patient_profile_id' => $this->patientProfile->id,
            'doctor_id' => $this->doctor->id,
            'symptoms' => 'Mệt mỏi, khát nước, đường huyết dao động',
            'diagnosis' => 'Đái tháo đường type 2 - Tăng huyết áp độ 2',
        ]);

        Prescription::create([
            'medical_record_id' => $this->medicalRecord->id,
            'medication_name' => 'Insulin Glargine (Lantus SoloStar) 100IU/ml',
            'dosage' => '20 IU',
            'frequency' => 'Tiêm dưới da 1 lần trước khi đi ngủ',
            'duration_days' => 30,
        ]);

        Prescription::create([
            'medical_record_id' => $this->medicalRecord->id,
            'medication_name' => 'Amlodipine 5mg',
            'dosage' => '1 viên',
            'frequency' => 'Uống buổi sáng',
            'duration_days' => 30,
        ]);
    }

    public function test_receptionist_can_view_deliveries_dashboard(): void
    {
        $response = $this->actingAs($this->receptionistUser)->get(route('reception.deliveries.index'));

        $response->assertStatus(200);
        $response->assertSee('Giao Thuốc Ngoại Trú');
        $response->assertSee('Tạo Đơn Giao Thuốc');
    }

    public function test_can_create_delivery_order_and_auto_detect_cold_chain(): void
    {
        $response = $this->actingAs($this->receptionistUser)->post(
            route('reception.deliveries.store'),
            [
                'medical_record_id' => $this->medicalRecord->id,
                'recipient_name' => 'Trần Thị Mai',
                'recipient_phone' => '0938123456',
                'delivery_address' => '108 Hoàng Như Tiếp, Bồ Đề, Long Biên, Hà Nội',
                'carrier_name' => 'tam_anh_express',
                'shipping_fee' => 30000,
                'pharmacist_notes' => 'Bệnh nhân tái khám định kỳ cấp thuốc 30 ngày',
            ]
        );

        $response->assertRedirect(route('reception.deliveries.index'));
        $response->assertSessionHas('success');

        $delivery = MedicationDelivery::where('medical_record_id', $this->medicalRecord->id)->first();
        $this->assertNotNull($delivery);
        $this->assertStringStartsWith('TRK-', $delivery->tracking_code);
        $this->assertTrue((bool)$delivery->requires_cold_chain, 'Hệ thống phải tự phát hiện Insulin cần bảo quản lạnh 2-8 độ C');
        $this->assertEquals(MedicationDelivery::STATUS_PENDING_PACKAGING, $delivery->status);
        $this->assertEquals(30000, $delivery->shipping_fee);
    }

    public function test_can_transition_delivery_lifecycle_packaged_shipped_delivered(): void
    {
        $delivery = MedicationDelivery::create([
            'tracking_code' => MedicationDelivery::generateTrackingCode(),
            'patient_profile_id' => $this->patientProfile->id,
            'medical_record_id' => $this->medicalRecord->id,
            'recipient_name' => 'Trần Thị Mai',
            'recipient_phone' => '0938123456',
            'delivery_address' => '108 Hoàng Như Tiếp, Long Biên',
            'carrier_name' => 'viettel_post',
            'shipping_fee' => 35000,
            'status' => MedicationDelivery::STATUS_PENDING_PACKAGING,
            'requires_cold_chain' => true,
        ]);

        // 1. Dược sĩ đóng gói
        $packRes = $this->actingAs($this->receptionistUser)->post(
            route('reception.deliveries.status', $delivery),
            [
                'status' => 'packaged',
                'pharmacist_notes' => 'Đã đóng thùng xốp kèm 2 túi gel đá giữ nhiệt 2-8°C',
            ]
        );
        $packRes->assertRedirect();
        $delivery->refresh();
        $this->assertEquals(MedicationDelivery::STATUS_PACKAGED, $delivery->status);
        $this->assertStringContainsString('túi gel đá', $delivery->pharmacist_notes);

        // 2. Bàn giao shipper
        $shipRes = $this->actingAs($this->receptionistUser)->post(
            route('reception.deliveries.status', $delivery),
            [
                'status' => 'in_transit',
                'waybill_number' => 'VTP-8899776655',
            ]
        );
        $shipRes->assertRedirect();
        $delivery->refresh();
        $this->assertEquals(MedicationDelivery::STATUS_IN_TRANSIT, $delivery->status);
        $this->assertEquals('VTP-8899776655', $delivery->waybill_number);
        $this->assertNotNull($delivery->shipped_at);

        // 3. Hoàn tất phát tận tay
        $doneRes = $this->actingAs($this->receptionistUser)->post(
            route('reception.deliveries.status', $delivery),
            [
                'status' => 'delivered',
            ]
        );
        $doneRes->assertRedirect();
        $delivery->refresh();
        $this->assertEquals(MedicationDelivery::STATUS_DELIVERED, $delivery->status);
        $this->assertNotNull($delivery->delivered_at);
    }

    public function test_can_view_and_print_standard_pharmacy_waybill(): void
    {
        $delivery = MedicationDelivery::create([
            'tracking_code' => MedicationDelivery::generateTrackingCode(),
            'patient_profile_id' => $this->patientProfile->id,
            'medical_record_id' => $this->medicalRecord->id,
            'recipient_name' => 'Trần Thị Mai',
            'recipient_phone' => '0938123456',
            'delivery_address' => '108 Hoàng Như Tiếp, Long Biên',
            'carrier_name' => 'tam_anh_express',
            'requires_cold_chain' => true,
        ]);

        $response = $this->actingAs($this->receptionistUser)->get(
            route('reception.deliveries.waybill', $delivery)
        );

        $response->assertStatus(200);
        $response->assertSee('PHIẾU GỬI HÀNG DƯỢC');
        $response->assertSee('BẢO QUẢN LẠNH');
        $response->assertSee($delivery->tracking_code);
    }

    public function test_public_user_can_track_delivery_status(): void
    {
        $testCode = 'TRK-TEST-' . uniqid();
        $delivery = MedicationDelivery::create([
            'tracking_code' => $testCode,
            'patient_profile_id' => $this->patientProfile->id,
            'medical_record_id' => $this->medicalRecord->id,
            'recipient_name' => 'Trần Thị Mai',
            'recipient_phone' => '0938123456',
            'delivery_address' => '108 Hoàng Như Tiếp, Long Biên',
            'carrier_name' => 'tam_anh_express',
            'status' => MedicationDelivery::STATUS_IN_TRANSIT,
        ]);

        $response = $this->get(route('public.delivery.tracking', $testCode));

        $response->assertStatus(200);
        $response->assertSee('Trần Thị Mai');
        $response->assertSee('Đang Giao Hàng');
        $response->assertSee($testCode);
    }
}
