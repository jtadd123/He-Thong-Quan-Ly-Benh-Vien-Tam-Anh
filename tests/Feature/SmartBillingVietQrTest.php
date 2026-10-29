<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\PatientInvoice;
use App\Models\PatientProfile;
use App\Models\Prescription;
use App\Models\Specialty;
use App\Models\User;
use App\Services\VietQrPaymentService;
use Carbon\Carbon;
use Tests\TestCase;

class SmartBillingVietQrTest extends TestCase
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
                'email' => 'rec_bill_' . uniqid() . '@tamanh.vn',
                'role' => User::ROLE_RECEPTIONIST,
            ]);

        $this->patient = PatientProfile::first() ?? PatientProfile::create([
            'user_id' => $this->receptionist->id,
            'patient_code' => 'BN' . rand(100000, 999999),
            'full_name' => 'Võ Thị Viện Phí',
            'dob' => '1995-10-10',
            'gender' => 'female',
            'insurance_code' => 'DN4797931800001', // Thẻ DN đúng tuyến hưởng 80%
            'phone' => '0944556677',
        ]);

        $specialty = Specialty::first() ?? Specialty::create([
            'name' => 'Khoa Khám Yêu Cầu',
            'slug' => 'khoa-kham-yeu-cau',
            'description' => 'Khám VIP',
            'is_active' => true,
        ]);

        $this->doctor = Doctor::first() ?? Doctor::create([
            'user_id' => User::factory()->create(['role' => User::ROLE_DOCTOR])->id,
            'specialty_id' => $specialty->id,
            'title' => 'TS.BS',
            'qualification' => 'Tiến sĩ Y khoa',
            'consultation_fee' => 300000,
            'room_number' => 'VIP01',
            'is_available' => true,
        ]);

        $this->appointment = Appointment::create([
            'appointment_code' => 'AP-BILL-' . uniqid(),
            'patient_profile_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'specialty_id' => $specialty->id,
            'appointment_date' => Carbon::today(),
            'shift' => 'morning',
            'status' => Appointment::STATUS_COMPLETED,
            'symptom_description' => 'Khám đau dạ dày',
        ]);

        $this->record = MedicalRecord::create([
            'record_code' => 'MR-BILL-' . uniqid(),
            'appointment_id' => $this->appointment->id,
            'patient_profile_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'symptoms' => 'Đau thượng vị',
            'diagnosis' => 'Viêm loét dạ dày tá tràng',
        ]);

        Prescription::create([
            'medical_record_id' => $this->record->id,
            'medication_name' => 'Nexium 40mg',
            'dosage' => '1 viên',
            'frequency' => 'Uống trước ăn sáng',
            'duration_days' => 14,
        ]);
    }

    public function test_can_create_invoice_from_appointment_with_insurance_copay(): void
    {
        $response = $this->actingAs($this->receptionist)
            ->post(route('reception.invoices.create-from-appointment', $this->appointment));

        $invoice = PatientInvoice::where('appointment_id', $this->appointment->id)->first();
        $this->assertNotNull($invoice);
        $response->assertRedirect(route('reception.invoices.show', $invoice));

        $this->assertGreaterThan(0, $invoice->consultation_fee);
        $this->assertGreaterThan(0, $invoice->medication_fee);
        $this->assertEquals($invoice->consultation_fee + $invoice->medication_fee, $invoice->total_amount);
        $this->assertEquals(PatientInvoice::STATUS_UNPAID, $invoice->payment_status);
        $this->assertStringStartsWith('HD-', $invoice->invoice_code);
    }

    public function test_vietqr_service_generates_valid_napas247_image_url(): void
    {
        $invoice = PatientInvoice::create([
            'invoice_code' => 'HD-TEST-' . uniqid(),
            'patient_profile_id' => $this->patient->id,
            'appointment_id' => $this->appointment->id,
            'consultation_fee' => 300000,
            'total_amount' => 300000,
            'insurance_covered_amount' => 0,
            'patient_co_pay_amount' => 300000,
            'payment_status' => PatientInvoice::STATUS_UNPAID,
        ]);

        $qrUrl = VietQrPaymentService::generateQrUrl($invoice, 'compact2');

        $this->assertStringContainsString('https://img.vietqr.io/image/ICB-110002868888-compact2.png', $qrUrl);
        $this->assertStringContainsString('amount=300000', $qrUrl);
        $this->assertStringContainsString($invoice->invoice_code, $qrUrl);
    }

    public function test_receptionist_can_confirm_payment_via_vietqr(): void
    {
        $invoice = PatientInvoice::create([
            'invoice_code' => 'HD-PAY-' . uniqid(),
            'patient_profile_id' => $this->patient->id,
            'consultation_fee' => 300000,
            'total_amount' => 300000,
            'insurance_covered_amount' => 0,
            'patient_co_pay_amount' => 300000,
            'payment_status' => PatientInvoice::STATUS_UNPAID,
        ]);

        $response = $this->actingAs($this->receptionist)
            ->post(route('reception.invoices.confirm-payment', $invoice), [
                'payment_method' => 'vietqr',
                'notes' => 'Bệnh nhân quét mã VietQR thành công',
            ]);

        $response->assertRedirect(route('reception.invoices.show', $invoice));
        $this->assertEquals(PatientInvoice::STATUS_PAID, $invoice->fresh()->payment_status);
        $this->assertEquals('vietqr', $invoice->fresh()->payment_method);
        $this->assertNotNull($invoice->fresh()->paid_at);
        $this->assertEquals($this->receptionist->id, $invoice->fresh()->received_by_user_id);
    }

    public function test_user_can_print_official_invoice(): void
    {
        $invoice = PatientInvoice::create([
            'invoice_code' => 'HD-PRINT-' . uniqid(),
            'patient_profile_id' => $this->patient->id,
            'consultation_fee' => 300000,
            'total_amount' => 300000,
            'patient_co_pay_amount' => 300000,
            'payment_status' => PatientInvoice::STATUS_PAID,
            'paid_at' => now(),
            'received_by_user_id' => $this->receptionist->id,
        ]);

        $response = $this->actingAs($this->receptionist)
            ->get(route('reception.invoices.print', $invoice));

        $response->assertStatus(200);
        $response->assertSee('BIÊN LAI THU VIỆN PHÍ NGOẠI TRÚ');
        $response->assertSee($this->patient->full_name);
        $response->assertSee($invoice->invoice_code);
    }

    public function test_public_billing_lookup_allows_patient_to_view_and_pay(): void
    {
        $invoice = PatientInvoice::create([
            'invoice_code' => 'HD-PUBLIC-' . uniqid(),
            'patient_profile_id' => $this->patient->id,
            'consultation_fee' => 300000,
            'total_amount' => 300000,
            'patient_co_pay_amount' => 300000,
            'payment_status' => PatientInvoice::STATUS_UNPAID,
        ]);

        $response = $this->get(route('public.billing.lookup', ['code' => $invoice->invoice_code]));

        $response->assertStatus(200);
        $response->assertSee('CỔNG TRA CỨU VIỆN PHÍ');
        $response->assertSee($this->patient->full_name);
        $response->assertSee($invoice->invoice_code);
        $response->assertSee('Quét Mã VietQR');
    }
}
