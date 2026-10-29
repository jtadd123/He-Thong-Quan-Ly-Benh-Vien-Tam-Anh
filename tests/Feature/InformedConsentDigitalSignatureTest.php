<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\InformedConsent;
use App\Models\PatientProfile;
use App\Models\Specialty;
use App\Models\User;
use Tests\TestCase;

class InformedConsentDigitalSignatureTest extends TestCase
{
    protected User $receptionist;
    protected PatientProfile $patient;
    protected Doctor $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receptionist = User::where('role', User::ROLE_RECEPTIONIST)->first()
            ?? User::factory()->create([
                'email' => 'reception_consent_' . uniqid() . '@tamanh.vn',
                'role' => User::ROLE_RECEPTIONIST,
            ]);

        $this->patient = PatientProfile::first() ?? PatientProfile::create([
            'user_id' => $this->receptionist->id,
            'patient_code' => 'BN' . rand(100000, 999999),
            'full_name' => 'Nguyễn Thị Bích Consent',
            'dob' => '1988-06-15',
            'gender' => 'female',
            'phone' => '0912345678',
        ]);

        $specialty = Specialty::first() ?? Specialty::create([
            'name' => 'Ngoại Khoa',
            'slug' => 'ngoai-khoa',
            'description' => 'Khoa Ngoại',
            'is_active' => true,
        ]);

        $this->doctor = Doctor::first() ?? Doctor::create([
            'user_id' => User::factory()->create(['role' => User::ROLE_DOCTOR])->id,
            'specialty_id' => $specialty->id,
            'title' => 'TS.BS',
            'qualification' => 'Bác sĩ Ngoại khoa Chuyên khoa II',
            'room_number' => 'PT01',
            'is_available' => true,
        ]);
    }

    public function test_receptionist_can_view_create_consent_page(): void
    {
        $response = $this->actingAs($this->receptionist)
            ->get(route('reception.consents.create', $this->patient));

        $response->assertStatus(200);
        $response->assertSee('Bản Cam Đoan');
        $response->assertSee($this->patient->full_name);
        $response->assertSee('signatureCanvas');
    }

    public function test_receptionist_can_store_informed_consent_with_digital_signature(): void
    {
        // Fake PNG 1x1 pixel base64 data URI
        $fakeSignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $data = [
            'patient_profile_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'procedure_name' => 'Phẫu thuật nội soi cắt ruột thừa viêm',
            'department_name' => 'Khoa Ngoại Tổng Quát',
            'signer_name' => $this->patient->full_name,
            'signer_relationship' => 'patient',
            'signer_id_card' => '001090012345',
            'signer_phone' => '0912345678',
            'diagnosis_description' => 'Viêm ruột thừa cấp giờ thứ 12',
            'risks_explained' => 'Bác sĩ đã giải thích cặn kẽ về nguy cơ chảy máu, nhiễm trùng và tỷ lệ thành công của thủ thuật.',
            'patient_undertaking' => 'Tôi đã hiểu rõ và đồng ý thực hiện can thiệp phẫu thuật.',
            'signature_data' => $fakeSignature,
        ];

        $response = $this->actingAs($this->receptionist)
            ->post(route('reception.consents.store'), $data);

        $createdConsent = InformedConsent::where('patient_profile_id', $this->patient->id)->latest()->first();
        $this->assertNotNull($createdConsent);
        $response->assertRedirect(route('reception.consents.print', $createdConsent));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('informed_consents', [
            'patient_profile_id' => $this->patient->id,
            'procedure_name' => 'Phẫu thuật nội soi cắt ruột thừa viêm',
            'signer_name' => $this->patient->full_name,
            'signer_relationship' => 'patient',
        ]);

        $createdConsent = InformedConsent::where('patient_profile_id', $this->patient->id)->latest()->first();
        $this->assertNotNull($createdConsent);
        $this->assertStringStartsWith('CAM-', $createdConsent->consent_code);
        $this->assertNotNull($createdConsent->signed_at);
    }

    public function test_user_can_print_official_moh_informed_consent(): void
    {
        $fakeSignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $consent = InformedConsent::create([
            'consent_code' => 'CC-TEST-' . uniqid(),
            'patient_profile_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'procedure_name' => 'Nội soi dạ dày gây mê',
            'department_name' => 'Khoa Ngoại Tổng Quát',
            'signer_name' => $this->patient->full_name,
            'signer_relationship' => 'patient',
            'signature_data' => $fakeSignature,
            'signed_at' => now(),
            'created_by_user_id' => $this->receptionist->id,
        ]);

        $response = $this->actingAs($this->receptionist)
            ->get(route('reception.consents.print', $consent));

        $response->assertStatus(200);
        $response->assertSee('GIẤY CAM ĐOAN CHẤP NHẬN PHẪU THUẬT, THỦ THUẬT');
        $response->assertSee('Nội soi dạ dày gây mê');
        $response->assertSee($consent->consent_code);
    }

    public function test_receptionist_can_destroy_consent(): void
    {
        $consent = InformedConsent::create([
            'consent_code' => 'CC-DEL-' . uniqid(),
            'patient_profile_id' => $this->patient->id,
            'procedure_name' => 'Thủ thuật test xóa',
            'department_name' => 'Khoa Ngoại Tổng Quát',
            'signer_name' => 'Người ký test',
            'signer_relationship' => 'family',
            'signature_data' => 'data:image/png;base64,abc',
            'signed_at' => now(),
            'created_by_user_id' => $this->receptionist->id,
        ]);

        $response = $this->actingAs($this->receptionist)
            ->delete(route('reception.consents.destroy', $consent));

        $response->assertRedirect();
        $this->assertDatabaseMissing('informed_consents', [
            'id' => $consent->id,
        ]);
    }
}
