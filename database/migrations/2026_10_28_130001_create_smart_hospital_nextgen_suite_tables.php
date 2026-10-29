<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Phân hệ Phòng Mổ Thông Minh & PACU (Smart OR & Anesthesia)
        Schema::create('operating_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number')->unique(); // P.Mổ 01, P.Mổ 02 Hybird...
            $table->string('room_name');
            $table->string('zone')->default('Khu mổ vô trùng trung tâm');
            $table->enum('cleanliness_class', ['ISO_5', 'ISO_6', 'ISO_7', 'STANDARD'])->default('ISO_5');
            $table->enum('status', ['available', 'in_use', 'cleaning', 'maintenance'])->default('available');
            $table->boolean('has_laminar_flow')->default(true); // Áp lực dương lọc khí HEPA
            $table->timestamps();
        });

        Schema::create('surgical_cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_code')->unique(); // SURG-YYYYMMDD-XXXX
            $table->foreignId('patient_id')->constrained('patient_profiles')->onDelete('cascade');
            $table->foreignId('room_id')->nullable()->constrained('operating_rooms')->nullOnDelete();
            $table->foreignId('lead_surgeon_id')->constrained('doctors')->onDelete('cascade'); // PTV chính
            $table->foreignId('anesthesiologist_id')->nullable()->constrained('doctors')->nullOnDelete(); // BS Gây mê
            $table->string('surgical_procedure'); // Tên phẫu thuật
            $table->string('icd10_code', 20)->nullable();
            $table->enum('priority', ['elective', 'urgent', 'emergency'])->default('elective');
            $table->enum('phase', ['scheduled', 'pre_op', 'anesthesia', 'surgery', 'hemostasis', 'pacu', 'discharged', 'cancelled'])->default('scheduled');
            $table->dateTime('scheduled_start');
            $table->dateTime('actual_start')->nullable();
            $table->dateTime('actual_end')->nullable();
            $table->text('surgical_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('surgical_sponge_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surgical_case_id')->constrained('surgical_cases')->onDelete('cascade');
            $table->string('item_name'); // Gạc lớn có cản quang, Gạc nhỏ, Kẹp gắp, Kéo phẫu thuật...
            $table->integer('initial_count')->default(0); // Đếm trước mở bụng
            $table->integer('added_count')->default(0); // Thêm trong lúc mổ
            $table->integer('before_closure_count')->default(0); // Đếm trước đóng bụng
            $table->integer('final_count')->default(0); // Đếm kết thúc ca mổ
            $table->boolean('is_matched')->default(true); // Đúng khớp 100%
            $table->string('scrub_nurse_name'); // Điều dưỡng vòng trong
            $table->string('circulating_nurse_name'); // Điều dưỡng vòng ngoài
            $table->text('discrepancy_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('pacu_aldrete_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surgical_case_id')->constrained('surgical_cases')->onDelete('cascade');
            $table->integer('respiration_score'); // 0-2 (Hô hấp)
            $table->integer('circulation_score'); // 0-2 (Tuần hoàn huyết áp)
            $table->integer('consciousness_score'); // 0-2 (Tri giác)
            $table->integer('activity_score'); // 0-2 (Vận động tứ chi)
            $table->integer('color_score'); // 0-2 (Màu da / SpO2)
            $table->integer('total_score'); // Max 10 (>=9 đủ điều kiện xuất)
            $table->boolean('is_eligible_for_discharge')->default(false);
            $table->string('evaluated_by');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Chẩn Đoán Hình Ảnh AI & Dự Báo Sepsis (Clinical AI & Diagnostics)
        Schema::create('pacs_ai_analyses', function (Blueprint $table) {
            $table->id();
            $table->string('analysis_code')->unique();
            $table->foreignId('patient_id')->constrained('patient_profiles')->onDelete('cascade');
            $table->string('modality'); // X-RAY, CT, MRI
            $table->string('body_part'); // CHEST, BRAIN, ABDOMEN
            $table->string('finding_detected'); // Tràn khí màng phổi, Xuất huyết dưới nhện, Bình thường...
            $table->decimal('confidence_score', 5, 2); // 98.50%
            $table->enum('triage_urgency', ['CRITICAL', 'URGENT', 'ROUTINE'])->default('ROUTINE');
            $table->boolean('is_prioritized')->default(false); // Đôn lên đầu hàng đợi đọc phim
            $table->string('ai_model_version')->default('DeepLung-Vision v4.2');
            $table->timestamps();
        });

        Schema::create('sepsis_qsofa_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patient_profiles')->onDelete('cascade');
            $table->integer('respiratory_rate'); // >= 22 bpm -> 1 điểm
            $table->integer('glasgow_coma_scale'); // < 15 -> 1 điểm
            $table->integer('systolic_bp'); // <= 100 mmHg -> 1 điểm
            $table->integer('qsofa_score'); // 0 to 3
            $table->enum('risk_level', ['LOW', 'MODERATE', 'HIGH_RISK_SHOCK'])->default('LOW');
            $table->boolean('icu_notified')->default(false);
            $table->string('assessed_by');
            $table->timestamps();
        });

        Schema::create('pharmacogenomic_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patient_profiles')->onDelete('cascade');
            $table->string('gene_name'); // CYP2C19, HLA-B*1502, TPMT, VKORC1...
            $table->string('genotype'); // *1/*1, *2/*2, Positive...
            $table->string('phenotype'); // Poor Metabolizer, High Risk...
            $table->string('contraindicated_medication'); // Clopidogrel, Carbamazepine, Azathioprine
            $table->string('recommended_alternative'); // Prasugrel/Ticagrelor, Levetiracetam
            $table->text('clinical_guideline_source')->nullable(); // CPIC / FDA Guidelines
            $table->timestamps();
        });

        // 3. Báo Cáo Tai Biến Truyền Máu & Hemovigilance
        Schema::create('transfusion_reaction_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_unit_id')->constrained('blood_units')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patient_profiles')->onDelete('cascade');
            $table->dateTime('onset_time');
            $table->enum('reaction_type', ['FEBRILE', 'ALLERGIC_ANAPHYLACTIC', 'ACUTE_HEMOLYTIC', 'TRALI', 'TACO', 'SEPTIC']);
            $table->text('symptoms'); // Sốt cao rét run, khó thở, tụt huyết áp...
            $table->boolean('immediate_transfusion_stopped')->default(true); // Dừng truyền khẩn cấp
            $table->boolean('blood_unit_quarantined')->default(true); // Niêm phong túi máu
            $table->string('reporting_physician');
            $table->text('outcome')->nullable();
            $table->timestamps();
        });

        // 4. Kỹ Thuật Y Sinh CMMS (Biomedical Equipment Life Cycle)
        Schema::create('biomedical_equipments', function (Blueprint $table) {
            $table->id();
            $table->string('equipment_code')->unique(); // MED-EQ-XXXX
            $table->string('name'); // Máy thở Puritan Bennett 980, Máy sốc tim Zoll R Series...
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('manufacturer')->nullable();
            $table->enum('risk_class', ['CLASS_A', 'CLASS_B', 'CLASS_C', 'CLASS_D'])->default('CLASS_D'); // Nghị định 98/2021
            $table->string('department'); // Cấp cứu, ICU, Gây mê hồi sức...
            $table->enum('operational_status', ['ready', 'in_use', 'under_repair', 'out_of_service'])->default('ready');
            $table->date('last_calibration_date')->nullable();
            $table->date('next_calibration_due'); // Hạn kiểm định
            $table->boolean('is_calibration_expired')->default(false);
            $table->timestamps();
        });

        Schema::create('biomedical_repair_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('biomedical_equipments')->onDelete('cascade');
            $table->string('reporter_name');
            $table->enum('urgency', ['CRITICAL_LIFE_SAVING', 'URGENT', 'STANDARD'])->default('STANDARD');
            $table->text('fault_description');
            $table->enum('status', ['pending', 'investigating', 'parts_waiting', 'resolved'])->default('pending');
            $table->dateTime('resolved_at')->nullable();
            $table->string('engineer_name')->nullable();
            $table->timestamps();
        });

        // 5. Kiểm Soát Nhiễm Khuẩn (HAI) & Kháng Sinh Dự Trữ (ASP)
        Schema::create('hai_surveillances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patient_profiles')->onDelete('cascade');
            $table->enum('infection_type', ['VAP', 'CLABSI', 'CAUTI', 'SSI']); // 4 loại kinh điển
            $table->string('pathogen_detected')->nullable(); // Klebsiella pneumoniae KPC, Acinetobacter baumannii...
            $table->string('department'); // Khoa Hồi sức tích cực, Ngoại thần kinh...
            $table->date('identified_date');
            $table->enum('containment_action', ['CONTACT_ISOLATION', 'DROPLET_ISOLATION', 'AIRBORNE_ISOLATION', 'STANDARD']);
            $table->boolean('is_resolved')->default(false);
            $table->timestamps();
        });

        Schema::create('antimicrobial_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patient_profiles')->onDelete('cascade');
            $table->string('antibiotic_name'); // Colistin, Meropenem, Linezolid, Tigecycline
            $table->enum('tier_level', ['TIER_1_UNRESTRICTED', 'TIER_2_RESTRICTED', 'TIER_3_RESERVE'])->default('TIER_3_RESERVE');
            $table->string('prescribing_doctor');
            $table->text('clinical_indication'); // Sốc nhiễm khuẩn do Acinetobacter kháng đa thuốc
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('pharmacist_approver')->nullable();
            $table->string('microbiologist_approver')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
        });

        // 6. Sản Nhi An Toàn & Vòng Đeo RFID Chống Bắt Cóc (Maternity & Infant Safety)
        Schema::create('electronic_partographs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patient_profiles')->onDelete('cascade');
            $table->dateTime('observation_time');
            $table->decimal('cervical_dilation_cm', 4, 1); // 1.0 to 10.0 cm
            $table->decimal('fetal_head_station', 3, 1); // -3 to +3
            $table->integer('fetal_heart_rate'); // 110 - 160 bpm
            $table->integer('contractions_per_10min'); // 2 - 5 cơn
            $table->boolean('crosses_alert_line')->default(false);
            $table->boolean('crosses_action_line')->default(false); // Chỉ định mổ bắt con khẩn
            $table->string('midwife_name');
            $table->timestamps();
        });

        Schema::create('infant_security_tags', function (Blueprint $table) {
            $table->id();
            $table->string('infant_tag_code')->unique(); // RFID-INFANT-XXXX
            $table->foreignId('mother_patient_id')->constrained('patient_profiles')->onDelete('cascade');
            $table->string('baby_gender'); // Trai / Gái
            $table->dateTime('birth_time');
            $table->decimal('birth_weight_grams', 6, 1);
            $table->string('mother_band_code');
            $table->boolean('is_paired')->default(true);
            $table->enum('perimeter_status', ['INSIDE_WARD', 'AUTHORIZED_TRANSFER', 'BREACH_ALARM'])->default('INSIDE_WARD');
            $table->timestamps();
        });

        // 7. Ung Bướu & Hội Đồng Tumor Board (Oncology & Regimens)
        Schema::create('chemo_treatment_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patient_profiles')->onDelete('cascade');
            $table->string('regimen_name'); // FOLFOX6, R-CHOP, AC-T...
            $table->integer('cycle_number'); // Chu kỳ 1, 2, 3...
            $table->integer('total_cycles')->default(6);
            $table->decimal('body_surface_area', 4, 2); // BSA tính bằng m2
            $table->integer('anc_neutrophil_count'); // Phải >= 1500 để truyền
            $table->integer('platelet_count'); // Phải >= 100,000
            $table->boolean('is_eligible_to_dispense')->default(false);
            $table->date('scheduled_date');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'delayed_toxicity'])->default('pending');
            $table->timestamps();
        });

        Schema::create('virtual_tumor_boards', function (Blueprint $table) {
            $table->id();
            $table->string('board_code')->unique();
            $table->foreignId('patient_id')->constrained('patient_profiles')->onDelete('cascade');
            $table->string('cancer_diagnosis'); // Ung thư đại trực tràng T3N1M0 Giai đoạn III
            $table->dateTime('meeting_date');
            $table->text('consensus_treatment_plan'); // Phẫu thuật nội soi cắt đoạn trực tràng + Hóa trị bổ trợ FOLFOX6
            $table->json('multidisciplinary_attendees'); // Array gồm [Oncologist, Surgeon, Pathologist, Radiologist]
            $table->enum('status', ['scheduled', 'in_discussion', 'approved'])->default('scheduled');
            $table->timestamps();
        });

        // 8. Hộp Đen Giờ Vàng Cấp Cứu & Bộ 83 Tiêu Chí Chất Lượng BYT
        Schema::create('golden_hour_trackers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patient_profiles')->onDelete('cascade');
            $table->enum('emergency_type', ['STROKE_BRAIN', 'STEMI_HEART']); // Đột quỵ / Nhồi máu cơ tim
            $table->dateTime('door_arrival_time'); // Thời điểm đến cửa viện
            $table->dateTime('ct_ecg_done_time')->nullable();
            $table->dateTime('intervention_time')->nullable(); // Tiêm thuốc tiêu sợi huyết hoặc Nong bóng mạch vành
            $table->integer('door_to_needle_minutes')->nullable(); // Target < 60 phút
            $table->integer('door_to_balloon_minutes')->nullable(); // Target < 90 phút
            $table->boolean('met_target')->default(false);
            $table->string('attending_team');
            $table->timestamps();
        });

        Schema::create('hospital_quality_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('criteria_code')->unique(); // C1.1, C2.1, D1.1, E1.1...
            $table->string('criteria_name');
            $table->string('section'); // Phần C: Hoạt động chuyên môn; Phần D: Cải tiến chất lượng
            $table->integer('current_level')->default(4); // Mức 1 đến Mức 5
            $table->integer('target_level')->default(5);
            $table->text('evidence_summary')->nullable(); // Dữ liệu minh chứng tự động trích xuất từ EMR
            $table->date('last_audited_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospital_quality_criteria');
        Schema::dropIfExists('golden_hour_trackers');
        Schema::dropIfExists('virtual_tumor_boards');
        Schema::dropIfExists('chemo_treatment_cycles');
        Schema::dropIfExists('infant_security_tags');
        Schema::dropIfExists('electronic_partographs');
        Schema::dropIfExists('antimicrobial_approvals');
        Schema::dropIfExists('hai_surveillances');
        Schema::dropIfExists('biomedical_repair_requests');
        Schema::dropIfExists('biomedical_equipments');
        Schema::dropIfExists('transfusion_reaction_reports');
        Schema::dropIfExists('pharmacogenomic_profiles');
        Schema::dropIfExists('sepsis_qsofa_alerts');
        Schema::dropIfExists('pacs_ai_analyses');
        Schema::dropIfExists('pacu_aldrete_scores');
        Schema::dropIfExists('surgical_sponge_counts');
        Schema::dropIfExists('surgical_cases');
        Schema::dropIfExists('operating_rooms');
    }
};
