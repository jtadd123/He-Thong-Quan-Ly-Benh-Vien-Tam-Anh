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
        // 1. Dược Lâm Sàng: TDM Đo Nồng Độ Thuốc Hẹp & TPN Dinh Dưỡng Tĩnh Mạch
        Schema::create('therapeutic_drug_monitorings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patient_profiles')->onDelete('cascade');
            $table->string('medication_name'); // Vancomycin, Tacrolimus, Amikacin...
            $table->decimal('trough_concentration_cmin', 8, 2); // Nồng độ đáy (mg/L hoặc ng/mL)
            $table->decimal('peak_concentration_cmax', 8, 2)->nullable(); // Nồng độ đỉnh
            $table->decimal('creatinine_clearance_crcl', 6, 2); // Độ thanh thải creatinin (mL/min)
            $table->string('target_range'); // VD: 15.0 - 20.0 mg/L
            $table->boolean('is_within_target')->default(true);
            $table->text('dose_adjustment_recommendation'); // Đề xuất chỉnh liều của Dược sĩ
            $table->string('pharmacist_evaluator');
            $table->timestamps();
        });

        Schema::create('parenteral_nutritions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patient_profiles')->onDelete('cascade');
            $table->integer('total_calories_kcal'); // Tổng calo / 24h
            $table->decimal('glucose_grams', 6, 1);
            $table->decimal('amino_acids_grams', 6, 1);
            $table->decimal('lipids_grams', 6, 1);
            $table->decimal('sodium_meq', 5, 1)->default(60.0);
            $table->decimal('potassium_meq', 5, 1)->default(40.0);
            $table->decimal('calcium_meq', 5, 1)->default(10.0);
            $table->decimal('phosphate_mmol', 5, 1)->default(15.0);
            $table->integer('osmolarity_mosm_l')->default(1100); // Áp lực thẩm thấu
            $table->enum('infusion_route', ['CENTRAL_LINE', 'PERIPHERAL'])->default('CENTRAL_LINE');
            $table->boolean('refeeding_risk')->default(false); // Nguy cơ hội chứng tái nuôi dưỡng
            $table->string('clinical_dietitian'); // Bác sĩ / Dược sĩ dinh dưỡng
            $table->timestamps();
        });

        Schema::create('adr_adverse_drug_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patient_profiles')->onDelete('cascade');
            $table->string('suspected_medication');
            $table->text('reaction_description');
            $table->enum('severity_level', ['MILD', 'MODERATE', 'SEVERE_LIFE_THREATENING', 'FATAL'])->default('MODERATE');
            $table->integer('causality_naranjo_score')->default(6); // Thang điểm Naranjo (0-13)
            $table->enum('causality_category', ['DOUBTFUL', 'POSSIBLE', 'PROBABLE', 'DEFINITE'])->default('PROBABLE');
            $table->string('outcome')->default('Hồi phục sau khi ngừng thuốc');
            $table->string('reporter_name');
            $table->timestamps();
        });

        // 2. Phục Hồi Chức Năng (FIM) & Y Học Cổ Truyền (Thập Bát Phản)
        Schema::create('rehabilitation_care_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patient_profiles')->onDelete('cascade');
            $table->string('diagnosis'); // Di chứng nhồi máu não liệt nửa người...
            $table->integer('fim_score_total'); // Thang điểm độc lập chức năng FIM (18 - 126 điểm)
            $table->integer('barthel_index'); // Chỉ số Barthel (0 - 100 điểm)
            $table->enum('functional_status', ['TOTAL_ASSISTANCE', 'MAXIMAL_ASSISTANCE', 'MODERATE_ASSISTANCE', 'INDEPENDENT'])->default('MODERATE_ASSISTANCE');
            $table->string('assigned_therapy_type'); // Vật lý trị liệu (PT), Hoạt động trị liệu (OT), Ngôn ngữ (ST)
            $table->text('weekly_goals');
            $table->string('pt_specialist');
            $table->timestamps();
        });

        Schema::create('herbal_prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patient_profiles')->onDelete('cascade');
            $table->string('remedy_name'); // Bài thuốc: Bát trân thang, Lục vị địa hoàng hoàn...
            $table->text('herbal_ingredients'); // Đương quy, Thục địa, Bạch thược, Đảng sâm...
            $table->boolean('has_incompatibility_contraindication')->default(false); // Cảnh báo Thập bát phản / Thập cửu úy
            $table->string('incompatible_pair')->nullable(); // VD: Cam thảo kỵ Cam toại
            $table->integer('decoction_bags_count')->default(6); // Số túi thuốc sắc đóng gói tự động
            $table->string('traditional_doctor');
            $table->timestamps();
        });

        Schema::create('acupoint_therapies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patient_profiles')->onDelete('cascade');
            $table->string('meridian_name'); // Kinh Thái dương Bàng quang, Kinh Dương minh Vị...
            $table->string('acupoints_selected'); // Hợp cốc (LI4), Túc tam lý (ST36), Thái xung (LR3)
            $table->enum('therapy_method', ['ELECTRO_ACUPUNCTURE', 'CATGUT_EMBEDDING', 'ACUPOINT_INJECTION', 'MOXIBUSTION'])->default('ELECTRO_ACUPUNCTURE');
            $table->integer('treatment_course_days')->default(10);
            $table->string('acupuncturist');
            $table->timestamps();
        });

        // 3. Sơ Sinh Chuyên Sâu: Vàng Da Bhutani & Ngân Hàng Sữa Mẹ
        Schema::create('bhutani_jaundice_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('infant_patient_id')->constrained('patient_profiles')->onDelete('cascade');
            $table->integer('age_in_hours'); // Tuổi sau sinh tính bằng giờ (VD: 48h, 72h)
            $table->decimal('total_serum_bilirubin_mg_dl', 5, 2); // Nồng độ Bilirubin toàn phần
            $table->enum('risk_zone', ['LOW_RISK', 'LOW_INTERMEDIATE', 'HIGH_INTERMEDIATE', 'HIGH_RISK'])->default('LOW_RISK');
            $table->boolean('phototherapy_indicated')->default(false); // Chỉ định chiếu đèn xanh
            $table->boolean('exchange_transfusion_indicated')->default(false); // Chỉ định thay máu
            $table->string('neonatologist');
            $table->timestamps();
        });

        Schema::create('donor_milk_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_code')->unique(); // MILK-YYYYMMDD-XXXX
            $table->string('donor_code'); // Mã người hiến tặng sữa
            $table->integer('volume_ml')->default(500);
            $table->decimal('pasteurization_temperature', 4, 1)->default(62.5); // Chuẩn Pasteur 62.5 độ C
            $table->integer('pasteurization_duration_min')->default(30); // 30 phút
            $table->enum('microbiology_result', ['PASSED_STERILE', 'FAILED_CONTAMINATED'])->default('PASSED_STERILE');
            $table->enum('status', ['quarantine', 'available', 'dispensed', 'discarded'])->default('available');
            $table->date('expiry_date');
            $table->timestamps();
        });

        // 4. Bệnh Viện Xanh ESG & An Toàn Bức Xạ ALARA
        Schema::create('green_hospital_esg_logs', function (Blueprint $table) {
            $table->id();
            $table->date('log_date')->unique();
            $table->integer('paperless_sheets_saved')->default(3500); // Số tờ giấy tiết kiệm nhờ EMR
            $table->decimal('carbon_footprint_saved_kg', 8, 2)->default(38.50); // kg CO2 giảm phát thải
            $table->decimal('energy_kwh_conserved', 8, 2)->default(120.00); // kWh điện tiết kiệm từ IoT
            $table->decimal('medical_waste_recycled_kg', 8, 2)->default(45.00); // Rác y tế xử lý tái chế
            $table->integer('esg_score')->default(94); // Thang điểm ESG Bệnh viện xanh (0-100)
            $table->timestamps();
        });

        Schema::create('radiation_dose_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patient_profiles')->onDelete('cascade');
            $table->string('procedure_name'); // Chụp CT Sọ não liều thấp, X-Quang phổi số hóa...
            $table->decimal('dose_msv', 6, 3); // Liều tia xạ ca chụp (mSv)
            $table->decimal('cumulative_dose_msv', 8, 3); // Liều tia xạ tích lũy cả năm
            $table->enum('alara_status', ['OPTIMAL_SAFE', 'MONITOR_CLOSELY', 'EXCEEDED_THRESHOLD'])->default('OPTIMAL_SAFE');
            $table->string('radiology_technician');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radiation_dose_logs');
        Schema::dropIfExists('green_hospital_esg_logs');
        Schema::dropIfExists('donor_milk_batches');
        Schema::dropIfExists('bhutani_jaundice_evaluations');
        Schema::dropIfExists('acupoint_therapies');
        Schema::dropIfExists('herbal_prescriptions');
        Schema::dropIfExists('rehabilitation_care_plans');
        Schema::dropIfExists('adr_adverse_drug_reports');
        Schema::dropIfExists('parenteral_nutritions');
        Schema::dropIfExists('therapeutic_drug_monitorings');
    }
};
