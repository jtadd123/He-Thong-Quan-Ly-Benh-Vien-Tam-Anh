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
        Schema::create('clinical_consultations', function (Blueprint $table) {
            $table->id();
            $table->string('consultation_code', 30)->unique(); // HC-20261028-0001
            $table->foreignId('medical_record_id')->nullable()->constrained('medical_records')->nullOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->foreignId('patient_profile_id')->constrained('patient_profiles')->cascadeOnDelete();
            $table->foreignId('requesting_user_id')->constrained('users'); // Bác sĩ / Điều dưỡng gửi yêu cầu
            $table->foreignId('requesting_specialty_id')->nullable()->constrained('specialties')->nullOnDelete();
            $table->foreignId('target_specialty_id')->constrained('specialties'); // Chuyên khoa được mời
            $table->foreignId('target_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->string('urgency_level')->default('routine'); // stat (Tối khẩn 15p), urgent (Khẩn 1h), routine (Thường quy)
            $table->text('reason'); // Lý do mời hội chẩn
            $table->text('clinical_summary'); // Tóm tắt diễn biến, sinh hiệu, cận lâm sàng
            $table->text('proposed_questions'); // Câu hỏi / Vấn đề xin ý kiến
            $table->text('consultation_result')->nullable(); // Kết luận & ý kiến chuyên môn của chuyên khoa được mời
            $table->text('treatment_recommendations')->nullable(); // Khuyến cáo phác đồ phối hợp
            $table->foreignId('consultant_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->timestamp('responded_at')->nullable();
            $table->string('status')->default('requested'); // requested, in_progress, completed, cancelled
            $table->timestamps();

            $table->index(['target_specialty_id', 'status']);
            $table->index(['urgency_level', 'status']);
            $table->index(['patient_profile_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinical_consultations');
    }
};
