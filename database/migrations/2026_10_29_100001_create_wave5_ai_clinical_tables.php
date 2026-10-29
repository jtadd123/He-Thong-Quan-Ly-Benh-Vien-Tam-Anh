<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // AI Sepsis Prediction
        if (!Schema::hasTable('sepsis_prediction_logs')) {
            Schema::create('sepsis_prediction_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patient_profiles')->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
                $table->decimal('qsofa_score', 3, 1)->default(0);
                $table->decimal('sofa_score', 4, 1)->default(0);
                $table->decimal('sirs_score', 3, 1)->default(0);
                $table->decimal('temperature', 5, 2)->nullable()->comment('Nhiệt độ °C');
                $table->integer('heart_rate')->nullable()->comment('Nhịp tim bpm');
                $table->integer('respiratory_rate')->nullable()->comment('Nhịp thở /phút');
                $table->decimal('sbp', 5, 1)->nullable()->comment('Huyết áp tâm thu mmHg');
                $table->decimal('gcs', 4, 1)->nullable()->comment('Glasgow Coma Scale');
                $table->decimal('lactate', 5, 2)->nullable()->comment('Lactate mmol/L');
                $table->decimal('creatinine', 5, 2)->nullable()->comment('Creatinine mg/dL');
                $table->decimal('bilirubin', 5, 2)->nullable()->comment('Bilirubin mg/dL');
                $table->integer('platelet_count')->nullable()->comment('Tiểu cầu K/μL');
                $table->decimal('pao2_fio2', 6, 1)->nullable()->comment('PaO2/FiO2 ratio');
                $table->decimal('map', 5, 1)->nullable()->comment('Mean Arterial Pressure mmHg');
                $table->decimal('urine_output', 6, 1)->nullable()->comment('Nước tiểu mL/kg/h');
                $table->enum('sepsis_risk', ['low', 'moderate', 'high', 'critical'])->default('low');
                $table->enum('ai_prediction', ['no_sepsis', 'sepsis_watch', 'sepsis', 'septic_shock'])->default('no_sepsis');
                $table->decimal('ai_confidence_score', 5, 2)->default(0)->comment('AI confidence %');
                $table->boolean('alert_sent')->default(false);
                $table->string('alert_sent_to')->nullable();
                $table->text('clinical_notes')->nullable();
                $table->enum('status', ['monitoring', 'treated', 'resolved', 'deceased'])->default('monitoring');
                $table->timestamps();
                $table->index(['patient_id', 'created_at']);
                $table->index('sepsis_risk');
            });
        }

        // Clinical Decision Rules
        if (!Schema::hasTable('clinical_decision_rules')) {
            Schema::create('clinical_decision_rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patient_profiles')->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
                $table->enum('rule_type', ['wells_dvt', 'wells_pe', 'heart_score', 'timi_score', 'chads2_vasc', 'has_bled', 'curb65', 'sofa', 'apache2', 'glasgow_coma'])->default('wells_pe');
                $table->json('input_parameters')->comment('JSON các thông số đầu vào');
                $table->decimal('calculated_score', 6, 2)->default(0);
                $table->string('score_interpretation')->nullable()->comment('Phân loại nguy cơ');
                $table->text('recommendation')->nullable()->comment('Khuyến cáo xử trí');
                $table->string('next_action')->nullable()->comment('Bước xử lý tiếp theo');
                $table->boolean('is_emergency')->default(false);
                $table->timestamp('calculated_at')->nullable();
                $table->timestamps();
                $table->index(['patient_id', 'rule_type']);
            });
        }

        // ICD-10 AI Coding
        if (!Schema::hasTable('icd10_coding_records')) {
            Schema::create('icd10_coding_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patient_profiles')->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
                $table->text('clinical_text')->comment('Văn bản lâm sàng nguồn');
                $table->string('primary_icd10_code', 10)->nullable();
                $table->string('primary_icd10_description')->nullable();
                $table->json('secondary_codes')->nullable()->comment('Mã phụ JSON');
                $table->decimal('ai_confidence', 5, 2)->default(0)->comment('%');
                $table->enum('coding_method', ['ai_auto', 'manual', 'ai_assisted'])->default('ai_auto');
                $table->enum('review_status', ['pending', 'approved', 'rejected', 'modified'])->default('pending');
                $table->string('reviewed_by')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->text('review_notes')->nullable();
                $table->timestamps();
                $table->index(['patient_id', 'review_status']);
                $table->index('primary_icd10_code');
            });
        }

        // Medication Reconciliation
        if (!Schema::hasTable('medication_reconciliations')) {
            Schema::create('medication_reconciliations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patient_profiles')->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
                $table->enum('reconciliation_type', ['admission', 'transfer', 'discharge'])->default('admission');
                $table->json('home_medications')->nullable()->comment('Thuốc đang dùng tại nhà');
                $table->json('hospital_medications')->nullable()->comment('Thuốc trong bệnh viện');
                $table->json('discrepancies')->nullable()->comment('Sai lệch phát hiện');
                $table->integer('total_home_meds')->default(0);
                $table->integer('total_hospital_meds')->default(0);
                $table->integer('discrepancies_count')->default(0);
                $table->integer('discrepancies_resolved')->default(0);
                $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('low');
                $table->enum('status', ['pending', 'completed', 'reviewed'])->default('pending');
                $table->text('pharmacist_notes')->nullable();
                $table->string('pharmacist_name')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                $table->index(['patient_id', 'reconciliation_type']);
            });
        }

        // Fluid Balance Records
        if (!Schema::hasTable('fluid_balance_records')) {
            Schema::create('fluid_balance_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patient_profiles')->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
                $table->date('record_date');
                $table->decimal('oral_intake', 8, 1)->default(0)->comment('Uống miệng mL');
                $table->decimal('iv_intake', 8, 1)->default(0)->comment('Truyền tĩnh mạch mL');
                $table->decimal('feeding_tube_intake', 8, 1)->default(0)->comment('Ống thông dạ dày mL');
                $table->decimal('total_intake', 8, 1)->default(0)->comment('Tổng vào mL');
                $table->decimal('urine_output_ml', 8, 1)->default(0)->comment('Nước tiểu mL');
                $table->decimal('drain_output', 8, 1)->default(0)->comment('Dẫn lưu mL');
                $table->decimal('stool_output', 8, 1)->default(0)->comment('Phân mL');
                $table->decimal('insensible_loss', 8, 1)->default(0)->comment('Mất không cảm nhận mL');
                $table->decimal('total_output', 8, 1)->default(0)->comment('Tổng ra mL');
                $table->decimal('fluid_balance', 9, 1)->default(0)->comment('Cân bằng dịch mL');
                $table->decimal('cumulative_balance', 10, 1)->default(0)->comment('Cân bằng tích lũy mL');
                $table->decimal('body_weight', 5, 2)->nullable()->comment('Cân nặng kg');
                $table->decimal('cvp', 5, 1)->nullable()->comment('CVP mmHg');
                $table->enum('balance_status', ['balanced', 'positive', 'negative', 'severely_positive', 'severely_negative'])->default('balanced');
                $table->text('nursing_notes')->nullable();
                $table->timestamps();
                $table->index(['patient_id', 'record_date']);
            });
        }

        // Anticoagulation Management
        if (!Schema::hasTable('anticoagulation_managements')) {
            Schema::create('anticoagulation_managements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patient_profiles')->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
                $table->enum('indication', ['af', 'dvt', 'pe', 'mechanical_valve', 'bridge_therapy', 'acs', 'vte_prevention'])->default('af');
                $table->enum('anticoagulant', ['warfarin', 'heparin_iv', 'enoxaparin', 'rivaroxaban', 'apixaban', 'dabigatran', 'fondaparinux'])->default('warfarin');
                $table->decimal('target_inr_min', 3, 1)->default(2.0)->comment('INR mục tiêu tối thiểu');
                $table->decimal('target_inr_max', 3, 1)->default(3.0)->comment('INR mục tiêu tối đa');
                $table->decimal('current_inr', 4, 2)->nullable()->comment('INR hiện tại');
                $table->decimal('current_aptt', 5, 1)->nullable()->comment('aPTT hiện tại giây');
                $table->decimal('target_aptt_min', 5, 1)->default(60)->comment('aPTT mục tiêu tối thiểu');
                $table->decimal('target_aptt_max', 5, 1)->default(100)->comment('aPTT mục tiêu tối đa');
                $table->decimal('current_dose', 6, 2)->nullable()->comment('Liều hiện tại');
                $table->string('dose_unit', 20)->default('mg/ngày');
                $table->string('dose_adjustment')->nullable()->comment('Điều chỉnh liều');
                $table->decimal('has_bled_score', 3, 1)->default(0)->comment('HAS-BLED score xuất huyết');
                $table->enum('bleeding_risk', ['low', 'moderate', 'high'])->default('low');
                $table->text('contraindications')->nullable();
                $table->date('next_monitoring_date')->nullable();
                $table->enum('status', ['active', 'held', 'discontinued', 'completed'])->default('active');
                $table->text('clinical_notes')->nullable();
                $table->timestamps();
                $table->index(['patient_id', 'status']);
                $table->index('anticoagulant');
            });
        }
    }

    public function down(): void {
        Schema::dropIfExists('anticoagulation_managements');
        Schema::dropIfExists('fluid_balance_records');
        Schema::dropIfExists('medication_reconciliations');
        Schema::dropIfExists('icd10_coding_records');
        Schema::dropIfExists('clinical_decision_rules');
        Schema::dropIfExists('sepsis_prediction_logs');
    }
};
