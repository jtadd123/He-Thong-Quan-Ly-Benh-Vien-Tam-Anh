<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. NEWS2 Early Warning System & Rapid Response Team (RRT)
        Schema::create('news2_vital_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_profile_id')->constrained('patient_profiles')->cascadeOnDelete();
            $table->integer('respiratory_rate'); // Nhịp thở (lần/phút)
            $table->integer('oxygen_saturation'); // SpO2 (%)
            $table->boolean('supplemental_oxygen')->default(false); // Có thở oxy không
            $table->integer('systolic_bp'); // Huyết áp tâm thu (mmHg)
            $table->integer('pulse_rate'); // Nhịp tim (lần/phút)
            $table->string('consciousness_level', 20)->default('alert'); // alert, confusion, voice, pain, unresponsive (ACVPU)
            $table->decimal('temperature', 4, 1); // Thân nhiệt độ C
            $table->integer('news2_score'); // Tổng điểm 0 - 20
            $table->string('risk_level', 30); // low (0-4), medium (5-6), high_critical (>=7)
            $table->boolean('rrt_triggered')->default(false);
            $table->text('clinician_notes')->nullable();
            $table->string('recorded_by', 100);
            $table->timestamps();

            $table->index(['patient_profile_id', 'risk_level'], 'news2_pt_risk_idx');
        });

        Schema::create('rrt_activations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news2_alert_id')->nullable()->constrained('news2_vital_alerts')->nullOnDelete();
            $table->foreignId('patient_profile_id')->constrained('patient_profiles')->cascadeOnDelete();
            $table->string('activation_code', 30)->unique(); // RRT-YYYYMMDD-XXXX
            $table->string('ward_location', 100); // Khoa Tim mạch P.302, Khu sảnh A...
            $table->string('status', 30)->default('dispatched'); // dispatched, arrived, stabilized, transferred_icu, expired
            $table->string('lead_doctor', 100)->nullable();
            $table->string('icu_nurse', 100)->nullable();
            $table->integer('response_time_minutes')->nullable();
            $table->text('intervention_summary')->nullable();
            $table->timestamps();
        });

        // 2. Clinical Nursing Risk Assessments: Braden (Pressure Ulcer) & Morse (Fall)
        Schema::create('clinical_risk_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_profile_id')->constrained('patient_profiles')->cascadeOnDelete();
            $table->string('assessment_type', 30); // braden_scale, morse_fall
            $table->integer('score');
            $table->string('risk_category', 30); // low, moderate, high, very_high
            $table->json('details'); // Chi tiết từng thành phần trắc nghiệm
            $table->json('preventive_actions')->nullable(); // Các can thiệp điều dưỡng
            $table->string('assessed_by', 100);
            $table->timestamp('assessed_at');
            $table->timestamps();

            $table->index(['patient_profile_id', 'assessment_type'], 'cra_pt_type_idx');
        });

        // 3. Smart Narcotic Emergency Box (TT 20/2017/TT-BYT)
        Schema::create('narcotic_emergency_boxes', function (Blueprint $table) {
            $table->id();
            $table->string('ward_name', 100); // Tủ Trực Cấp Cứu, Tủ Gây Mê Hồi Sức...
            $table->string('box_code', 30)->unique(); // BOX-EMR-01
            $table->string('medication_name', 100); // Morphin 10mg/ml, Fentanyl 0.1mg/2ml, Diazepam 10mg...
            $table->string('concentration', 50);
            $table->string('unit', 20)->default('ampoule'); // Ống, Lọ
            $table->integer('quantity_stock')->default(0);
            $table->integer('min_threshold')->default(5);
            $table->timestamps();
        });

        Schema::create('narcotic_dispense_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('box_id')->constrained('narcotic_emergency_boxes')->cascadeOnDelete();
            $table->foreignId('patient_profile_id')->constrained('patient_profiles')->cascadeOnDelete();
            $table->string('doctor_name', 100); // Bác sĩ chỉ định
            $table->string('nurse_name', 100); // Điều dưỡng lấy thuốc
            $table->string('witness_name', 100); // Nhân chứng đối chiếu ký kép
            $table->integer('quantity_dispensed');
            $table->text('indication'); // Lý do sử dụng (Giảm đau sau mổ tim, shock tim...)
            $table->integer('empty_ampoules_returned')->default(0);
            $table->boolean('disposal_verified')->default(false); // Đã hủy vỏ ống thuốc độc
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('narcotic_dispense_logs');
        Schema::dropIfExists('narcotic_emergency_boxes');
        Schema::dropIfExists('clinical_risk_assessments');
        Schema::dropIfExists('rrt_activations');
        Schema::dropIfExists('news2_vital_alerts');
    }
};
