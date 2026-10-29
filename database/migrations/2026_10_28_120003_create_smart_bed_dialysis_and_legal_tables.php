<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 7. Hemodialysis Dialysis Shift & Dialyzer Reuse Lifecycle
        Schema::create('hemodialysis_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_profile_id')->constrained('patient_profiles')->cascadeOnDelete();
            $table->date('session_date');
            $table->string('shift', 20)->default('morning'); // morning, afternoon, evening
            $table->string('machine_number', 30); // HD-MACHINE-05
            $table->decimal('dry_weight_kg', 4, 1); // Cân nặng khô
            $table->decimal('pre_weight_kg', 4, 1); // Cân nặng trước lọc
            $table->decimal('post_weight_kg', 4, 1)->nullable(); // Cân nặng sau lọc
            $table->integer('uf_target_ml'); // Thể tích siêu lọc mục tiêu (ml)
            $table->integer('uf_actual_ml')->nullable(); // Thể tích siêu lọc thực tế (ml)
            $table->integer('dialyzer_reuse_count')->default(1); // Số lần tái sử dụng màng lọc (1-6)
            $table->string('dialyzer_barcode', 50); // MAVACH-MANG-01
            $table->string('status', 30)->default('ongoing'); // ongoing, completed, aborted_hypotension
            $table->string('nurse_in_charge', 100);
            $table->timestamps();

            $table->index(['patient_profile_id', 'session_date'], 'hd_pt_date_idx');
        });

        // 8. Smart Bed Board & Infection Cleaning Turnaround
        Schema::create('hospital_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number', 20)->unique(); // P.301, P.502
            $table->string('department', 100); // Hồi Sức Tích Cực ICU, Hậu Phẫu Tim Mạch, Nội Tiêu Hóa
            $table->string('room_type', 50)->default('double_bed'); // single_vip, double_bed, negative_pressure_isolation
            $table->integer('floor')->default(3);
            $table->timestamps();
        });

        Schema::create('hospital_beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('hospital_rooms')->cascadeOnDelete();
            $table->string('bed_code', 30)->unique(); // BED-301-A
            $table->string('status', 30)->default('available_clean'); // available_clean, occupied, dirty_pending, cleaning_in_progress, isolation
            $table->foreignId('current_patient_id')->nullable()->constrained('patient_profiles')->nullOnDelete();
            $table->timestamp('last_disinfected_at')->nullable();
            $table->timestamps();
        });

        Schema::create('bed_cleaning_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bed_id')->constrained('hospital_beds')->cascadeOnDelete();
            $table->string('cleaner_name', 100);
            $table->string('disinfectant_chemical', 100); // Cloramin B 0.5%, Aniospray 29M, Con 70...
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->string('verified_by', 100)->nullable(); // Điều dưỡng trưởng kiểm tra
            $table->string('status', 30)->default('completed'); // in_progress, completed, verified
            $table->timestamps();
        });

        // 9. Indoor Hospital Wayfinding & Step-by-Step Navigation
        Schema::create('hospital_wayfindings', function (Blueprint $table) {
            $table->id();
            $table->string('start_location', 100); // Kiosk Sảnh Tầng 1, Bàn Tiếp Đón Khu B
            $table->string('destination_department', 100); // Phòng Khám Nội Tim Mạch 302, Khoa Chẩn Đoán Hình Ảnh
            $table->integer('floor_level');
            $table->integer('estimated_walk_minutes')->default(3);
            $table->json('step_directions'); // Mảng từng bước dẫn đường
            $table->string('accessibility_friendly', 50)->default('wheelchair_accessible'); // wheelchair_accessible, stairs_only
            $table->timestamps();
        });

        // 10. Pre-visit Mobile Health Screener & Tele-Triage
        Schema::create('pre_visit_screenings', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_code', 30);
            $table->string('patient_name', 100);
            $table->string('patient_phone', 20);
            $table->json('symptoms_declared'); // Đau ngực, sốt, ho, khó thở...
            $table->text('home_medications')->nullable(); // Thuốc đang uống tại nhà
            $table->text('known_allergies')->nullable(); // Dị ứng tự khai
            $table->boolean('pregnancy_or_breastfeeding')->default(false);
            $table->text('family_medical_history')->nullable();
            $table->string('risk_stratification', 30)->default('routine'); // routine, priority, urgent_red_flag
            $table->boolean('synced_to_emr')->default(false);
            $table->timestamps();

            $table->index(['appointment_code'], 'pvs_app_code_idx');
        });

        // 11. Real-time Claims Scrubbing & Decision 4210/QD-BYT XML Gateway
        Schema::create('insurance_claim_scrubs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_profile_id')->constrained('patient_profiles')->cascadeOnDelete();
            $table->string('icd10_code', 20); // I10 (Tăng huyết áp), E11 (Đái tháo đường)...
            $table->string('icd10_name', 150);
            $table->decimal('claim_amount', 12, 2);
            $table->string('scrub_status', 30)->default('clean'); // clean, warning_minor, rejected_high_risk
            $table->json('scrub_violations')->nullable(); // Chi tiết các lỗi BHYT phát hiện
            $table->boolean('xml_4210_exported')->default(false);
            $table->string('verified_by', 100)->nullable();
            $table->timestamps();
        });

        // 12. Organ Donation Registry & Living Will / DNR Directive
        Schema::create('organ_donations_and_living_wills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_profile_id')->constrained('patient_profiles')->cascadeOnDelete();
            $table->string('record_type', 30); // organ_donation, living_will_dnr
            $table->string('registration_number', 40)->unique(); // OD-YYYYMMDD-XXXX hoặc DNR-YYYYMMDD-XXXX
            $table->json('organs_pledged')->nullable(); // tim, than, gan, phoi, giac_mac, tuy_tang
            $table->json('dnr_directives')->nullable(); // khong_ep_tim, khong_dat_noi_khi_quan, cham_soc_giam_dau
            $table->string('representative_name', 100);
            $table->string('representative_phone', 20);
            $table->string('representative_relationship', 50);
            $table->boolean('legal_consent_signed')->default(true);
            $table->string('status', 30)->default('active_registered'); // active_registered, verified, revoked
            $table->timestamps();

            $table->index(['patient_profile_id', 'record_type'], 'odlw_pt_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organ_donations_and_living_wills');
        Schema::dropIfExists('insurance_claim_scrubs');
        Schema::dropIfExists('pre_visit_screenings');
        Schema::dropIfExists('hospital_wayfindings');
        Schema::dropIfExists('bed_cleaning_logs');
        Schema::dropIfExists('hospital_beds');
        Schema::dropIfExists('hospital_rooms');
        Schema::dropIfExists('hemodialysis_sessions');
    }
};
