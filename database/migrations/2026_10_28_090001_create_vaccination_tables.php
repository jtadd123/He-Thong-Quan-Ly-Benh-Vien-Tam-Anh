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
        // 1. Danh mục vắc-xin tiêm chủng
        Schema::create('vaccines', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. Vaxigrip Tetra, Prevenar 13, Gardasil 9, Engerix-B
            $table->string('code', 30)->unique(); // e.g. VAC-VAXIGRIP, VAC-PREVENAR13
            $table->string('disease_prevented'); // Cúm mùa, Phế cầu khuẩn, Ung thư CTC do HPV...
            $table->string('manufacturer')->nullable(); // Sanofi Pasteur, Pfizer, MSD...
            $table->string('origin_country')->nullable(); // Pháp, Bỉ, Mỹ...
            $table->string('standard_dosage')->default('0.5ml');
            $table->string('route')->default('Tiêm bắp (IM)'); // Tiêm bắp, tiêm dưới da, uống
            $table->integer('total_doses')->default(1);
            $table->integer('interval_days_between_doses')->default(0); // Khoảng cách mũi tiếp theo (ngày)
            $table->string('storage_temp')->default('2°C - 8°C');
            $table->decimal('price', 12, 2)->default(350000);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Sổ tiêm chủng & Lịch sử tiêm của người bệnh
        Schema::create('vaccination_records', function (Blueprint $table) {
            $table->id();
            $table->string('record_code', 30)->unique(); // VX-20261028-0001
            $table->foreignId('patient_profile_id')->constrained('patient_profiles')->cascadeOnDelete();
            $table->foreignId('vaccine_id')->constrained('vaccines');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->integer('dose_number')->default(1); // Mũi 1, Mũi 2, Mũi nhắc...
            $table->string('batch_number', 50); // Số lô vắc-xin
            $table->date('batch_expiry_date')->nullable();
            $table->string('injection_site')->default('Cơ delta cánh tay trái'); // Vị trí tiêm
            $table->foreignId('administered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Sàng lọc trước tiêm
            $table->boolean('screening_passed')->default(true);
            $table->decimal('body_temperature', 4, 1)->default(36.5);
            $table->text('screening_notes')->nullable();

            // Thực hiện tiêm & Hẹn mũi sau
            $table->timestamp('administered_at');
            $table->date('next_dose_due_date')->nullable();

            // Theo dõi 30 phút sau tiêm
            $table->string('observation_status')->default('observing'); // observing, cleared, adverse_reaction
            $table->timestamp('observation_cleared_at')->nullable();
            $table->text('adverse_reaction_notes')->nullable();

            $table->timestamps();

            $table->index(['patient_profile_id', 'administered_at']);
            $table->index(['observation_status', 'administered_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaccination_records');
        Schema::dropIfExists('vaccines');
    }
};
