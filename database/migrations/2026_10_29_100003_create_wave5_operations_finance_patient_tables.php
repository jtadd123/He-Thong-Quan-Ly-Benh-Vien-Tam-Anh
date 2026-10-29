<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // OR Block Schedules
        if (!Schema::hasTable('or_block_schedules')) {
            Schema::create('or_block_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patient_profiles')->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
                $table->string('or_room', 20)->comment('Phòng mổ');
                $table->date('surgery_date');
                $table->time('scheduled_start_time');
                $table->time('scheduled_end_time');
                $table->time('actual_start_time')->nullable();
                $table->time('actual_end_time')->nullable();
                $table->string('surgery_name')->comment('Tên phẫu thuật');
                $table->enum('surgery_category', ['elective', 'urgent', 'emergency'])->default('elective');
                $table->enum('anesthesia_type', ['general', 'regional', 'local', 'sedation'])->default('general');
                $table->string('surgical_team')->nullable();
                $table->string('anesthesia_team')->nullable();
                $table->string('scrub_nurse')->nullable();
                $table->integer('estimated_duration_minutes')->default(60);
                $table->integer('actual_duration_minutes')->nullable();
                $table->integer('turnover_time_minutes')->nullable()->comment('Thời gian dọn phòng phút');
                $table->enum('block_status', ['scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled', 'postponed'])->default('scheduled');
                $table->string('cancellation_reason')->nullable();
                $table->string('equipment_needed')->nullable();
                $table->string('implants_needed')->nullable();
                $table->decimal('case_complexity', 2, 0)->default(1)->comment('Độ phức tạp 1-5');
                $table->text('pre_op_notes')->nullable();
                $table->text('post_op_notes')->nullable();
                $table->timestamps();
                $table->index(['surgery_date', 'or_room']);
                $table->index(['patient_id', 'block_status']);
            });
        }

        // Porter Transport Requests
        if (!Schema::hasTable('porter_transport_requests')) {
            Schema::create('porter_transport_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patient_profiles')->cascadeOnDelete();
                $table->string('from_location', 100)->comment('Địa điểm đón');
                $table->string('to_location', 100)->comment('Địa điểm đến');
                $table->enum('transport_mode', ['wheelchair', 'stretcher', 'bed', 'ambulatory', 'incubator'])->default('wheelchair');
                $table->enum('priority', ['routine', 'urgent', 'emergency'])->default('routine');
                $table->string('requested_by')->comment('Người yêu cầu');
                $table->string('porter_name')->nullable()->comment('Hộ lý phụ trách');
                $table->timestamp('requested_at');
                $table->timestamp('assigned_at')->nullable();
                $table->timestamp('picked_up_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->integer('wait_time_minutes')->nullable()->comment('Thời gian chờ phút');
                $table->integer('transport_duration_minutes')->nullable()->comment('Thời gian vận chuyển phút');
                $table->enum('status', ['pending', 'assigned', 'in_progress', 'completed', 'cancelled'])->default('pending');
                $table->boolean('oxygen_required')->default(false);
                $table->boolean('iv_line_present')->default(false);
                $table->boolean('monitoring_required')->default(false);
                $table->text('special_instructions')->nullable();
                $table->timestamps();
                $table->index(['status', 'priority']);
                $table->index(['patient_id', 'created_at']);
            });
        }

        // Dietary Orders
        if (!Schema::hasTable('dietary_orders')) {
            Schema::create('dietary_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patient_profiles')->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
                $table->enum('diet_type', ['regular', 'diabetic', 'low_sodium', 'low_fat', 'renal', 'hepatic', 'high_protein', 'soft', 'liquid', 'npo', 'tube_feeding', 'parenteral'])->default('regular');
                $table->decimal('caloric_target', 7, 1)->nullable()->comment('Mục tiêu calorie kcal/ngày');
                $table->decimal('protein_target_g', 6, 1)->nullable()->comment('Protein g/ngày');
                $table->decimal('fluid_restriction_ml', 7, 1)->nullable()->comment('Hạn chế dịch mL/ngày');
                $table->decimal('sodium_restriction_mg', 7, 1)->nullable()->comment('Hạn chế Na mg/ngày');
                $table->string('allergy_notes')->nullable()->comment('Dị ứng thực phẩm');
                $table->string('food_preferences')->nullable()->comment('Sở thích ăn uống');
                $table->enum('texture', ['regular', 'minced', 'pureed', 'liquid'])->default('regular');
                $table->boolean('halal')->default(false);
                $table->boolean('vegetarian')->default(false);
                $table->string('meal_frequency')->default('3 bữa/ngày');
                $table->json('supplements')->nullable()->comment('Bổ sung dinh dưỡng JSON');
                $table->decimal('bmi', 5, 2)->nullable()->comment('BMI kg/m²');
                $table->string('nutritional_risk_score')->nullable()->comment('NRS 2002 / MUST');
                $table->enum('nutritional_status', ['well_nourished', 'at_risk', 'malnourished', 'severely_malnourished'])->default('well_nourished');
                $table->date('order_start_date');
                $table->date('order_end_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->text('dietitian_notes')->nullable();
                $table->timestamps();
                $table->index(['patient_id', 'is_active']);
                $table->index('diet_type');
            });
        }

        // DRG Billing
        if (!Schema::hasTable('drg_billing_records')) {
            Schema::create('drg_billing_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patient_profiles')->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
                $table->string('drg_code', 20)->comment('Mã DRG');
                $table->string('drg_description')->comment('Mô tả DRG');
                $table->string('mdc_code', 5)->nullable()->comment('Major Diagnostic Category');
                $table->string('principal_diagnosis_icd10', 10);
                $table->json('secondary_diagnoses')->nullable()->comment('Chẩn đoán phụ JSON');
                $table->json('procedures_icd9')->nullable()->comment('Thủ thuật ICD-9-CM JSON');
                $table->integer('length_of_stay_days')->default(1);
                $table->integer('geometric_mean_los')->nullable()->comment('LOS trung bình hình học');
                $table->decimal('drg_weight', 6, 4)->default(1.0)->comment('Trọng số DRG');
                $table->decimal('base_rate', 12, 0)->default(0)->comment('Giá cơ bản VNĐ');
                $table->decimal('expected_payment', 12, 0)->default(0)->comment('Thanh toán kỳ vọng VNĐ');
                $table->decimal('actual_cost', 12, 0)->default(0)->comment('Chi phí thực tế VNĐ');
                $table->decimal('cost_variance', 12, 0)->default(0)->comment('Chênh lệch VNĐ');
                $table->enum('mcc_cc', ['mcc', 'cc', 'no_mcc_cc'])->default('no_mcc_cc')->comment('MCC/CC');
                $table->boolean('outlier_case')->default(false)->comment('Ca ngoại lệ');
                $table->decimal('outlier_payment', 12, 0)->default(0)->comment('Thanh toán ngoại lệ VNĐ');
                $table->enum('insurance_type', ['bhyt', 'private', 'self_pay', 'workers_comp'])->default('bhyt');
                $table->string('insurance_code')->nullable();
                $table->decimal('insurance_coverage_pct', 5, 2)->default(80)->comment('Tỷ lệ BHYT %');
                $table->decimal('patient_copay', 12, 0)->default(0)->comment('Đồng chi trả VNĐ');
                $table->enum('billing_status', ['draft', 'submitted', 'pending', 'approved', 'rejected', 'paid'])->default('draft');
                $table->text('coding_notes')->nullable();
                $table->timestamps();
                $table->index(['patient_id', 'billing_status']);
                $table->index('drg_code');
            });
        }

        // Research Projects
        if (!Schema::hasTable('research_projects')) {
            Schema::create('research_projects', function (Blueprint $table) {
                $table->id();
                $table->string('project_code')->unique()->comment('Mã đề tài');
                $table->string('title')->comment('Tên đề tài');
                $table->string('principal_investigator')->comment('Chủ nhiệm đề tài');
                $table->string('department')->comment('Khoa/Phòng chủ trì');
                $table->enum('research_type', ['clinical_trial', 'observational', 'retrospective', 'case_study', 'rct', 'meta_analysis', 'quality_improvement'])->default('clinical_trial');
                $table->enum('phase', ['phase1', 'phase2', 'phase3', 'phase4', 'na'])->default('na');
                $table->string('irb_approval_number')->nullable()->comment('Số phê duyệt Hội đồng Đạo đức');
                $table->date('irb_approval_date')->nullable();
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->integer('target_enrollment')->default(0)->comment('Mục tiêu tuyển mộ');
                $table->integer('actual_enrollment')->default(0)->comment('Đã tuyển mộ');
                $table->decimal('budget_approved', 15, 0)->default(0)->comment('Ngân sách phê duyệt VNĐ');
                $table->decimal('budget_spent', 15, 0)->default(0)->comment('Đã chi VNĐ');
                $table->string('funding_source')->nullable()->comment('Nguồn tài trợ');
                $table->string('grant_number')->nullable()->comment('Số grant');
                $table->enum('status', ['planning', 'irb_pending', 'recruiting', 'data_collection', 'analysis', 'publication', 'completed', 'suspended', 'terminated'])->default('planning');
                $table->string('clinicaltrials_gov_id')->nullable()->comment('NCT number');
                $table->text('description')->nullable();
                $table->json('co_investigators')->nullable()->comment('Đồng nghiên cứu viên JSON');
                $table->timestamps();
                $table->index(['status', 'department']);
            });
        }

        // Patient Digital Twins
        if (!Schema::hasTable('patient_digital_twins')) {
            Schema::create('patient_digital_twins', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patient_profiles')->cascadeOnDelete()->unique();
                $table->json('chronic_conditions')->nullable()->comment('Bệnh mãn tính JSON');
                $table->json('allergy_profile')->nullable()->comment('Hồ sơ dị ứng JSON');
                $table->json('medication_history')->nullable()->comment('Lịch sử thuốc JSON');
                $table->json('vaccination_record')->nullable()->comment('Tiêm chủng JSON');
                $table->json('family_history')->nullable()->comment('Tiền sử gia đình JSON');
                $table->json('surgical_history')->nullable()->comment('Tiền sử phẫu thuật JSON');
                $table->json('lab_trends')->nullable()->comment('Xu hướng xét nghiệm JSON');
                $table->json('vital_trends')->nullable()->comment('Xu hướng sinh hiệu JSON');
                $table->json('wearable_data')->nullable()->comment('Dữ liệu thiết bị đeo JSON');
                $table->decimal('health_score', 5, 1)->default(100)->comment('Điểm sức khỏe 0-100');
                $table->string('risk_stratification', 20)->default('low')->comment('Phân tầng nguy cơ');
                $table->json('predicted_risks')->nullable()->comment('Nguy cơ dự báo JSON');
                $table->json('care_gaps')->nullable()->comment('Khoảng trống chăm sóc JSON');
                $table->timestamp('last_updated_at')->nullable();
                $table->timestamps();
                $table->index('health_score');
            });
        }

        // Telehealth Consultations
        if (!Schema::hasTable('telehealth_consultations')) {
            Schema::create('telehealth_consultations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patient_profiles')->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
                $table->enum('consultation_type', ['video_call', 'phone_call', 'chat', 'asynchronous'])->default('video_call');
                $table->string('platform', 50)->default('Zoom for Healthcare')->comment('Nền tảng');
                $table->string('meeting_link')->nullable()->comment('Link cuộc họp');
                $table->string('meeting_id')->nullable();
                $table->timestamp('scheduled_at');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('ended_at')->nullable();
                $table->integer('duration_minutes')->nullable();
                $table->enum('consultation_reason', ['follow_up', 'new_complaint', 'prescription_renewal', 'lab_review', 'post_discharge', 'chronic_disease_management'])->default('follow_up');
                $table->text('chief_complaint')->nullable();
                $table->text('assessment')->nullable();
                $table->text('plan')->nullable();
                $table->boolean('prescription_issued')->default(false);
                $table->boolean('referral_needed')->default(false);
                $table->string('referral_to')->nullable();
                $table->enum('status', ['scheduled', 'waiting', 'in_progress', 'completed', 'missed', 'cancelled', 'rescheduled'])->default('scheduled');
                $table->decimal('patient_satisfaction', 3, 1)->nullable()->comment('Đánh giá 1-5');
                $table->boolean('technical_issues')->default(false);
                $table->text('technical_notes')->nullable();
                $table->decimal('consultation_fee', 10, 0)->default(0)->comment('Phí khám VNĐ');
                $table->timestamps();
                $table->index(['patient_id', 'status']);
                $table->index(['doctor_id', 'scheduled_at']);
            });
        }

        // Discharge Checklists
        if (!Schema::hasTable('discharge_checklists')) {
            Schema::create('discharge_checklists', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patient_profiles')->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
                $table->date('discharge_date');
                $table->enum('discharge_type', ['home', 'home_with_services', 'skilled_nursing', 'rehabilitation', 'another_hospital', 'hospice', 'ama', 'deceased'])->default('home');
                $table->boolean('discharge_summary_signed')->default(false);
                $table->boolean('medications_reconciled')->default(false);
                $table->boolean('medications_dispensed')->default(false);
                $table->boolean('patient_education_done')->default(false);
                $table->boolean('follow_up_appointment_booked')->default(false);
                $table->date('follow_up_date')->nullable();
                $table->string('follow_up_doctor')->nullable();
                $table->boolean('lab_results_reviewed')->default(false);
                $table->boolean('imaging_reviewed')->default(false);
                $table->boolean('wound_care_instructions')->default(false);
                $table->boolean('activity_restrictions_explained')->default(false);
                $table->boolean('diet_instructions_given')->default(false);
                $table->boolean('red_flag_symptoms_explained')->default(false);
                $table->boolean('emergency_contact_confirmed')->default(false);
                $table->boolean('transport_arranged')->default(false);
                $table->string('transport_mode')->nullable()->comment('Phương tiện về');
                $table->text('discharge_instructions')->nullable();
                $table->text('home_care_plan')->nullable();
                $table->json('medications_on_discharge')->nullable()->comment('Thuốc khi xuất viện JSON');
                $table->boolean('checklist_complete')->default(false);
                $table->integer('readmission_risk_score')->default(0)->comment('Lhospital 30 ngày risk score');
                $table->text('nursing_notes')->nullable();
                $table->timestamps();
                $table->index(['patient_id', 'discharge_date']);
                $table->index('discharge_type');
            });
        }

        // Post-Discharge Follow-up
        if (!Schema::hasTable('postdischarge_followups')) {
            Schema::create('postdischarge_followups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patient_profiles')->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
                $table->integer('days_post_discharge')->comment('Ngày sau xuất viện');
                $table->enum('contact_method', ['phone_call', 'sms', 'email', 'app', 'home_visit'])->default('phone_call');
                $table->enum('contact_attempt', ['first', 'second', 'third'])->default('first');
                $table->boolean('patient_reached')->default(false);
                $table->boolean('readmission_occurred')->default(false);
                $table->date('readmission_date')->nullable();
                $table->string('readmission_reason')->nullable();
                $table->boolean('medication_adherence')->default(true)->comment('Tuân thủ thuốc');
                $table->boolean('follow_up_appointment_kept')->default(false)->comment('Giữ lịch tái khám');
                $table->boolean('symptoms_resolved')->default(true);
                $table->text('new_symptoms')->nullable();
                $table->decimal('patient_wellbeing_score', 3, 1)->nullable()->comment('Điểm 1-10');
                $table->boolean('escalation_needed')->default(false);
                $table->text('escalation_action')->nullable();
                $table->string('call_agent_name')->nullable()->comment('Nhân viên gọi');
                $table->integer('call_duration_minutes')->nullable();
                $table->text('call_notes')->nullable();
                $table->enum('outcome', ['no_issues', 'advice_given', 'prescription_renewed', 'appointment_scheduled', 'emergency_referral', 'readmitted'])->default('no_issues');
                $table->timestamps();
                $table->index(['patient_id', 'days_post_discharge']);
                $table->index(['readmission_occurred', 'created_at']);
            });
        }

        // Health Screening Campaigns
        if (!Schema::hasTable('health_screening_campaigns')) {
            Schema::create('health_screening_campaigns', function (Blueprint $table) {
                $table->id();
                $table->string('campaign_code')->unique()->comment('Mã chiến dịch');
                $table->string('campaign_name')->comment('Tên chiến dịch');
                $table->enum('campaign_type', ['cancer_screening', 'cardiovascular', 'diabetes', 'hiv_hepatitis', 'maternal', 'pediatric', 'mental_health', 'occupational', 'community'])->default('cardiovascular');
                $table->string('target_population')->comment('Đối tượng mục tiêu');
                $table->string('age_range')->nullable()->comment('Độ tuổi VD: 40-70');
                $table->date('start_date');
                $table->date('end_date');
                $table->string('venue')->comment('Địa điểm');
                $table->integer('target_participants')->default(0)->comment('Mục tiêu');
                $table->integer('actual_participants')->default(0)->comment('Thực tế');
                $table->integer('positive_findings')->default(0)->comment('Phát hiện bất thường');
                $table->integer('referred_for_followup')->default(0)->comment('Chuyển tái khám');
                $table->decimal('cost_per_participant', 10, 0)->default(0)->comment('Chi phí/người VNĐ');
                $table->decimal('total_cost', 12, 0)->default(0)->comment('Tổng chi phí VNĐ');
                $table->json('screening_tests')->nullable()->comment('Xét nghiệm tầm soát JSON');
                $table->string('lead_doctor')->nullable();
                $table->string('sponsor')->nullable()->comment('Nhà tài trợ');
                $table->enum('status', ['planning', 'active', 'completed', 'cancelled'])->default('planning');
                $table->text('outcomes_summary')->nullable();
                $table->timestamps();
                $table->index(['status', 'start_date']);
            });
        }

        // Loyalty Points
        if (!Schema::hasTable('loyalty_points_logs')) {
            Schema::create('loyalty_points_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patient_profiles')->cascadeOnDelete();
                $table->enum('transaction_type', ['earn', 'redeem', 'expire', 'bonus', 'adjustment'])->default('earn');
                $table->integer('points_change')->default(0)->comment('Điểm thay đổi');
                $table->integer('points_balance')->default(0)->comment('Số dư điểm');
                $table->string('earn_reason')->nullable()->comment('Lý do tích điểm');
                $table->string('redeem_for')->nullable()->comment('Đổi điểm cho gì');
                $table->decimal('monetary_value', 10, 0)->default(0)->comment('Giá trị quy đổi VNĐ');
                $table->string('tier_level')->default('Bronze')->comment('Hạng: Bronze/Silver/Gold/Platinum');
                $table->date('expiry_date')->nullable();
                $table->string('reference_number')->nullable()->comment('Số tham chiếu giao dịch');
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index(['patient_id', 'transaction_type']);
                $table->index(['patient_id', 'created_at']);
            });
        }

        // Chronic Disease Cohorts
        if (!Schema::hasTable('chronic_disease_cohorts')) {
            Schema::create('chronic_disease_cohorts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patient_profiles')->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
                $table->enum('disease_type', ['diabetes_t1', 'diabetes_t2', 'hypertension', 'copd', 'chf', 'ckd', 'asthma', 'cad', 'stroke', 'cancer', 'hiv', 'hepatitis_b', 'hepatitis_c', 'obesity', 'mental_health'])->default('diabetes_t2');
                $table->string('icd10_code', 10)->nullable();
                $table->date('diagnosis_date');
                $table->enum('disease_stage', ['early', 'mild', 'moderate', 'severe', 'end_stage'])->default('mild');
                $table->json('comorbidities')->nullable()->comment('Bệnh kèm JSON');
                $table->json('target_metrics')->nullable()->comment('Chỉ số mục tiêu JSON');
                $table->json('current_metrics')->nullable()->comment('Chỉ số hiện tại JSON');
                $table->boolean('metrics_on_target')->default(false)->comment('Đạt mục tiêu');
                $table->string('care_manager')->nullable()->comment('Điều phối viên chăm sóc');
                $table->date('last_review_date')->nullable();
                $table->date('next_review_date')->nullable();
                $table->integer('annual_visit_count')->default(0)->comment('Số lần khám trong năm');
                $table->integer('er_visits_12mo')->default(0)->comment('Lần cấp cứu 12 tháng');
                $table->integer('hospitalizations_12mo')->default(0)->comment('Lần nhập viện 12 tháng');
                $table->boolean('enrolled_in_program')->default(true);
                $table->enum('engagement_level', ['high', 'medium', 'low', 'disengaged'])->default('high');
                $table->text('care_plan_notes')->nullable();
                $table->timestamps();
                $table->index(['patient_id', 'disease_type']);
                $table->index(['disease_type', 'disease_stage']);
            });
        }
    }

    public function down(): void {
        Schema::dropIfExists('chronic_disease_cohorts');
        Schema::dropIfExists('loyalty_points_logs');
        Schema::dropIfExists('health_screening_campaigns');
        Schema::dropIfExists('postdischarge_followups');
        Schema::dropIfExists('discharge_checklists');
        Schema::dropIfExists('telehealth_consultations');
        Schema::dropIfExists('patient_digital_twins');
        Schema::dropIfExists('research_projects');
        Schema::dropIfExists('drg_billing_records');
        Schema::dropIfExists('dietary_orders');
        Schema::dropIfExists('porter_transport_requests');
        Schema::dropIfExists('or_block_schedules');
    }
};
