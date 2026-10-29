<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\PatientProfile;
use App\Models\Prescription;
use App\Models\Specialty;
use App\Models\User;
use App\Services\PrescriptionLabelService;
use Carbon\Carbon;
use Tests\TestCase;

class SmartAuxiliaryPrescriptionLabelTest extends TestCase
{
    protected User $receptionistUser;
    protected User $doctorUser;
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

        $this->doctorUser = User::where('role', User::ROLE_DOCTOR)->first()
            ?? User::factory()->create(['role' => User::ROLE_DOCTOR]);

        $this->patientUser = User::factory()->create([
            'role' => User::ROLE_PATIENT,
            'email' => 'label_' . uniqid() . '@tamanh.vn',
        ]);

        $this->doctor = Doctor::where('user_id', $this->doctorUser->id)->first()
            ?? Doctor::factory()->create(['user_id' => $this->doctorUser->id]);

        $this->specialty = Specialty::first() ?? Specialty::factory()->create();

        $this->patientProfile = PatientProfile::create([
            'user_id' => $this->patientUser->id,
            'patient_code' => 'BN-' . date('Ymd') . '-' . uniqid(),
            'full_name' => 'Hoàng Anh Tuấn',
            'dob' => '1985-03-25',
            'gender' => 'male',
            'phone' => '0977889900',
        ]);

        $this->appointment = Appointment::create([
            'appointment_code' => 'AP-LABEL-' . uniqid(),
            'patient_profile_id' => $this->patientProfile->id,
            'doctor_id' => $this->doctor->id,
            'specialty_id' => $this->specialty->id,
            'appointment_date' => Carbon::today(),
            'shift' => 'morning',
            'queue_number' => 99,
            'status' => Appointment::STATUS_COMPLETED,
            'symptom_description' => 'Đau dạ dày & Cảm cúm',
        ]);

        $this->record = MedicalRecord::create([
            'record_code' => MedicalRecord::generateRecordCode(),
            'appointment_id' => $this->appointment->id,
            'patient_profile_id' => $this->patientProfile->id,
            'doctor_id' => $this->doctor->id,
            'symptoms' => 'Đau khớp gối và sốt nhẹ',
            'diagnosis' => 'Thoái hóa khớp gối / Cúm mùa',
        ]);

        // Tạo 3 đơn thuốc mẫu kiểm tra các cảnh báo phụ
        Prescription::create([
            'medical_record_id' => $this->record->id,
            'medication_name' => 'Ibuprofen 400mg',
            'dosage' => '400mg',
            'frequency' => 'Ngày 2 lần',
            'duration_days' => 5,
            'instructions' => 'Sáng 1 viên, Tối 1 viên sau khi ăn no',
        ]);

        Prescription::create([
            'medical_record_id' => $this->record->id,
            'medication_name' => 'Paracetamol 500mg',
            'dosage' => '500mg',
            'frequency' => 'Khi sốt',
            'duration_days' => 3,
            'instructions' => 'Sáng 1 viên, Trưa 1 viên, Tối 1 viên khi sốt trên 38.5 độ',
        ]);

        Prescription::create([
            'medical_record_id' => $this->record->id,
            'medication_name' => 'Clorpheniramin 4mg',
            'dosage' => '4mg',
            'frequency' => 'Tối trước ngủ',
            'duration_days' => 5,
            'instructions' => 'Tối 1 viên trước khi đi ngủ',
        ]);
    }

    public function test_service_parses_dosing_and_detects_auxiliary_cautions(): void
    {
        $service = new PrescriptionLabelService();
        $labels = $service->generateLabelsForRecord($this->record);

        $this->assertCount(3, $labels);

        // 1. Ibuprofen (NSAID) -> Cảnh báo uống sau ăn no
        $ibuLabel = $labels[0];
        $this->assertEquals('Ibuprofen 400mg', $ibuLabel['medication_name']);
        $this->assertEquals(1, $ibuLabel['dosing']['morning']);
        $this->assertEquals(1, $ibuLabel['dosing']['night']);
        $this->assertStringContainsString('UỐNG SAU KHI ĂN NO', $ibuLabel['cautions'][0]['badge_text']);

        // 2. Paracetamol -> Cảnh báo không uống rượu bia
        $paraLabel = $labels[1];
        $this->assertEquals(1, $paraLabel['dosing']['morning']);
        $this->assertEquals(1, $paraLabel['dosing']['noon']);
        $this->assertEquals(1, $paraLabel['dosing']['night']);
        $this->assertStringContainsString('KHÔNG RƯỢU BIA', $paraLabel['cautions'][0]['badge_text']);

        // 3. Clorpheniramin -> Cảnh báo buồn ngủ
        $clorLabel = $labels[2];
        $this->assertEquals(1, $clorLabel['dosing']['night']);
        $this->assertStringContainsString('GÂY BUỒN NGỦ', $clorLabel['cautions'][0]['badge_text']);
    }

    public function test_pharmacist_can_render_smart_bottle_labels_view(): void
    {
        $response = $this->actingAs($this->receptionistUser)->get(
            route('reception.pharmacy.bottle-labels', $this->record)
        );

        $response->assertStatus(200);
        $response->assertSee('Tem Nhãn Dán Hộp Thuốc (75mm x 50mm)');
        $response->assertSee('Ibuprofen 400mg');
        $response->assertSee('UỐNG SAU KHI ĂN NO');
        $response->assertSee('KHÔNG RƯỢU BIA');
        $response->assertSee('GÂY BUỒN NGỦ');
        $response->assertSee($this->patientProfile->full_name);
    }
}
