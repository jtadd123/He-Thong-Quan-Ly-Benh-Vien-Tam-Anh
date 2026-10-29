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
        Schema::create('patient_surveys', function (Blueprint $table) {
            $table->id();
            $table->string('survey_code', 30)->unique(); // KS-20261028-0001
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->foreignId('patient_profile_id')->nullable()->constrained('patient_profiles')->nullOnDelete();
            
            // 4 Trụ cột chuẩn Bộ 83 Tiêu chí Chất lượng Bệnh viện của Bộ Y Tế
            $table->tinyInteger('rating_reception')->default(5); // 1-5 sao: Đón tiếp & Thủ tục
            $table->tinyInteger('rating_doctor')->default(5); // 1-5 sao: Bác sĩ & Điều dưỡng
            $table->tinyInteger('rating_facilities')->default(5); // 1-5 sao: Cơ sở vật chất & Vệ sinh
            $table->tinyInteger('rating_waiting_time')->default(5); // 1-5 sao: Thời gian chờ đợi

            // Điểm trung bình & Net Promoter Score (NPS)
            $table->decimal('overall_score', 3, 2)->default(5.00);
            $table->tinyInteger('nps_score')->default(10); // 0-10: Sẵn sàng giới thiệu
            $table->text('feedback_text')->nullable();

            // Cảnh báo CSKH khẩn cấp khi người bệnh không hài lòng (<= 2 sao)
            $table->boolean('requires_followup')->default(false);
            $table->string('followup_status')->default('pending'); // pending, resolved, closed
            $table->text('followup_notes')->nullable();
            $table->foreignId('resolved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            $table->index(['requires_followup', 'followup_status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_surveys');
    }
};
