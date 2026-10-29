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
        // 1. Quản lý Giường Ngoại Trú / Lưu Bệnh Ban Ngày
        Schema::create('day_beds', function (Blueprint $table) {
            $table->id();
            $table->string('room_name'); // e.g. Phòng Lưu Ban Ngày 101, Phòng Hồi Tỉnh Nội Soi
            $table->string('bed_code', 30)->unique(); // e.g. BED-LBN-01, BED-HT-02
            $table->string('bed_type')->default('general'); // general, infusion, recovery, observation
            $table->string('status')->default('available'); // available, occupied, cleaning, maintenance
            $table->decimal('hourly_rate', 10, 2)->default(50000);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Lượt tiếp nhận lưu bệnh ngoại trú
        Schema::create('day_bed_admissions', function (Blueprint $table) {
            $table->id();
            $table->string('admission_code', 30)->unique(); // e.g. ADM-20261028-0001
            $table->foreignId('day_bed_id')->constrained('day_beds');
            $table->foreignId('patient_profile_id')->constrained('patient_profiles');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->foreignId('attending_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->string('attending_nurse_name')->nullable();
            $table->text('admission_reason');
            $table->timestamp('admitted_at');
            $table->timestamp('expected_discharge_at')->nullable();
            $table->timestamp('discharged_at')->nullable();
            $table->string('status')->default('active'); // active, discharged, transferred
            $table->text('discharge_notes')->nullable();
            $table->timestamps();

            $table->index(['day_bed_id', 'status']);
            $table->index(['patient_profile_id', 'status']);
        });

        // 3. Theo dõi truyền dịch tại giường (IV Infusion Flowsheet)
        Schema::create('iv_infusions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('day_bed_admission_id')->constrained('day_bed_admissions')->cascadeOnDelete();
            $table->string('fluid_name'); // Natri Clorid 0.9%, Ringer Lactat, Glucose 5%...
            $table->integer('volume_ml')->default(500);
            $table->integer('drip_rate_drops_per_min')->default(30);
            $table->timestamp('started_at');
            $table->timestamp('estimated_finish_at')->nullable();
            $table->string('status')->default('running'); // running, completed, paused
            $table->string('nurse_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iv_infusions');
        Schema::dropIfExists('day_bed_admissions');
        Schema::dropIfExists('day_beds');
    }
};
