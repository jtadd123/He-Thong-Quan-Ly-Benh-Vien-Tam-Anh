<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Stroke Pathway
        if (!Schema::hasTable('stroke_pathway_logs')) {
            Schema::create('stroke_pathway_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patient_profiles')->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
                $table->enum('stroke_type', ['ischemic', 'hemorrhagic', 'tia', 'undetermined'])->default('ischemic');
                $table->timestamp('symptom_onset_time')->nullable()->comment('Giờ khởi phát triệu chứng');
                $table->timestamp('door_time')->nullable()->comment('Giờ vào viện');
                $table->timestamp('ct_scan_time')->nullable()->comment('Giờ chụp CT');
                $table->timestamp('tpa_decision_time')->nullable()->comment('Giờ quyết định tPA');
                $table->timestamp('tpa_start_time')->nullable()->comment('Giờ bắt đầu tPA');
                $table->timestamp('thrombectomy_time')->nullable()->comment('Giờ lấy huyết khối');
                $table->integer('nihss_score')->nullable()->comment('NIH Stroke Scale');
                $table->integer('aspects_score')->nullable()->comment('ASPECTS score CT');
                $table->decimal('mrs_at_admission', 3, 1)->nullable()->comment('mRS nhập viện');
                $table->decimal('mrs_at_discharge', 3, 1)->nullable()->comment('mRS xuất viện');
                $table->boolean('tpa_eligible')->default(false)->comment('Đủ điều kiện tPA');
                $table->boolean('tpa_given')->default(false)->comment('Đã dùng tPA');
                $table->string('tpa_contraindication')->nullable()->comment('Chống chỉ định tPA');
                $table->decimal('tpa_dose_mg', 6, 2)->nullable()->comment('Liều tPA mg');
                $table->boolean('thrombectomy_done')->default(false);
                $table->integer('door_to_ct_minutes')->nullable()->comment('Door-to-CT phút');
                $table->integer('door_to_needle_minutes')->nullable()->comment('Door-to-Needle phút');
                $table->integer('onset_to_treatment_minutes')->nullable()->comment('Onset-to-Treatment phút');
                $table->boolean('target_dtn_met')->default(false)->comment('DTN <=60 phút đạt');
                $table->enum('outcome', ['improving', 'stable', 'deteriorating', 'discharged', 'deceased'])->default('stable');
                $table->text('clinical_notes')->nullable();
                $table->timestamps();
                $table->index(['patient_id', 'stroke_type']);
            });
        }

        // STEMI Cathlab
        if (!Schema::hasTable('stemi_cathlab_activations')) {
            Schema::create('stemi_cathlab_activations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patient_profiles')->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
                $table->timestamp('ecg_time')->nullable()->comment('Giờ đọc ECG');
                $table->timestamp('diagnosis_time')->nullable()->comment('Giờ chẩn đoán STEMI');
                $table->timestamp('cathlab_activation_time')->nullable()->comment('Giờ kích hoạt Cath Lab');
                $table->timestamp('balloon_time')->nullable()->comment('Giờ bơm bóng - Primary PCI');
                $table->timestamp('thrombolysis_time')->nullable()->comment('Giờ tiêu sợi huyết');
                $table->integer('door_to_balloon_minutes')->nullable()->comment('Door-to-Balloon phút');
                $table->integer('door_to_ecg_minutes')->nullable()->comment('Door-to-ECG phút');
                $table->boolean('target_dtb_met')->default(false)->comment('DTB <=90 phút đạt');
                $table->enum('infarct_territory', ['anterior', 'inferior', 'lateral', 'posterior', 'rvmi'])->default('anterior');
                $table->string('culprit_vessel', 20)->nullable()->comment('Mạch thủ phạm');
                $table->string('timi_flow_post_pci', 5)->nullable()->comment('TIMI flow sau PCI');
                $table->decimal('ef_post_pci', 5, 1)->nullable()->comment('EF sau PCI %');
                $table->enum('reperfusion_strategy', ['primary_pci', 'thrombolysis', 'pharmaco_invasive', 'cabg', 'conservative'])->default('primary_pci');
                $table->json('medications_given')->nullable()->comment('Thuốc đã dùng JSON');
                $table->enum('killip_class', ['I', 'II', 'III', 'IV'])->default('I');
                $table->enum('outcome', ['survived', 'cardiogenic_shock', 'vf_arrested', 'deceased'])->default('survived');
                $table->text('clinical_notes')->nullable();
                $table->timestamps();
                $table->index(['patient_id', 'created_at']);
            });
        }

        // Anesthesia Records
        if (!Schema::hasTable('anesthesia_records')) {
            Schema::create('anesthesia_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patient_profiles')->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
                $table->string('surgery_name')->comment('Tên phẫu thuật');
                $table->enum('anesthesia_type', ['general', 'regional', 'spinal', 'epidural', 'local', 'sedation', 'combined'])->default('general');
                $table->string('asa_class', 5)->default('II')->comment('ASA Classification');
                $table->decimal('mallampati_score', 2, 0)->nullable()->comment('Mallampati I-IV');
                $table->json('premedication')->nullable()->comment('Thuốc tiền mê JSON');
                $table->json('induction_agents')->nullable()->comment('Thuốc khởi mê JSON');
                $table->json('maintenance_agents')->nullable()->comment('Thuốc duy trì mê JSON');
                $table->json('muscle_relaxants')->nullable()->comment('Giãn cơ JSON');
                $table->decimal('total_fentanyl_mcg', 7, 1)->nullable()->comment('Tổng Fentanyl mcg');
                $table->decimal('total_propofol_mg', 7, 1)->nullable()->comment('Tổng Propofol mg');
                $table->decimal('total_blood_loss_ml', 7, 1)->nullable()->comment('Lượng máu mất mL');
                $table->decimal('total_fluid_given_ml', 7, 1)->nullable()->comment('Dịch truyền tổng mL');
                $table->integer('units_prbc')->default(0)->comment('Đơn vị hồng cầu khối');
                $table->timestamp('anesthesia_start_time')->nullable();
                $table->timestamp('surgery_start_time')->nullable();
                $table->timestamp('surgery_end_time')->nullable();
                $table->timestamp('anesthesia_end_time')->nullable();
                $table->integer('anesthesia_duration_minutes')->nullable();
                $table->integer('surgery_duration_minutes')->nullable();
                $table->json('intraop_vitals')->nullable()->comment('Sinh hiệu trong mổ JSON');
                $table->text('complications')->nullable()->comment('Biến chứng trong mổ');
                $table->json('pacu_vitals')->nullable()->comment('Sinh hiệu phòng hồi tỉnh');
                $table->integer('aldrete_score')->nullable()->comment('Aldrete score xuất PACU');
                $table->enum('pacu_discharge_criteria', ['met', 'not_met', 'icu_required'])->default('met');
                $table->text('anesthesia_notes')->nullable();
                $table->timestamps();
                $table->index(['patient_id', 'created_at']);
            });
        }

        // Ventilator Records
        if (!Schema::hasTable('ventilator_records')) {
            Schema::create('ventilator_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patient_profiles')->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
                $table->enum('ventilation_mode', ['ac_vc', 'ac_pc', 'simv', 'psv', 'cpap', 'bipap', 'hfov', 'hfnc'])->default('ac_vc');
                $table->decimal('tidal_volume_ml', 6, 1)->nullable()->comment('Thể tích lưu thông mL');
                $table->decimal('respiratory_rate_set', 4, 1)->nullable()->comment('Tần số cài đặt /phút');
                $table->decimal('peep', 4, 1)->nullable()->comment('PEEP cmH2O');
                $table->decimal('fio2', 4, 2)->nullable()->comment('FiO2 0-1');
                $table->decimal('inspiratory_pressure', 5, 1)->nullable()->comment('áp lực hít vào cmH2O');
                $table->decimal('driving_pressure', 5, 1)->nullable()->comment('Driving pressure cmH2O');
                $table->decimal('plateau_pressure', 5, 1)->nullable()->comment('Plateau pressure cmH2O');
                $table->decimal('peak_pressure', 5, 1)->nullable()->comment('Peak pressure cmH2O');
                $table->decimal('compliance', 6, 1)->nullable()->comment('Compliance mL/cmH2O');
                $table->decimal('spo2', 5, 1)->nullable()->comment('SpO2 %');
                $table->decimal('etco2', 5, 1)->nullable()->comment('EtCO2 mmHg');
                $table->decimal('pao2', 5, 1)->nullable()->comment('PaO2 mmHg');
                $table->decimal('pco2', 5, 1)->nullable()->comment('PaCO2 mmHg');
                $table->decimal('ph_value', 4, 2)->nullable()->comment('pH máu');
                $table->string('berlin_category', 20)->nullable()->comment('Berlin ARDS: mild/moderate/severe');
                $table->boolean('prone_positioning')->default(false)->comment('Nằm sấp');
                $table->boolean('recruitment_maneuver')->default(false)->comment('Nghiệm pháp huy động phế nang');
                $table->json('weaning_assessment')->nullable()->comment('Đánh giá cai máy JSON');
                $table->enum('weaning_status', ['not_started', 'in_progress', 'successful', 'failed'])->default('not_started');
                $table->integer('total_ventilation_hours')->default(0);
                $table->text('nursing_notes')->nullable();
                $table->timestamps();
                $table->index(['patient_id', 'created_at']);
            });
        }

        // Wound Care
        if (!Schema::hasTable('wound_care_assessments')) {
            Schema::create('wound_care_assessments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patient_profiles')->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
                $table->enum('wound_type', ['pressure_injury', 'diabetic_foot', 'venous_ulcer', 'arterial_ulcer', 'surgical', 'traumatic', 'burn', 'other'])->default('pressure_injury');
                $table->string('wound_location')->comment('Vị trí vết thương');
                $table->enum('pressure_injury_stage', ['stage_1', 'stage_2', 'stage_3', 'stage_4', 'unstageable', 'deep_tissue', 'na'])->default('na');
                $table->decimal('wound_length_cm', 5, 1)->nullable();
                $table->decimal('wound_width_cm', 5, 1)->nullable();
                $table->decimal('wound_depth_cm', 4, 1)->nullable();
                $table->decimal('wound_area_cm2', 7, 2)->nullable()->comment('Diện tích cm²');
                $table->integer('push_score')->nullable()->comment('PUSH Tool score 0-17');
                $table->integer('braden_score')->nullable()->comment('Braden Scale 6-23');
                $table->enum('wound_bed', ['red', 'yellow', 'black', 'mixed'])->default('red');
                $table->enum('exudate_amount', ['none', 'scant', 'small', 'moderate', 'large'])->default('none');
                $table->enum('exudate_type', ['serous', 'serosanguineous', 'sanguineous', 'purulent', 'none'])->default('none');
                $table->boolean('wound_infection')->default(false);
                $table->string('dressing_type')->nullable()->comment('Loại băng vết thương');
                $table->integer('dressing_frequency_days')->default(1);
                $table->string('topical_treatment')->nullable()->comment('Điều trị tại chỗ');
                $table->json('photos')->nullable()->comment('Ảnh vết thương JSON paths');
                $table->text('assessment_notes')->nullable();
                $table->date('next_assessment_date')->nullable();
                $table->enum('healing_trend', ['improving', 'stable', 'deteriorating', 'healed'])->default('stable');
                $table->timestamps();
                $table->index(['patient_id', 'wound_type']);
            });
        }

        // Pain Assessments
        if (!Schema::hasTable('pain_assessments')) {
            Schema::create('pain_assessments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patient_profiles')->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
                $table->enum('scale_type', ['vas', 'nrs', 'flacc', 'cpot', 'faces_wong'])->default('nrs');
                $table->decimal('pain_score', 4, 1)->default(0)->comment('Điểm đau 0-10');
                $table->string('pain_location')->nullable()->comment('Vị trí đau');
                $table->enum('pain_quality', ['sharp', 'dull', 'burning', 'throbbing', 'stabbing', 'cramping', 'radiating'])->nullable();
                $table->boolean('pain_at_rest')->default(false);
                $table->boolean('pain_on_movement')->default(false);
                $table->string('aggravating_factors')->nullable();
                $table->string('relieving_factors')->nullable();
                $table->enum('analgesic_ladder', ['step1_nonopioid', 'step2_weak_opioid', 'step3_strong_opioid', 'adjuvant'])->default('step1_nonopioid');
                $table->json('analgesics_given')->nullable()->comment('Thuốc giảm đau đã dùng');
                $table->decimal('pain_score_after', 4, 1)->nullable()->comment('Điểm đau sau can thiệp');
                $table->integer('reassessment_minutes')->default(60)->comment('Phút tái đánh giá');
                $table->boolean('pain_controlled')->default(false)->comment('Đau được kiểm soát');
                $table->enum('pain_severity', ['none', 'mild', 'moderate', 'severe', 'very_severe'])->default('none');
                $table->text('nursing_notes')->nullable();
                $table->timestamps();
                $table->index(['patient_id', 'created_at']);
            });
        }

        // NICU Monitoring
        if (!Schema::hasTable('nicu_monitoring_records')) {
            Schema::create('nicu_monitoring_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patient_profiles')->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
                $table->integer('gestational_age_weeks')->nullable()->comment('Tuổi thai tuần');
                $table->decimal('birth_weight_grams', 6, 1)->nullable()->comment('Cân nặng lúc sinh g');
                $table->integer('apgar_1min')->nullable()->comment('APGAR phút 1');
                $table->integer('apgar_5min')->nullable()->comment('APGAR phút 5');
                $table->decimal('current_weight_grams', 7, 1)->nullable()->comment('Cân nặng hiện tại g');
                $table->decimal('temperature', 4, 1)->nullable()->comment('Nhiệt độ °C');
                $table->decimal('spo2_percent', 5, 1)->nullable()->comment('SpO2 %');
                $table->integer('heart_rate_bpm')->nullable()->comment('Nhịp tim');
                $table->integer('respiratory_rate')->nullable()->comment('Nhịp thở');
                $table->decimal('blood_glucose', 5, 1)->nullable()->comment('Đường huyết mmol/L');
                $table->decimal('bilirubin_total', 5, 2)->nullable()->comment('Bilirubin toàn phần mg/dL');
                $table->decimal('bilirubin_direct', 5, 2)->nullable()->comment('Bilirubin trực tiếp mg/dL');
                $table->boolean('phototherapy_active')->default(false)->comment('Chiếu đèn vàng da');
                $table->boolean('oxygen_support')->default(false)->comment('Hỗ trợ oxy');
                $table->enum('respiratory_support', ['room_air', 'nasal_cannula', 'cpap', 'hfnc', 'intubated'])->default('room_air');
                $table->boolean('iv_access')->default(false);
                $table->boolean('umbilical_line')->default(false);
                $table->decimal('total_fluid_ml_per_kg', 7, 1)->nullable()->comment('Dịch tổng mL/kg/ngày');
                $table->decimal('caloric_intake_kcal_per_kg', 6, 1)->nullable()->comment('Calorie kcal/kg/ngày');
                $table->boolean('breast_feeding')->default(false);
                $table->string('feeding_type', 50)->nullable()->comment('Loại nuôi dưỡng');
                $table->integer('ballard_score')->nullable()->comment('Ballard Maturity Score');
                $table->enum('discharge_readiness', ['not_ready', 'almost_ready', 'ready'])->default('not_ready');
                $table->text('nursing_notes')->nullable();
                $table->timestamps();
                $table->index(['patient_id', 'created_at']);
            });
        }

        // Mental Health Assessments
        if (!Schema::hasTable('mental_health_assessments')) {
            Schema::create('mental_health_assessments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patient_profiles')->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
                $table->enum('assessment_type', ['phq9', 'gad7', 'columbia_scale', 'mmse', 'moca', 'audit', 'cage', 'madrs', 'panss', 'ham_a', 'ham_d'])->default('phq9');
                $table->json('responses')->nullable()->comment('Câu trả lời từng mục JSON');
                $table->decimal('total_score', 5, 1)->default(0);
                $table->string('severity_level')->nullable()->comment('Mức độ nặng');
                $table->string('interpretation')->nullable()->comment('Giải thích kết quả');
                $table->enum('suicide_risk', ['none', 'low', 'moderate', 'high', 'imminent'])->default('none');
                $table->boolean('safety_plan_made')->default(false);
                $table->text('safety_plan_details')->nullable();
                $table->string('referred_to')->nullable()->comment('Chuyển gặp ai');
                $table->boolean('medication_started')->default(false);
                $table->string('medication_name')->nullable();
                $table->boolean('psychotherapy_recommended')->default(false);
                $table->date('follow_up_date')->nullable();
                $table->text('clinical_impression')->nullable();
                $table->timestamps();
                $table->index(['patient_id', 'assessment_type']);
            });
        }

        // Liver Cirrhosis Scores
        if (!Schema::hasTable('liver_cirrhosis_scores')) {
            Schema::create('liver_cirrhosis_scores', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patient_profiles')->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
                // Child-Pugh
                $table->decimal('bilirubin_cp', 5, 2)->nullable()->comment('Bilirubin mg/dL');
                $table->decimal('albumin_cp', 4, 1)->nullable()->comment('Albumin g/dL');
                $table->decimal('inr_cp', 4, 2)->nullable()->comment('INR/PT');
                $table->enum('ascites_grade', ['none', 'mild', 'moderate', 'severe'])->default('none');
                $table->enum('encephalopathy_grade', ['none', 'grade1', 'grade2', 'grade3', 'grade4'])->default('none');
                $table->integer('child_pugh_score')->default(0);
                $table->string('child_pugh_class', 5)->nullable()->comment('A/B/C');
                $table->decimal('one_year_survival', 5, 1)->nullable()->comment('Sống sót 1 năm %');
                // MELD
                $table->decimal('creatinine_meld', 5, 2)->nullable()->comment('Creatinine mg/dL');
                $table->decimal('bilirubin_meld', 5, 2)->nullable()->comment('Bilirubin mg/dL');
                $table->decimal('inr_meld', 4, 2)->nullable()->comment('INR');
                $table->decimal('sodium_meld', 5, 1)->nullable()->comment('Natri mEq/L');
                $table->boolean('dialysis_twice_weekly')->default(false);
                $table->decimal('meld_score', 5, 1)->default(0);
                $table->decimal('meld_na_score', 5, 1)->default(0)->comment('MELD-Na score');
                $table->integer('three_month_mortality_pct')->default(0)->comment('Tỷ lệ tử vong 3 tháng %');
                $table->enum('transplant_urgency', ['not_indicated', 'elective', 'urgent', 'status_1'])->default('not_indicated');
                $table->text('clinical_notes')->nullable();
                $table->timestamps();
                $table->index(['patient_id', 'created_at']);
            });
        }

        // Diabetic Foot
        if (!Schema::hasTable('diabetic_foot_assessments')) {
            Schema::create('diabetic_foot_assessments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patient_profiles')->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
                $table->enum('foot_side', ['left', 'right', 'bilateral'])->default('right');
                $table->integer('wagner_grade')->default(0)->comment('Wagner Grade 0-5');
                $table->string('wagner_description')->nullable()->comment('Mô tả Wagner');
                $table->decimal('abi_left', 4, 2)->nullable()->comment('ABI trái');
                $table->decimal('abi_right', 4, 2)->nullable()->comment('ABI phải');
                $table->enum('abi_interpretation', ['normal', 'mild_pad', 'moderate_pad', 'severe_pad', 'medial_calcification'])->default('normal');
                $table->decimal('tbpi', 4, 2)->nullable()->comment('Toe-Brachial Pressure Index');
                $table->boolean('neuropathy_present')->default(false)->comment('Bệnh lý thần kinh ngoại vi');
                $table->boolean('monofilament_10g')->nullable()->comment('Test monofilament 10g');
                $table->boolean('vibration_sensation')->nullable()->comment('Cảm giác rung');
                $table->boolean('protective_sensation_lost')->default(false);
                $table->boolean('infection_present')->default(false);
                $table->enum('infection_severity', ['none', 'mild', 'moderate', 'severe', 'limb_threatening'])->default('none');
                $table->string('wound_culture_organism')->nullable()->comment('Vi khuẩn cấy');
                $table->boolean('osteomyelitis_suspected')->default(false)->comment('Nghi ngờ viêm xương');
                $table->enum('treatment_plan', ['conservative', 'debridement', 'minor_amputation', 'major_amputation', 'revascularization'])->default('conservative');
                $table->decimal('hba1c', 4, 1)->nullable()->comment('HbA1c %');
                $table->text('assessment_notes')->nullable();
                $table->timestamps();
                $table->index(['patient_id', 'created_at']);
            });
        }
    }

    public function down(): void {
        Schema::dropIfExists('diabetic_foot_assessments');
        Schema::dropIfExists('liver_cirrhosis_scores');
        Schema::dropIfExists('mental_health_assessments');
        Schema::dropIfExists('nicu_monitoring_records');
        Schema::dropIfExists('pain_assessments');
        Schema::dropIfExists('wound_care_assessments');
        Schema::dropIfExists('ventilator_records');
        Schema::dropIfExists('anesthesia_records');
        Schema::dropIfExists('stemi_cathlab_activations');
        Schema::dropIfExists('stroke_pathway_logs');
    }
};
