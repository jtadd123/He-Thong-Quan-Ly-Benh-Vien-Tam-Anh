<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng quản lý phác đồ thực đơn điều trị và phiếu tư vấn dinh dưỡng lâm sàng
     */
    public function up(): void
    {
        Schema::create('diet_regimens', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique(); // DIET-DIABETES, DIET-DASH...
            $table->string('title', 200);
            $table->string('target_disease', 200);
            $table->integer('daily_calorie_target')->default(1800);
            $table->json('macronutrient_ratio')->nullable(); // {"carb": 55, "protein": 18, "fat": 27}
            $table->text('foods_recommended'); // Các thực phẩm nên ăn
            $table->text('foods_restricted'); // Các thực phẩm hạn chế
            $table->text('foods_avoided'); // Các thực phẩm kiêng tuyệt đối
            $table->json('sample_meal_plan')->nullable(); // Thực đơn mẫu 7 ngày
            $table->timestamps();
        });

        Schema::create('nutrition_assessments', function (Blueprint $table) {
            $table->id();
            $table->string('assessment_code', 50)->unique();
            $table->foreignId('patient_profile_id')->constrained('patient_profiles')->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->float('height_cm');
            $table->float('weight_kg');
            $table->float('bmi'); // W / (H/100)^2
            $table->string('bmi_category', 50); // Gầy, Bình thường, Thừa cân, Béo phì I, II
            $table->integer('tdee_kcal')->default(1800); // Tổng năng lượng khuyến nghị
            $table->foreignId('diet_regimen_id')->nullable()->constrained('diet_regimens')->nullOnDelete();
            $table->integer('water_intake_ml')->default(2000); // Lượng nước uống khuyến nghị ml
            $table->text('custom_instructions')->nullable();
            $table->foreignId('consultant_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('assessed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nutrition_assessments');
        Schema::dropIfExists('diet_regimens');
    }
};
