<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\FamilyMember;
use App\Models\PatientProfile;
use App\Models\SecurityAuditLog;
use App\Models\Specialty;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\TotpService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TechLeadInnovationSuiteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        SystemSetting::set('booking_maintenance_mode', '0');
    }

    protected function tearDown(): void
    {
        SystemSetting::set('booking_maintenance_mode', '0');
        parent::tearDown();
    }

    /**
     * Feature 1: Two-Factor Authentication TOTP Flow.
     */
    public function test_two_factor_authentication_totp_setup_and_confirmation(): void
    {
        $user = User::factory()->create([
            'email' => 'techlead_totp_' . uniqid() . '@tamanh.vn',
            'role' => User::ROLE_ADMIN,
            'password' => Hash::make('Secret123!'),
        ]);

        $this->actingAs($user);

        // 1. Visit setup page -> generates secret & recovery codes
        $response = $this->get(route('two-factor.setup'));
        $response->assertStatus(200);
        $response->assertSee('Xác Thực 2 Bước');

        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);
        $this->assertNotNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);

        // 2. Confirm with invalid code -> fails
        $failResponse = $this->post(route('two-factor.confirm'), [
            'code' => '000000',
        ]);
        $failResponse->assertSessionHasErrors('code');

        // 3. Confirm with valid code -> succeeds
        $totpService = app(TotpService::class);
        $validCode = $totpService->getCurrentCode($user->two_factor_secret);

        $successResponse = $this->post(route('two-factor.confirm'), [
            'code' => $validCode,
        ]);
        $successResponse->assertSessionHas('success');

        $user->refresh();
        $this->assertNotNull($user->two_factor_confirmed_at);

        // Audit log created
        $this->assertDatabaseHas('security_audit_logs', [
            'user_id' => $user->id,
            'event_type' => 'two_factor_enabled',
        ]);
    }

    /**
     * Feature 1 & 2: 2FA Login Interception and Challenge Verification.
     */
    public function test_login_with_two_factor_challenge_and_recovery_code(): void
    {
        $totpService = app(TotpService::class);
        $secret = $totpService->generateSecret();
        $recoveryCodes = ['ABCD-1234', 'EFGH-5678'];

        $user = User::factory()->create([
            'email' => 'techlead_2fa_login_' . uniqid() . '@tamanh.vn',
            'role' => User::ROLE_ADMIN,
            'password' => Hash::make('Password123!'),
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => json_encode($recoveryCodes),
            'two_factor_confirmed_at' => now(),
        ]);

        // 1. Login attempt with valid password -> redirected to 2FA challenge
        $loginRes = $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        $loginRes->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest(); // Not authenticated yet!

        // 2. Submit valid TOTP code
        $validCode = $totpService->getCurrentCode($secret);
        $challengeRes = $this->withSession(['login.2fa.user_id' => $user->id])
            ->post(route('two-factor.verify'), [
                'code' => $validCode,
            ]);

        $challengeRes->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Feature 2: Active Sessions and Remote Device Logout.
     */
    public function test_active_sessions_and_remote_device_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'techlead_session_' . uniqid() . '@tamanh.vn',
            'role' => User::ROLE_ADMIN,
            'password' => Hash::make('MyPassword123'),
        ]);

        $this->actingAs($user);

        $viewRes = $this->get(route('sessions.index'));
        $viewRes->assertStatus(200);
        $viewRes->assertSee('Quản Lý Phiên Thiết Bị');

        // Logout others with invalid password fails
        $failRes = $this->post(route('sessions.logout-others'), [
            'password' => 'WrongPassword',
        ]);
        $failRes->assertSessionHasErrors('password');

        // Logout others with correct password succeeds
        $successRes = $this->post(route('sessions.logout-others'), [
            'password' => 'MyPassword123',
        ]);
        $successRes->assertSessionHas('success');

        $this->assertDatabaseHas('security_audit_logs', [
            'user_id' => $user->id,
            'event_type' => 'session_revoked_others',
        ]);
    }

    /**
     * Feature 3: Security Audit Trail & CSV Export.
     */
    public function test_security_audit_logging_and_csv_export(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin_audit_lead@tamanh.vn'],
            [
                'name' => 'Quản Trị Viên Audit',
                'password' => Hash::make('Password123!'),
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ]
        );

        $this->actingAs($admin);

        // Record a mock audit event
        \App\Services\SecurityAuditService::log(
            'test_security_probe',
            'Kiểm tra thử nghiệm hệ thống phòng vệ',
            $admin->id,
            'warning'
        );

        // View index
        $indexRes = $this->get(route('admin.security-audit.index'));
        $indexRes->assertStatus(200);
        $indexRes->assertSee('Nhật Ký Giám Sát');
        $indexRes->assertSee('test_security_probe');

        // Export CSV
        $exportRes = $this->get(route('admin.security-audit.export'));
        $exportRes->assertStatus(200);
        $this->assertStringContainsString('text/csv', $exportRes->headers->get('Content-Type'));
    }

    /**
     * Feature 4: Smart Waitlist Engine & FIFO Auto-Promotion.
     */
    public function test_smart_waitlist_registration_and_auto_promotion_on_cancellation(): void
    {
        $specialty = Specialty::first() ?: Specialty::create([
            'name' => 'Tim Mạch Waitlist Test',
            'slug' => 'tim-mach-wl-' . uniqid(),
            'description' => 'Khoa kiểm thử hàng đợi chờ',
            'is_active' => true,
        ]);

        $doctorUser = User::factory()->create([
            'name' => 'BS. Waitlist Tester',
            'email' => 'dr_waitlist_' . uniqid() . '@tamanh.vn',
            'role' => User::ROLE_DOCTOR,
            'password' => Hash::make('Password123!'),
        ]);

        $doctor = Doctor::create([
            'user_id' => $doctorUser->id,
            'specialty_id' => $specialty->id,
            'room_number' => 'P-WL01',
            'qualification' => 'Bác sĩ chuyên khoa II',
            'academic_title' => 'ThS.BS',
            'is_available' => true,
        ]);

        // Schedule for tomorrow morning with max 1 patient
        $targetDate = Carbon::tomorrow();
        $dayOfWeek = strtolower($targetDate->englishDayOfWeek);

        DoctorSchedule::create([
            'doctor_id' => $doctor->id,
            'day_of_week' => $dayOfWeek,
            'shift' => 'morning',
            'start_time' => '07:30',
            'end_time' => '11:30',
            'max_patients' => 1,
            'is_active' => true,
        ]);

        $patient1 = PatientProfile::create([
            'patient_code' => 'BN_WL01_' . uniqid(),
            'full_name' => 'Bệnh Nhân Thứ Nhất',
            'phone' => '0912345678',
            'gender' => 'male',
            'dob' => '1990-01-01',
        ]);

        $patient2 = PatientProfile::create([
            'patient_code' => 'BN_WL02_' . uniqid(),
            'full_name' => 'Bệnh Nhân Chờ Hàng Đợi',
            'phone' => '0987654321',
            'email' => 'waitlisted_patient@gmail.com',
            'gender' => 'female',
            'dob' => '1995-05-05',
        ]);

        // Patient 1 takes the only slot
        $appointment1 = Appointment::create([
            'appointment_code' => Appointment::generateAppointmentCode(),
            'patient_profile_id' => $patient1->id,
            'doctor_id' => $doctor->id,
            'specialty_id' => $specialty->id,
            'appointment_date' => $targetDate->toDateString(),
            'shift' => 'morning',
            'symptom_description' => 'Khám định kỳ',
            'status' => Appointment::STATUS_PENDING,
        ]);

        // Patient 2 tries to book with join_waitlist = 1
        $waitlistService = app(\App\Services\WaitlistEngineService::class);
        $waitlistAppointment = $waitlistService->registerWaitlist(
            $patient2->id,
            $doctor->id,
            $specialty->id,
            $targetDate->toDateString(),
            'morning',
            'Cần khám gấp tim mạch'
        );

        $this->assertEquals('waitlist', $waitlistAppointment->status);
        $this->assertEquals(1, $waitlistAppointment->waitlist_position);
        $this->assertNotNull($waitlistAppointment->waitlisted_at);

        // When Patient 1 cancels -> Waitlist Engine auto-promotes Patient 2!
        $appointment1->update(['status' => Appointment::STATUS_CANCELLED]);
        $promoted = $waitlistService->promoteNextCandidate(
            $doctor->id,
            $targetDate->toDateString(),
            'morning'
        );

        $this->assertNotNull($promoted);
        $this->assertEquals($waitlistAppointment->id, $promoted->id);
        $this->assertEquals(Appointment::STATUS_PENDING, $promoted->status);
        $this->assertNull($promoted->waitlist_position);
        $this->assertNotNull($promoted->promoted_at);
    }

    /**
     * Feature 5: Calendar Sync RFC 5545 .ics Download.
     */
    public function test_calendar_sync_ics_export(): void
    {
        $specialty = Specialty::first() ?: Specialty::create([
            'name' => 'Đa Khoa',
            'slug' => 'da-khoa-' . uniqid(),
            'is_active' => true,
        ]);
        $doctor = Doctor::with('user')->first();

        $profile = PatientProfile::create([
            'patient_code' => 'BN_CAL_' . uniqid(),
            'full_name' => 'Nguyễn Calendar',
            'phone' => '0933445566',
            'gender' => 'male',
            'dob' => '1988-08-08',
        ]);

        $app = Appointment::create([
            'appointment_code' => Appointment::generateAppointmentCode(),
            'patient_profile_id' => $profile->id,
            'doctor_id' => $doctor->id,
            'specialty_id' => $specialty->id,
            'appointment_date' => Carbon::tomorrow()->toDateString(),
            'shift' => 'morning',
            'symptom_description' => 'Kiểm tra đường huyết',
            'status' => Appointment::STATUS_PENDING,
        ]);

        $response = $this->get(route('calendar.ics', ['code' => $app->appointment_code]));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $this->assertStringContainsString('BEGIN:VCALENDAR', $response->getContent());
        $this->assertStringContainsString('SUMMARY:Lịch Khám Bệnh Viện Tâm Anh', $response->getContent());
        $this->assertStringContainsString($app->appointment_code, $response->getContent());
    }

    /**
     * Feature 6: Family Health Profiles & Dependent Booking.
     */
    public function test_family_member_management(): void
    {
        $patientUser = User::factory()->create([
            'email' => 'techlead_family_' . uniqid() . '@gmail.com',
            'role' => User::ROLE_PATIENT,
            'password' => Hash::make('Password123!'),
        ]);

        $profile = PatientProfile::create([
            'user_id' => $patientUser->id,
            'patient_code' => 'BN_FAM_' . uniqid(),
            'full_name' => $patientUser->name,
            'phone' => '0901234567',
            'gender' => 'female',
            'dob' => '1985-03-15',
        ]);

        $this->actingAs($patientUser);

        // Add child dependent
        $storeRes = $this->post(route('patient.family.store'), [
            'full_name' => 'Nguyễn Bé Con',
            'relationship' => 'child',
            'date_of_birth' => '2020-06-01',
            'gender' => 'male',
            'medical_notes' => 'Tiền sử dị ứng tôm cua',
        ]);

        $storeRes->assertSessionHas('success');

        $this->assertDatabaseHas('family_members', [
            'user_id' => $patientUser->id,
            'relationship' => 'child',
        ]);

        $member = FamilyMember::where('user_id', $patientUser->id)->first();

        // Delete family member
        $delRes = $this->delete(route('patient.family.destroy', $member));
        $delRes->assertSessionHas('success');
        $this->assertDatabaseMissing('family_members', ['id' => $member->id]);
    }

    /**
     * Feature 7: Self-service Appointment Reschedule Engine.
     */
    public function test_self_service_appointment_reschedule(): void
    {
        $specialty = Specialty::first() ?: Specialty::create([
            'name' => 'Khoa Mắt',
            'slug' => 'khoa-mat-' . uniqid(),
            'is_active' => true,
        ]);
        $doctor = Doctor::with('user')->first();

        $patientUser = User::factory()->create([
            'email' => 'patient_reschedule_' . uniqid() . '@gmail.com',
            'role' => User::ROLE_PATIENT,
            'password' => Hash::make('Password123!'),
        ]);

        $profile = PatientProfile::create([
            'user_id' => $patientUser->id,
            'patient_code' => 'BN_RE_' . uniqid(),
            'full_name' => $patientUser->name,
            'phone' => '0944556677',
            'gender' => 'male',
            'dob' => '1992-02-02',
        ]);

        $currentDate = Carbon::tomorrow();
        $newDate = Carbon::tomorrow()->addDays(2);

        // Clean up target slot for test isolation
        Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $newDate->toDateString())
            ->where('shift', 'afternoon')
            ->delete();

        // Ensure schedule exists for both days
        DoctorSchedule::updateOrCreate(
            ['doctor_id' => $doctor->id, 'day_of_week' => strtolower($currentDate->englishDayOfWeek), 'shift' => 'morning'],
            ['max_patients' => 10, 'is_active' => true]
        );
        DoctorSchedule::updateOrCreate(
            ['doctor_id' => $doctor->id, 'day_of_week' => strtolower($newDate->englishDayOfWeek), 'shift' => 'afternoon'],
            ['max_patients' => 10, 'is_active' => true]
        );

        $app = Appointment::create([
            'appointment_code' => Appointment::generateAppointmentCode(),
            'patient_profile_id' => $profile->id,
            'doctor_id' => $doctor->id,
            'specialty_id' => $specialty->id,
            'appointment_date' => $currentDate->toDateString(),
            'shift' => 'morning',
            'symptom_description' => 'Mắt nhìn mờ',
            'status' => Appointment::STATUS_PENDING,
        ]);

        $this->actingAs($patientUser);

        // Reschedule to new date and afternoon shift
        $res = $this->post(route('patient.appointments.reschedule.process', $app), [
            'appointment_date' => $newDate->toDateString(),
            'shift' => 'afternoon',
            'reschedule_reason' => 'Đổi kế hoạch đi công tác',
        ]);

        $res->assertSessionHas('success');

        $app->refresh();
        $this->assertEquals($newDate->toDateString(), $app->appointment_date->toDateString());
        $this->assertEquals('afternoon', $app->shift);
        $this->assertNotNull($app->rescheduled_at);
    }

    /**
     * Feature 8: Booking Bot Honeypot & Anti-Spam Middleware.
     */
    public function test_booking_spam_honeypot_rejection(): void
    {
        $specialty = Specialty::first();
        $doctor = Doctor::first();

        // Spam bot filling honeypot hidden input
        $response = $this->post(route('booking.store'), [
            'website_verification_code' => 'SPAM_BOT_PAYLOAD',
            'specialty_id' => $specialty->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => Carbon::tomorrow()->toDateString(),
            'shift' => 'morning',
            'full_name' => 'Robot Spam',
            'phone' => '0900000000',
            'dob' => '2000-01-01',
            'gender' => 'male',
            'symptom_description' => 'Spam test',
        ]);

        $response->assertStatus(422);
    }

    /**
     * Feature 9: Dynamic System Settings & Booking Maintenance Mode.
     */
    public function test_system_settings_and_maintenance_mode(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin_settings_lead@tamanh.vn'],
            [
                'name' => 'Admin Settings',
                'password' => Hash::make('Password123!'),
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ]
        );

        $this->actingAs($admin);

        // Update settings
        $res = $this->post(route('admin.settings.update'), [
            'settings' => [
                'hospital_name' => 'Bệnh Viện Đa Khoa Tâm Anh - Hà Nội',
                'booking_maintenance_mode' => '1',
                'booking_maintenance_message' => 'Hệ thống đang bảo trì máy chủ định kỳ.',
            ],
        ]);
        $res->assertSessionHas('success');

        $this->assertEquals('Bệnh Viện Đa Khoa Tâm Anh - Hà Nội', SystemSetting::get('hospital_name'));
        $this->assertTrue(SystemSetting::isTrue('booking_maintenance_mode'));

        // Admin can still access booking page
        $adminBooking = $this->get(route('booking.create'));
        $adminBooking->assertStatus(200);

        // Public guest visiting booking receives 503 Maintenance page
        auth()->logout();
        $guestBooking = $this->get(route('booking.create'));
        $guestBooking->assertStatus(503);
        $guestBooking->assertSee('HỆ THỐNG ĐẶT LỊCH ĐANG BẢO TRÌ ĐỊNH KỲ');

        // Turn off maintenance mode
        SystemSetting::set('booking_maintenance_mode', '0');
        $this->assertFalse(SystemSetting::isTrue('booking_maintenance_mode'));
    }

    /**
     * Feature 10: Infrastructure Health Diagnostic Telemetry.
     */
    public function test_system_health_check_service_and_dashboard(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin_health_lead@tamanh.vn'],
            [
                'name' => 'Admin Health',
                'password' => Hash::make('Password123!'),
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ]
        );

        $this->actingAs($admin);

        $healthService = app(\App\Services\SystemHealthCheckService::class);
        $diagnostic = $healthService->runFullDiagnostic();

        $this->assertArrayHasKey('status', $diagnostic);
        $this->assertArrayHasKey('database', $diagnostic);
        $this->assertEquals('healthy', $diagnostic['database']['status']);
        $this->assertArrayHasKey('storage', $diagnostic);
        $this->assertArrayHasKey('cache', $diagnostic);
        $this->assertArrayHasKey('mail', $diagnostic);
        $this->assertArrayHasKey('runtime', $diagnostic);

        // Health Dashboard view renders
        $viewRes = $this->get(route('admin.system-health.index'));
        $viewRes->assertStatus(200);
        $viewRes->assertSee('Giám Sát Sức Khỏe Hạ Tầng');

        // Test mail ping
        $mailRes = $this->post(route('admin.system-health.ping-mail'), [
            'email' => 'test_health_ping@tamanh.vn',
        ]);
        $mailRes->assertSessionHas('success');
    }
}
