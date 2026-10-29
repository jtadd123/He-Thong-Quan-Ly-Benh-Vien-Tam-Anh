<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\PatientProfile;
use App\Models\Prescription;
use App\Models\Specialty;
use App\Models\User;
use App\Services\MedicationScheduleService;
use Carbon\Carbon;
use Tests\TestCase;

class MedicationScheduleIcalTest extends TestCase
{
    protected User $receptionist;
    protected PatientProfile $patient;
    protected Doctor $doctor;
    protected MedicalRecord $record;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receptionist = User::where('role', User::ROLE_RECEPTIONIST)->first()
            ?? User::factory()->create([
                'email' => 'reception_ical_' . uniqid() . '@tamanh.vn',
                'role' => User::ROLE_RECEPTIONIST,
            ]);

        $this->patient = PatientProfile::first() ?? PatientProfile::create([
            'user_id' => $this->receptionist->id,
            'patient_code' => 'BN' . rand(100000, 999999),
            'full_name' => 'Trần Văn Lịch Thuốc',
            'dob' => '1985-04-12',
            'gender' => 'male',
            'phone' => '0987654321',
        ]);

        $specialty = Specialty::first() ?? Specialty::create([
            'name' => 'Khoa Nội Tổng Hợp',
            'slug' => 'khoa-noi-tong-hop',
            'description' => 'Khoa Nội',
            'is_active' => true,
        ]);

        $this->doctor = Doctor::first() ?? Doctor::create([
            'user_id' => User::factory()->create(['role' => User::ROLE_DOCTOR])->id,
            'specialty_id' => $specialty->id,
            'title' => 'PGS.TS',
            'qualification' => 'Bác sĩ Nội khoa Chuyên khoa II',
            'room_number' => 'NOI01',
            'is_available' => true,
        ]);

        $appointment = Appointment::create([
            'appointment_code' => 'AP-SCHED-' . uniqid(),
            'patient_profile_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'specialty_id' => $specialty->id,
            'appointment_date' => Carbon::today(),
            'shift' => 'morning',
            'status' => Appointment::STATUS_COMPLETED,
            'symptom_description' => 'Tăng huyết áp kèm sốt ho',
        ]);

        $this->record = MedicalRecord::create([
            'record_code' => 'MR-SCHED-' . uniqid(),
            'appointment_id' => $appointment->id,
            'patient_profile_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'symptoms' => 'Tăng huyết áp, mệt mỏi',
            'diagnosis' => 'Tăng huyết áp độ 2 & Viêm hô hấp trên',
            'dispensed_at' => Carbon::today()->setTime(9, 30),
        ]);

        Prescription::create([
            'medical_record_id' => $this->record->id,
            'medication_name' => 'Amlodipin 5mg',
            'dosage' => '1 viên',
            'frequency' => 'Uống 1 lần/ngày vào buổi sáng',
            'duration_days' => 14,
            'instructions' => 'Uống sau ăn sáng',
        ]);

        Prescription::create([
            'medical_record_id' => $this->record->id,
            'medication_name' => 'Paracetamol 500mg',
            'dosage' => '1 viên',
            'frequency' => '3 lần/ngày (Sáng, Trưa, Tối)',
            'duration_days' => 5,
            'instructions' => 'Uống sau bữa ăn no',
        ]);

        Prescription::create([
            'medical_record_id' => $this->record->id,
            'medication_name' => 'Seduxen 5mg',
            'dosage' => '0.5 viên',
            'frequency' => '1 lần trước khi đi ngủ',
            'duration_days' => 7,
            'instructions' => 'Uống trước lúc ngủ 30 phút',
        ]);
    }

    public function test_service_parses_prescriptions_into_4_daily_sessions(): void
    {
        $service = new MedicationScheduleService();
        $schedule = $service->parseDailySchedule($this->record);

        $this->assertCount(4, $schedule['sessions']);
        $this->assertNotEmpty($schedule['sessions']['morning']['items'], 'Buổi sáng phải có thuốc');
        $this->assertNotEmpty($schedule['sessions']['noon']['items'], 'Buổi trưa phải có thuốc Paracetamol');
        $this->assertNotEmpty($schedule['sessions']['evening']['items'], 'Buổi tối phải có thuốc Paracetamol');
        $this->assertNotEmpty($schedule['sessions']['bedtime']['items'], 'Trước khi ngủ phải có thuốc Seduxen');

        $this->assertEquals(14, $schedule['max_duration_days']);
        $this->assertEquals(3, $schedule['total_medications']);
    }

    public function test_service_calculates_smart_follow_up_recall_date(): void
    {
        $service = new MedicationScheduleService();
        $followUp = $service->calculateFollowUpRecall($this->record);

        $this->assertNotNull($followUp['date']);
        $this->assertNotNull($followUp['formatted']);
        $this->assertNotEmpty($followUp['day_of_week']);
        // Max duration days is 14 days
        $this->assertEquals(
            Carbon::parse($this->record->dispensed_at)->addDays(14)->format('d/m/Y'),
            $followUp['formatted']
        );
    }

    public function test_service_generates_valid_rfc5545_icalendar_content(): void
    {
        $service = new MedicationScheduleService();
        $ical = $service->generateIcalendar($this->record);

        $this->assertStringContainsString('BEGIN:VCALENDAR', $ical);
        $this->assertStringContainsString('VERSION:2.0', $ical);
        $this->assertStringContainsString('PRODID:-//BV Da Khoa Tam Anh', $ical);
        $this->assertStringContainsString('BEGIN:VEVENT', $ical);
        $this->assertStringContainsString('RRULE:FREQ=DAILY', $ical);
        $this->assertStringContainsString('BEGIN:VALARM', $ical);
        $this->assertStringContainsString('END:VCALENDAR', $ical);
    }

    public function test_receptionist_can_view_schedule_page(): void
    {
        $response = $this->actingAs($this->receptionist)
            ->get(route('reception.pharmacy.schedule', $this->record));

        $response->assertStatus(200);
        $response->assertSee('Thời Gian Biểu Uống Thuốc');
        $response->assertSee($this->patient->full_name);
        $response->assertSee('Amlodipin 5mg');
        $response->assertSee('Hẹn Tái Khám Tự Động');
    }

    public function test_user_can_download_ical_file(): void
    {
        $response = $this->actingAs($this->receptionist)
            ->get(route('reception.pharmacy.schedule.ical', $this->record));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="BVTamAnh_LichUongThuoc_' . $this->record->record_code . '.ics"');
        $this->assertStringContainsString('BEGIN:VCALENDAR', $response->getContent());
    }
}
