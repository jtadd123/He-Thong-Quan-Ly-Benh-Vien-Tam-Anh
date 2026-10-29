<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng quản lý thiết bị hỗ trợ di chuyển (Xe lăn, cáng đẩy, bình oxy) và lịch sử mượn trả
     */
    public function up(): void
    {
        Schema::create('mobility_equipments', function (Blueprint $table) {
            $table->id();
            $table->string('device_code', 50)->unique(); // XL-001, CG-002, O2-005...
            $table->string('name', 150);
            $table->enum('device_type', ['wheelchair', 'electric_wheelchair', 'stretcher', 'walker', 'oxygen_tank'])->default('wheelchair');
            $table->string('current_location', 100)->default('Sảnh Tiếp Đón Tầng 1');
            $table->enum('status', ['available', 'in_use', 'disinfecting', 'maintenance'])->default('available');
            $table->dateTime('last_disinfected_at')->nullable();
            $table->foreignId('last_disinfected_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('equipment_loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_code', 50)->unique();
            $table->foreignId('mobility_equipment_id')->constrained('mobility_equipments')->cascadeOnDelete();
            $table->foreignId('patient_profile_id')->nullable()->constrained('patient_profiles')->nullOnDelete();
            $table->string('borrower_name', 150);
            $table->string('borrower_phone', 20);
            $table->string('id_card_held', 50)->nullable(); // Số CCCD hoặc giấy tờ giữ lại
            $table->string('destination_department', 100)->nullable(); // Khoa/Phòng di chuyển tới
            $table->dateTime('borrowed_at');
            $table->dateTime('returned_at')->nullable();
            $table->foreignId('dispatched_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('disinfection_notes')->nullable();
            $table->enum('status', ['borrowed', 'returned', 'overdue'])->default('borrowed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_loans');
        Schema::dropIfExists('mobility_equipments');
    }
};
