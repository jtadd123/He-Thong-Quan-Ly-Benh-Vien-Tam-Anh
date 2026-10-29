<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PatientDigitalTwin;
use App\Models\TelehealthConsultation;
use App\Models\DischargeChecklist;
use App\Models\PostdischargeFollowup;
use App\Models\HealthScreeningCampaign;
use App\Models\LoyaltyPointsLog;
use App\Models\ChronicDiseaseCohort;

class PatientExperienceController extends Controller
{
    public function index()
    {
        $stats = [
            'digital_twins' => PatientDigitalTwin::count(),
            'telehealth_today' => TelehealthConsultation::whereDate('scheduled_at', today())->count(),
            'discharges_today' => DischargeChecklist::whereDate('discharge_date', today())->count(),
            'campaigns_active' => HealthScreeningCampaign::where('status', 'active')->count(),
            'chronic_enrolled' => ChronicDiseaseCohort::where('enrolled_in_program', true)->count(),
        ];
        return view('reception.patient_experience.index', compact('stats'));
    }

    public function storeDigitalTwin(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id|unique:patient_digital_twins,patient_id',
            'chronic_conditions' => 'nullable|array',
            'allergy_profile' => 'nullable|array',
            'medication_history' => 'nullable|array',
            'vaccination_record' => 'nullable|array',
        ]);

        $healthScore = PatientDigitalTwin::calculateHealthScore([
            'chronic_conditions_count' => count($validated['chronic_conditions'] ?? []),
            'er_visits' => 0,
            'hospitalizations' => 0,
        ]);
        $risk = PatientDigitalTwin::classifyRisk($healthScore);

        $twin = PatientDigitalTwin::create(array_merge($validated, [
            'health_score' => $healthScore,
            'risk_stratification' => $risk,
            'last_updated_at' => now(),
        ]));

        return response()->json(['success' => true, 'message' => 'Tạo Hồ sơ số bệnh nhân thành công', 'data' => $twin], 201);
    }

    public function storeTelehealth(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'doctor_id' => 'required|exists:doctors,id',
            'consultation_type' => 'required|in:video_call,phone_call,chat,asynchronous',
            'scheduled_at' => 'required|date|after:now',
            'consultation_reason' => 'required|in:follow_up,new_complaint,prescription_renewal,lab_review,post_discharge,chronic_disease_management',
            'chief_complaint' => 'nullable|string|max:1000',
            'consultation_fee' => 'nullable|numeric|min:0',
        ]);

        $meetingId = TelehealthConsultation::generateMeetingId();
        $link = TelehealthConsultation::generateMeetingLink($meetingId);

        $record = TelehealthConsultation::create(array_merge($validated, [
            'platform' => 'Zoom for Healthcare',
            'meeting_id' => $meetingId,
            'meeting_link' => $link,
            'status' => 'scheduled',
            'prescription_issued' => false,
            'referral_needed' => false,
            'technical_issues' => false,
        ]));

        return response()->json(['success' => true, 'message' => 'Đặt lịch khám từ xa thành công', 'data' => $record], 201);
    }

    public function storeDischargeChecklist(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'doctor_id' => 'required|exists:doctors,id',
            'discharge_date' => 'required|date',
            'discharge_type' => 'required|in:home,home_with_services,skilled_nursing,rehabilitation,another_hospital,hospice,ama,deceased',
            'discharge_summary_signed' => 'nullable|boolean',
            'medications_reconciled' => 'nullable|boolean',
            'patient_education_done' => 'nullable|boolean',
            'follow_up_appointment_booked' => 'nullable|boolean',
            'follow_up_date' => 'nullable|date|after:discharge_date',
            'red_flag_symptoms_explained' => 'nullable|boolean',
            'emergency_contact_confirmed' => 'nullable|boolean',
            'discharge_instructions' => 'nullable|string|max:3000',
            'home_care_plan' => 'nullable|string|max:3000',
        ]);

        $checklistData = array_filter($validated, fn($v) => is_bool($v));
        $completionPct = DischargeChecklist::calculateCompletionPercentage($checklistData);
        $isComplete = $completionPct >= 80;
        $riskScore = DischargeChecklist::calculateReadmissionRisk([]);

        $record = DischargeChecklist::create(array_merge($validated, [
            'checklist_complete' => $isComplete,
            'readmission_risk_score' => $riskScore,
            'medications_dispensed' => false,
            'lab_results_reviewed' => false,
            'imaging_reviewed' => false,
            'wound_care_instructions' => false,
            'activity_restrictions_explained' => false,
            'diet_instructions_given' => false,
            'transport_arranged' => false,
            'discharge_summary_signed' => (bool)($validated['discharge_summary_signed'] ?? false),
            'medications_reconciled' => (bool)($validated['medications_reconciled'] ?? false),
            'patient_education_done' => (bool)($validated['patient_education_done'] ?? false),
            'follow_up_appointment_booked' => (bool)($validated['follow_up_appointment_booked'] ?? false),
            'red_flag_symptoms_explained' => (bool)($validated['red_flag_symptoms_explained'] ?? false),
            'emergency_contact_confirmed' => (bool)($validated['emergency_contact_confirmed'] ?? false),
        ]));

        return response()->json(['success' => true, 'message' => 'Checklist xuất viện thành công', 'data' => $record], 201);
    }

    public function storeFollowup(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'doctor_id' => 'required|exists:doctors,id',
            'days_post_discharge' => 'required|integer|min:1',
            'contact_method' => 'required|in:phone_call,sms,email,app,home_visit',
            'contact_attempt' => 'required|in:first,second,third',
            'patient_reached' => 'required|boolean',
            'readmission_occurred' => 'nullable|boolean',
            'medication_adherence' => 'nullable|boolean',
            'symptoms_resolved' => 'nullable|boolean',
            'patient_wellbeing_score' => 'nullable|numeric|between:1,10',
            'call_agent_name' => 'nullable|string|max:100',
            'call_notes' => 'nullable|string|max:2000',
            'outcome' => 'nullable|in:no_issues,advice_given,prescription_renewed,appointment_scheduled,emergency_referral,readmitted',
        ]);

        $escalation = PostdischargeFollowup::needsEscalation(
            (bool)$validated['patient_reached'],
            (bool)($validated['symptoms_resolved'] ?? true),
            (float)($validated['patient_wellbeing_score'] ?? 7)
        );

        $record = PostdischargeFollowup::create(array_merge($validated, [
            'escalation_needed' => $escalation,
            'readmission_occurred' => (bool)($validated['readmission_occurred'] ?? false),
            'medication_adherence' => (bool)($validated['medication_adherence'] ?? true),
            'symptoms_resolved' => (bool)($validated['symptoms_resolved'] ?? true),
        ]));

        return response()->json(['success' => true, 'message' => 'Ghi nhận theo dõi sau xuất viện thành công', 'data' => $record], 201);
    }

    public function storeScreeningCampaign(Request $request)
    {
        $validated = $request->validate([
            'campaign_code' => 'required|string|max:50|unique:health_screening_campaigns,campaign_code',
            'campaign_name' => 'required|string|max:300',
            'campaign_type' => 'required|in:cancer_screening,cardiovascular,diabetes,hiv_hepatitis,maternal,pediatric,mental_health,occupational,community',
            'target_population' => 'required|string|max:300',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'venue' => 'required|string|max:300',
            'target_participants' => 'nullable|integer|min:0',
            'lead_doctor' => 'nullable|string|max:200',
            'sponsor' => 'nullable|string|max:200',
        ]);

        $record = HealthScreeningCampaign::create(array_merge($validated, ['status' => 'planning']));
        return response()->json(['success' => true, 'message' => 'Tạo chiến dịch tầm soát thành công', 'data' => $record], 201);
    }

    public function storeLoyaltyPoints(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'transaction_type' => 'required|in:earn,redeem,expire,bonus,adjustment',
            'points_change' => 'required|integer',
            'earn_reason' => 'nullable|string|max:300',
            'redeem_for' => 'nullable|string|max:300',
            'monetary_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $lastBalance = LoyaltyPointsLog::where('patient_id', $validated['patient_id'])->latest()->value('points_balance') ?? 0;
        $newBalance = $lastBalance + $validated['points_change'];
        if ($newBalance < 0) $newBalance = 0;
        $tier = LoyaltyPointsLog::calculateTierLevel($newBalance);

        $record = LoyaltyPointsLog::create(array_merge($validated, [
            'points_balance' => $newBalance,
            'tier_level' => $tier,
            'reference_number' => 'LYL-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5)),
        ]));

        return response()->json(['success' => true, 'message' => 'Cập nhật điểm thành viên thành công', 'data' => $record], 201);
    }

    public function storeChronicCohort(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,id',
            'doctor_id' => 'required|exists:doctors,id',
            'disease_type' => 'required|in:diabetes_t1,diabetes_t2,hypertension,copd,chf,ckd,asthma,cad,stroke,cancer,hiv,hepatitis_b,hepatitis_c,obesity,mental_health',
            'diagnosis_date' => 'required|date',
            'disease_stage' => 'required|in:early,mild,moderate,severe,end_stage',
            'care_manager' => 'nullable|string|max:200',
            'care_plan_notes' => 'nullable|string|max:3000',
        ]);

        $targetMetrics = ChronicDiseaseCohort::getTargetMetrics($validated['disease_type']);

        $record = ChronicDiseaseCohort::create(array_merge($validated, [
            'target_metrics' => $targetMetrics,
            'metrics_on_target' => false,
            'enrolled_in_program' => true,
            'engagement_level' => 'high',
            'next_review_date' => now()->addMonths(3)->toDateString(),
            'annual_visit_count' => 0,
            'er_visits_12mo' => 0,
            'hospitalizations_12mo' => 0,
        ]));

        return response()->json(['success' => true, 'message' => 'Đăng ký quản lý bệnh mãn tính thành công', 'data' => $record], 201);
    }
}
