<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Medication;
use App\Models\PatientProfile;
use App\Models\Prescription;
use App\Models\Specialty;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FefoMedicationExpiryTest extends TestCase
{
    protected User $receptionist;
    protected Specialty $specialty;
    protected Doctor $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receptionist = User::where('role', User::ROLE_RECEPTIONIST)->first()
            ?? User::factory()->create([
                'email' => 'reception_fefo_' . uniqid() . '@tamanh.vn',
                'role' => User::ROLE_RECEPTIONIST,
            ]);

        $this->specialty = Specialty::first() ?? Specialty::create([
            'name' => 'Khoa Dược Ngoại Trú',
            'slug' => 'khoa-duoc-ngoai-tru',
            'description' => 'Khoa Dược',
            'is_active' => true,
        ]);

        $doctorUser = User::where('role', User::ROLE_DOCTOR)->first()
            ?? User::factory()->create([
                'role' => User::ROLE_DOCTOR,
                'name' => 'BS. Test Dược FEFO',
            ]);

        $this->doctor = Doctor::first() ?? Doctor::create([
            'user_id' => $doctorUser->id,
            'specialty_id' => $this->specialty->id,
            'title' => 'ThS.BS',
            'qualification' => 'Bác sĩ Chuyên khoa Dược lâm sàng',
            'room_number' => 'P101',
            'is_available' => true,
        ]);
    }

    public function test_fefo_scope_orders_medications_by_expiry_date_ascending(): void
    {
        $medExpiringLater = Medication::create([
            'medication_code' => 'MED-FAR-' . uniqid(),
            'name' => 'Thuốc Hạn Xa ' . uniqid(),
            'stock_quantity' => 50,
            'unit' => 'Viên',
            'expiry_date' => Carbon::today()->addMonths(12),
            'batch_number' => 'BATCH-FAR',
        ]);

        $medExpiringSoon = Medication::create([
            'medication_code' => 'MED-SOON-' . uniqid(),
            'name' => 'Thuốc Cận Hạn ' . uniqid(),
            'stock_quantity' => 20,
            'unit' => 'Viên',
            'expiry_date' => Carbon::today()->addDays(45),
            'batch_number' => 'BATCH-SOON',
        ]);

        $fefoList = Medication::fefoOrder()->get();

        $indexSoon = $fefoList->search(fn($m) => $m->id === $medExpiringSoon->id);
        $indexFar = $fefoList->search(fn($m) => $m->id === $medExpiringLater->id);

        $this->assertNotFalse($indexSoon);
        $this->assertNotFalse($indexFar);
        $this->assertLessThan($indexFar, $indexSoon, 'Thuốc có hạn gần hơn phải đứng trước trong danh mục FEFO');
    }

    public function test_fefo_status_and_badge_attributes(): void
    {
        $expiredMed = Medication::create([
            'medication_code' => 'MED-EXP-' . uniqid(),
            'name' => 'Thuốc Hết Hạn ' . uniqid(),
            'stock_quantity' => 10,
            'unit' => 'Hộp',
            'expiry_date' => Carbon::today()->subDays(5),
            'batch_number' => 'BATCH-EXP',
        ]);

        $criticalMed = Medication::create([
            'medication_code' => 'MED-CRIT-' . uniqid(),
            'name' => 'Thuốc Cận Hạn Nguy Hiểm ' . uniqid(),
            'stock_quantity' => 10,
            'unit' => 'Hộp',
            'expiry_date' => Carbon::today()->addDays(20),
            'batch_number' => 'BATCH-CRIT',
        ]);

        $this->assertEquals('expired', $expiredMed->fefo_status['status']);
        $this->assertStringContainsString('HẾT HẠN', $expiredMed->fefo_badge);
        $this->assertStringContainsString('bg-danger', $expiredMed->fefo_badge);

        $this->assertEquals('critical', $criticalMed->fefo_status['status']);
        $this->assertStringContainsString('Cận hạn', $criticalMed->fefo_badge);
    }

    public function test_pharmacy_inventory_filters_by_fefo(): void
    {
        $response = $this->actingAs($this->receptionist)
            ->get(route('reception.pharmacy.inventory.index', ['fefo' => 'expiring_soon']));

        $response->assertStatus(200);
        $response->assertSee('FEFO');
    }

    public function test_dispensing_blocks_when_medication_is_critically_near_expiry(): void
    {
        $expiredMed = Medication::create([
            'medication_code' => 'MED-NEAR-' . uniqid(),
            'name' => 'Kháng Sinh Cận Hạn ' . uniqid(),
            'stock_quantity' => 100,
            'unit' => 'Viên',
            'expiry_date' => Carbon::today()->addDays(15), // Dưới 30 ngày -> Nguy hiểm theo chuẩn GPP
            'batch_number' => 'BATCH-DANGER',
        ]);

        $patient = PatientProfile::first() ?? PatientProfile::create([
            'user_id' => $this->receptionist->id,
            'patient_code' => 'BN' . rand(100000, 999999),
            'full_name' => 'Bệnh Nhân Cận Hạn Test',
            'dob' => '1990-01-01',
            'gender' => 'male',
            'phone' => '0901234567',
        ]);

        $appointment = Appointment::create([
            'appointment_code' => 'AP-FEFO-' . uniqid(),
            'patient_profile_id' => $patient->id,
            'doctor_id' => $this->doctor->id,
            'specialty_id' => $this->specialty->id,
            'appointment_date' => Carbon::today(),
            'shift' => 'morning',
            'status' => Appointment::STATUS_COMPLETED,
            'symptom_description' => 'Khám kiểm tra FEFO',
        ]);

        $record = MedicalRecord::create([
            'record_code' => 'MR-FEFO-' . uniqid(),
            'appointment_id' => $appointment->id,
            'patient_profile_id' => $patient->id,
            'doctor_id' => $this->doctor->id,
            'symptoms' => 'Ho có đờm, rát họng',
            'diagnosis' => 'Viêm phế quản cấp',
        ]);

        Prescription::create([
            'medical_record_id' => $record->id,
            'medication_name' => $expiredMed->name,
            'dosage' => '500mg',
            'frequency' => '2 lần/ngày',
            'duration_days' => 5,
        ]);

        // Cố gắng phát thuốc chứa thuốc cận hạn nguy hiểm
        $response = $this->actingAs($this->receptionist)
            ->post(route('reception.pharmacy.dispense', $record), [
                'dispense_notes' => 'Cấp phát thuốc',
            ]);

        // Phải bị redirect back kèm thông báo lỗi an toàn FEFO
        $response->assertSessionHas('error');
        $this->assertNull($record->fresh()->dispensed_at, 'Đơn thuốc không được phát khi có thuốc cận hạn nguy hiểm');
    }
}
