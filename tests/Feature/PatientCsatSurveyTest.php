<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\PatientProfile;
use App\Models\PatientSurvey;
use App\Models\Specialty;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class PatientCsatSurveyTest extends TestCase
{
    protected User $receptionistUser;
    protected User $patientUser;
    protected PatientProfile $patientProfile;
    protected Doctor $doctor;
    protected Appointment $appointment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receptionistUser = User::where('role', User::ROLE_RECEPTIONIST)->first()
            ?? User::factory()->create(['role' => User::ROLE_RECEPTIONIST]);

        $this->patientUser = User::factory()->create([
            'role' => User::ROLE_PATIENT,
            'email' => 'pat_srv_' . uniqid() . '@tamanh.vn',
        ]);

        $this->patientProfile = PatientProfile::create([
            'user_id' => $this->patientUser->id,
            'patient_code' => 'BN-' . date('Ymd') . '-' . uniqid(),
            'full_name' => 'Hoàng Minh Tuấn',
            'dob' => '1990-11-15',
            'gender' => 'male',
            'phone' => '0988112233',
        ]);

        $doctorUser = User::factory()->create([
            'role' => User::ROLE_DOCTOR,
            'email' => 'doc_srv_' . uniqid() . '@tamanh.vn',
        ]);

        $specialty = Specialty::first() ?? Specialty::create([
            'name' => 'Tim Mạch Can Thiệp',
            'code' => 'TMCT-' . uniqid(),
            'description' => 'Khoa Tim Mạch BV ĐK Tâm Anh',
        ]);

        $this->doctor = Doctor::create([
            'user_id' => $doctorUser->id,
            'specialty_id' => $specialty->id,
            'qualification' => 'TS.BS',
            'consultation_fee' => 600000,
            'room_number' => 'P.405',
            'is_available' => true,
        ]);

        $this->appointment = Appointment::create([
            'appointment_code' => 'AP-SRV-' . uniqid(),
            'patient_profile_id' => $this->patientProfile->id,
            'doctor_id' => $this->doctor->id,
            'specialty_id' => $specialty->id,
            'appointment_date' => Carbon::today(),
            'shift' => 'morning',
            'queue_number' => 25,
            'status' => Appointment::STATUS_COMPLETED,
            'symptom_description' => 'Khám tổng quát sức khỏe',
        ]);
    }

    public function test_public_patient_can_view_survey_form(): void
    {
        $response = $this->get(route('public.survey.create', $this->appointment->appointment_code));

        $response->assertStatus(200);
        $response->assertSee('KHẢO SÁT Ý KIẾN NGƯỜI BỆNH');
        $response->assertSee('Hoàng Minh Tuấn');
        $response->assertSee('Bộ Y Tế');
    }

    public function test_patient_can_submit_satisfactory_survey_without_followup(): void
    {
        $response = $this->post(route('public.survey.store'), [
            'appointment_id' => $this->appointment->id,
            'patient_profile_id' => $this->patientProfile->id,
            'rating_reception' => 5,
            'rating_doctor' => 5,
            'rating_facilities' => 5,
            'rating_waiting_time' => 5,
            'nps_score' => 10,
            'feedback_text' => 'Bác sĩ và điều dưỡng rất tận tình, cơ sở vật chất 5 sao đẳng cấp!',
        ]);

        $response->assertRedirect(route('public.survey.thankyou'));

        $survey = PatientSurvey::where('appointment_id', $this->appointment->id)->first();
        $this->assertNotNull($survey);
        $this->assertStringStartsWith('KS-', $survey->survey_code);
        $this->assertEquals(5.0, (float)$survey->overall_score);
        $this->assertEquals(10, $survey->nps_score);
        $this->assertFalse((bool)$survey->requires_followup);
        $this->assertEquals(PatientSurvey::STATUS_CLOSED, $survey->followup_status);
    }

    public function test_dissatisfied_patient_survey_automatically_flags_red_for_customer_service(): void
    {
        $response = $this->post(route('public.survey.store'), [
            'appointment_id' => $this->appointment->id,
            'patient_profile_id' => $this->patientProfile->id,
            'rating_reception' => 3,
            'rating_doctor' => 4,
            'rating_facilities' => 3,
            'rating_waiting_time' => 1, // Đánh giá 1 sao thời gian chờ
            'nps_score' => 3, // Detractor
            'feedback_text' => 'Thời gian chờ đợi lấy thuốc ngoại trú quá lâu, cần cải thiện!',
        ]);

        $response->assertRedirect(route('public.survey.thankyou'));

        $survey = PatientSurvey::where('appointment_id', $this->appointment->id)->latest()->first();
        $this->assertNotNull($survey);
        $this->assertTrue((bool)$survey->requires_followup, 'Hệ thống phải tự động bật cờ đỏ cảnh báo CSKH khi điểm hài lòng thấp');
        $this->assertEquals(PatientSurvey::STATUS_PENDING, $survey->followup_status);
    }

    public function test_receptionist_can_view_csat_nps_dashboard(): void
    {
        // Tạo khảo sát mẫu
        PatientSurvey::create([
            'survey_code' => PatientSurvey::generateSurveyCode(),
            'appointment_id' => $this->appointment->id,
            'patient_profile_id' => $this->patientProfile->id,
            'rating_reception' => 5,
            'rating_doctor' => 5,
            'rating_facilities' => 4,
            'rating_waiting_time' => 4,
            'overall_score' => 4.5,
            'nps_score' => 9,
            'requires_followup' => false,
            'followup_status' => PatientSurvey::STATUS_CLOSED,
        ]);

        $response = $this->actingAs($this->receptionistUser)->get(route('reception.surveys.index'));

        $response->assertStatus(200);
        $response->assertSee('Bộ 83 Tiêu chí');
        $response->assertSee('Điểm CSAT Trung Bình');
        $response->assertSee('NPS Score');
    }

    public function test_cs_team_can_resolve_followup_complaint(): void
    {
        $survey = PatientSurvey::create([
            'survey_code' => PatientSurvey::generateSurveyCode(),
            'appointment_id' => $this->appointment->id,
            'patient_profile_id' => $this->patientProfile->id,
            'rating_reception' => 2,
            'rating_doctor' => 3,
            'rating_facilities' => 2,
            'rating_waiting_time' => 1,
            'overall_score' => 2.0,
            'nps_score' => 2,
            'feedback_text' => 'Không hài lòng thái độ bảo vệ bãi đỗ xe',
            'requires_followup' => true,
            'followup_status' => PatientSurvey::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->receptionistUser)->post(
            route('reception.surveys.resolve', $survey),
            [
                'followup_notes' => 'Đã chuyển phản ánh đến Tổ An Ninh bãi xe chấn chỉnh và gọi điện cảm ơn đóng góp của bệnh nhân.',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $survey->refresh();
        $this->assertEquals(PatientSurvey::STATUS_RESOLVED, $survey->followup_status);
        $this->assertEquals($this->receptionistUser->id, $survey->resolved_by_user_id);
        $this->assertNotNull($survey->resolved_at);
        $this->assertStringContainsString('Tổ An Ninh', $survey->followup_notes);
    }
}
