<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng dịch vụ cận lâm sàng và chỉ định xét nghiệm / CĐHA
     */
    public function up(): void
    {
        Schema::create('lab_services', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 200);
            $table->enum('category', ['laboratory', 'imaging', 'functional'])->default('laboratory');
            $table->string('sample_type', 100)->nullable(); // Máu EDTA, Nước tiểu, Huyết thanh...
            $table->string('normal_range', 100)->nullable(); // Khoảng tham chiếu bình thường
            $table->string('unit', 50)->nullable(); // mmol/L, U/L, g/L...
            $table->decimal('price', 12, 2)->default(0);
            $table->string('execution_room', 100)->default('Phòng Xét Nghiệm P.102');
            $table->float('panic_low')->nullable(); // Ngưỡng báo động đỏ dưới
            $table->float('panic_high')->nullable(); // Ngưỡng báo động đỏ trên
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('lab_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 50)->unique();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->foreignId('patient_profile_id')->constrained('patient_profiles')->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->string('clinical_diagnosis', 255)->nullable();
            $table->enum('status', ['pending', 'sampling', 'processing', 'completed', 'cancelled'])->default('pending');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->dateTime('ordered_at');
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lab_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_order_id')->constrained('lab_orders')->cascadeOnDelete();
            $table->foreignId('lab_service_id')->constrained('lab_services')->cascadeOnDelete();
            $table->string('result_value', 255)->nullable();
            $table->float('result_number')->nullable();
            $table->enum('result_status', ['normal', 'high', 'low', 'critical_panic'])->default('normal');
            $table->text('technician_notes')->nullable();
            $table->string('dicom_image_url', 500)->nullable();
            $table->boolean('is_panic_alert')->default(false);
            $table->dateTime('panic_alerted_at')->nullable();
            $table->foreignId('technician_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_order_items');
        Schema::dropIfExists('lab_orders');
        Schema::dropIfExists('lab_services');
    }
};
