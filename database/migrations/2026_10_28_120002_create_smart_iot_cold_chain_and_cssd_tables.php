<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 4. Occupational Needle Stick & Body Fluid Exposure Surveillance (JCI GL.4 & MOH)
        Schema::create('occupational_exposures', function (Blueprint $table) {
            $table->id();
            $table->string('incident_code', 30)->unique(); // PEP-YYYYMMDD-XXXX
            $table->string('employee_name', 100);
            $table->string('employee_role', 50); // doctor, nurse, lab_tech, cleaning_staff
            $table->string('department', 100);
            $table->string('exposure_type', 50); // needlestick, sharp_cut, splash_mucosa
            $table->foreignId('source_patient_id')->nullable()->constrained('patient_profiles')->nullOnDelete();
            $table->string('source_status', 30)->default('unknown'); // negative, hiv_pos, hbv_pos, hcv_pos, unknown
            $table->boolean('pep_started')->default(false);
            $table->integer('pep_start_hours_elapsed')->nullable(); // Số giờ từ lúc phơi nhiễm đến khi uống ARV (<=72h)
            $table->json('followup_timeline')->nullable(); // Mốc 1 tháng, 3 tháng, 6 tháng
            $table->string('status', 30)->default('monitoring'); // monitoring, completed_cleared, infected_support
            $table->text('incident_description')->nullable();
            $table->timestamps();
        });

        // 5. IoT Cold Chain Continuous Monitoring (GSP & WHO Vaccine Safe Storage)
        Schema::create('cold_chain_storages', function (Blueprint $table) {
            $table->id();
            $table->string('storage_name', 100); // Tủ Bảo Quản Vắc-Xin Trung Tâm, Tủ Máu Khoa Huyết Học...
            $table->string('storage_code', 30)->unique(); // CC-FRIDGE-01
            $table->string('storage_type', 50)->default('vaccine_fridge'); // vaccine_fridge (2-8C), deep_freezer (-20C), blood_bank (2-6C)
            $table->decimal('temp_min', 4, 1)->default(2.0);
            $table->decimal('temp_max', 4, 1)->default(8.0);
            $table->decimal('current_temp', 4, 1)->default(4.5);
            $table->string('status', 30)->default('normal'); // normal, warning_high, warning_low, safety_locked
            $table->boolean('is_locked')->default(false); // Khóa kê đơn an toàn khi hỏng nhiệt độ
            $table->timestamp('last_reading_at')->nullable();
            $table->timestamps();
        });

        Schema::create('temperature_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('storage_id')->constrained('cold_chain_storages')->cascadeOnDelete();
            $table->decimal('temperature', 4, 1);
            $table->decimal('humidity', 4, 1)->nullable();
            $table->boolean('alert_dispatched')->default(false);
            $table->string('status_tag', 20)->default('normal');
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['storage_id', 'recorded_at'], 'temp_storage_time_idx');
        });

        // 6. Central Sterile Supply Department (CSSD) Tray Traceability (JCI PCI)
        Schema::create('sterilization_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('cycle_code', 30)->unique(); // STER-YYYYMMDD-XXXX
            $table->string('machine_name', 100); // Nồi Hấp Autoclave Hơi Nước #01, Máy Tiệt Khuẩn Plasma #02
            $table->string('method', 50); // steam_autoclave_134c, plasma_peroxide, eo_gas
            $table->integer('temp_celsius')->default(134);
            $table->decimal('pressure_bar', 3, 1)->default(2.1);
            $table->boolean('chemical_indicator_pass')->default(true);
            $table->boolean('biological_indicator_pass')->default(true);
            $table->string('operator_name', 100);
            $table->string('status', 30)->default('passed'); // running, passed, failed_rejected
            $table->timestamp('cycle_time');
            $table->timestamps();
        });

        Schema::create('cssd_instrument_trays', function (Blueprint $table) {
            $table->id();
            $table->string('tray_code', 30)->unique(); // TRAY-CARDIO-01
            $table->string('tray_name', 100); // Khay Đại Phẫu Tim Hở, Khay Mổ Nội Soi Ổ Bụng...
            $table->string('department', 100); // Khoa Phẫu Thuật Gây Mê Hồi Sức, Khoa Cấp Cứu
            $table->integer('total_instruments_count');
            $table->json('instruments_manifest'); // Danh mục chi tiết các kẹp, kéo, banh phẫu thuật
            $table->string('status', 30)->default('sterilized'); // contaminated, washing, packed, sterilized, in_or
            $table->foreignId('last_cycle_id')->nullable()->constrained('sterilization_cycles')->nullOnDelete();
            $table->date('expiry_date')->nullable(); // Hạn vô trùng (thường 30 ngày)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cssd_instrument_trays');
        Schema::dropIfExists('sterilization_cycles');
        Schema::dropIfExists('temperature_logs');
        Schema::dropIfExists('cold_chain_storages');
        Schema::dropIfExists('occupational_exposures');
    }
};
