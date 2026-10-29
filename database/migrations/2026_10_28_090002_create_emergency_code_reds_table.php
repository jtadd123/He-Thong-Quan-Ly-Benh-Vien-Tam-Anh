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
        Schema::create('emergency_code_reds', function (Blueprint $table) {
            $table->id();
            $table->string('code_red_number', 30)->unique(); // CR-20261028-0001
            $table->string('patient_name');
            $table->string('gender')->default('unknown'); // male, female, unknown
            $table->string('estimated_age')->nullable();
            $table->foreignId('patient_profile_id')->nullable()->constrained('patient_profiles')->nullOnDelete();
            
            // Thông tin xe cấp cứu & Tuyến vận chuyển
            $table->string('ambulance_unit'); // e.g. Xe Cấp Cứu 115 Tâm Anh - Số 03
            $table->string('paramedic_in_charge')->nullable();
            $table->string('paramedic_phone', 20)->nullable();
            $table->integer('eta_minutes')->default(10); // Thời gian xe tới (phút)

            // Loại cấp cứu tối khẩn
            $table->string('emergency_type'); // stroke (Đột quỵ não), stemi (Nhồi máu cơ tim), trauma (Đa chấn thương), cardiac_arrest (Ngừng tuần hoàn), anaphylaxis (Phản vệ độ 4)
            $table->text('clinical_condition'); // Tình trạng tri giác, huyết áp, thở oxy...
            $table->json('in_transit_vitals')->nullable(); // bp, pulse, spo2, glasgow

            // Kích hoạt & Điều phối toàn viện
            $table->foreignId('activated_by_user_id')->constrained('users');
            $table->timestamp('activated_at');
            $table->timestamp('arrived_at')->nullable(); // Giờ xe cập sảnh cấp cứu (Door time)
            $table->timestamp('intervention_started_at')->nullable(); // Giờ bắt đầu can thiệp (Needle / Balloon time)
            $table->timestamp('resolved_at')->nullable();
            
            $table->string('status')->default('en_route'); // en_route, arrived, in_intervention, resolved, cancelled
            $table->text('outcome_notes')->nullable();

            $table->timestamps();

            $table->index(['status', 'activated_at']);
            $table->index('emergency_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_code_reds');
    }
};
