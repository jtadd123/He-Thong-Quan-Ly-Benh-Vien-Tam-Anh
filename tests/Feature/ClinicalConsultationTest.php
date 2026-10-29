<?php

namespace Tests\Feature;

use App\Models\ClinicalConsultation;
use App\Models\Doctor;
use App\Models\PatientProfile;
use App\Models\Specialty;
use App\Models\User;
use Tests\TestCase;

class ClinicalConsultationTest extends TestCase
{
    protected User $doctorUser;
    protected User $patientUser;
    protected Doctor $doctor;
    protected Specialty $specialty1;
    protected Specialty $specialty2;
    protected PatientProfile $patientProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->doctorUser = User::where('role', User::ROLE_DOCTOR)->first()
            ?? User::factory()->create(['role' => User::ROLE_DOCTOR]);

        $this->patientUser = User::factory()->create([
            'role' => User::ROLE_PATIENT,
            'email' => 'consult_' . uniqid() . '@tamanh.vn',
        ]);

        $this->specialty1 = Specialty::first() ?? Specialty::factory()->create(['name' => 'Tim mạch học']);
        $this->specialty2 = Specialty::skip(1)->first() ?? Specialty::factory()->create(['name' => 'Nội tiết']);

        $this->doctor = Doctor::where('user_id', $this->doctorUser->id)->first()
            ?? Doctor::factory()->create(['user_id' => $this->doctorUser->id, 'specialty_id' => $this->specialty1->id]);

        $this->patientProfile = PatientProfile::create([
            'user_id' => $this->patientUser->id,
            'patient_code' => 'BN-' . date('Ymd') . '-' . uniqid(),
            'full_name' => 'Phạm Quốc Bảo',
            'dob' => '1975-08-12',
            'gender' => 'male',
            'phone' => '0933445566',
            'allergies' => 'Dị ứng Penicillin',
        ]);
    }

    public function test_doctor_can_view_consultations_index(): void
    {
        $response = $this->actingAs($this->doctorUser)->get(route('consultations.index'));

        $response->assertStatus(200);
        $response->assertSee('Hội Chẩn Đa Chuyên Khoa Nội Viện');
        $response->assertSee('Tạo Yêu Cầu Hội Chẩn');
    }

    public function test_can_create_stat_emergency_consultation_request(): void
    {
        $response = $this->actingAs($this->doctorUser)->post(
            route('consultations.store'),
            [
                'patient_profile_id' => $this->patientProfile->id,
                'target_specialty_id' => $this->specialty2->id,
                'urgency_level' => ClinicalConsultation::URGENCY_STAT,
                'reason' => 'Đái tháo đường type 2 kèm cơn tăng huyết áp kịch phát 190/110 mmHg',
                'proposed_questions' => 'Xin ý kiến phối hợp thuốc hạ áp đường tĩnh mạch và điều chỉnh liều Insulin',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $consultation = ClinicalConsultation::where('patient_profile_id', $this->patientProfile->id)->first();
        $this->assertNotNull($consultation);
        $this->assertStringStartsWith('HC-', $consultation->consultation_code);
        $this->assertEquals(ClinicalConsultation::URGENCY_STAT, $consultation->urgency_level);
        $this->assertEquals(ClinicalConsultation::STATUS_REQUESTED, $consultation->status);
        $this->assertStringContainsString('Dị ứng Penicillin', $consultation->clinical_summary);
    }

    public function test_consultant_doctor_can_respond_with_clinical_conclusion(): void
    {
        $consultation = ClinicalConsultation::create([
            'consultation_code' => ClinicalConsultation::generateConsultationCode(),
            'patient_profile_id' => $this->patientProfile->id,
            'requesting_user_id' => $this->doctorUser->id,
            'target_specialty_id' => $this->specialty2->id,
            'urgency_level' => ClinicalConsultation::URGENCY_URGENT,
            'reason' => 'Hạ kali máu nghi do thuốc',
            'clinical_summary' => 'Sinh hiệu ổn, K+ máu 2.8 mmol/L',
            'proposed_questions' => 'Xin ý kiến bù Kali đường truyền hay đường uống',
            'status' => ClinicalConsultation::STATUS_REQUESTED,
        ]);

        $response = $this->actingAs($this->doctorUser)->post(
            route('consultations.respond', $consultation),
            [
                'consultation_result' => 'Chẩn đoán: Hạ kali máu mức độ trung bình có triệu chứng yếu cơ.',
                'treatment_recommendations' => 'Bù Kali Clorid 10% pha dịch truyền tĩnh mạch chậm 40 giọt/phút kết hợp xét nghiệm lại điện giải sau 4 giờ.',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $consultation->refresh();
        $this->assertEquals(ClinicalConsultation::STATUS_COMPLETED, $consultation->status);
        $this->assertNotNull($consultation->responded_at);
        $this->assertStringContainsString('Hạ kali máu mức độ trung bình', $consultation->consultation_result);
    }

    public function test_can_render_official_consultation_print_sheet(): void
    {
        $consultation = ClinicalConsultation::create([
            'consultation_code' => ClinicalConsultation::generateConsultationCode(),
            'patient_profile_id' => $this->patientProfile->id,
            'requesting_user_id' => $this->doctorUser->id,
            'target_specialty_id' => $this->specialty2->id,
            'urgency_level' => ClinicalConsultation::URGENCY_STAT,
            'reason' => 'Hội chẩn sốc phản vệ độ II',
            'clinical_summary' => 'Khó thở, mạch nhanh, ban đỏ rải rác',
            'proposed_questions' => 'Chỉ định tiêm Adrenalin bắp',
            'consultation_result' => 'Thống nhất phác đồ cấp cứu sốc phản vệ theo Thông tư 51/2017/TT-BYT.',
            'treatment_recommendations' => 'Tiêm ngay Adrenalin 1mg 1/2 ống bắp đùi, thở oxy 5L/phút.',
            'status' => ClinicalConsultation::STATUS_COMPLETED,
        ]);

        $response = $this->actingAs($this->doctorUser)->get(route('consultations.print', $consultation));

        $response->assertStatus(200);
        $response->assertSee('TRÍCH BIÊN BẢN HỘI CHẨN CHUYÊN KHOA');
        $response->assertSee($this->patientProfile->full_name);
        $response->assertSee('Thống nhất phác đồ cấp cứu sốc phản vệ');
    }
}
