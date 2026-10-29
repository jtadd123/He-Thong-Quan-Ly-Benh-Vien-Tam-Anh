<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 5. Niêm Phong Tài Sản Cấp Cứu
        Schema::create('emergency_custodies', function (Blueprint $table) {
            $table->id();
            $table->string('custody_code', 30)->unique();
            $table->foreignId('patient_profile_id')->constrained('patient_profiles')->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->string('bag_seal_number', 50)->comment('Mã niêm phong túi chuyên dụng');
            $table->text('item_description')->comment('Mô tả tài sản (tiền mặt, trang sức, điện thoại...)');
            $table->decimal('cash_amount', 14, 2)->default(0)->comment('Tiền mặt nếu có');
            $table->foreignId('nurse_user_id')->constrained('users')->comment('Điều dưỡng bàn giao');
            $table->foreignId('security_user_id')->constrained('users')->comment('Nhân viên an ninh tiếp nhận');
            $table->string('status', 30)->default('sealed')->comment('sealed: đang niêm phong, returned: đã bàn giao trả');
            $table->string('return_recipient_name', 100)->nullable();
            $table->string('return_recipient_id_card', 20)->nullable();
            $table->string('return_relationship', 50)->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->foreignId('returned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['patient_profile_id', 'status'], 'ec_patient_status_idx');
        });

        // Đồ thất lạc tại sảnh bệnh viện (Lost & Found)
        Schema::create('lost_found_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code', 30)->unique();
            $table->string('item_name', 150);
            $table->string('found_location', 150);
            $table->timestamp('found_at');
            $table->text('description')->nullable();
            $table->string('status', 30)->default('unclaimed')->comment('unclaimed: chưa có người nhận, claimed: đã nhận lại, disposed: hủy/chuyển công an');
            $table->string('claimant_name', 100)->nullable();
            $table->string('claimant_phone', 20)->nullable();
            $table->string('claimant_id_card', 20)->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->foreignId('received_by_user_id')->constrained('users');
            $table->timestamps();

            $table->index('status', 'lf_status_idx');
        });

        // 6. Thẻ Thăm Bệnh Điện Tử & Kiểm Soát Nhiễm Khuẩn (Visitor Pass)
        Schema::create('visitor_passes', function (Blueprint $table) {
            $table->id();
            $table->string('pass_code', 30)->unique();
            $table->foreignId('patient_profile_id')->constrained('patient_profiles')->cascadeOnDelete();
            $table->string('visitor_name', 100);
            $table->string('visitor_phone', 20);
            $table->string('visitor_id_card', 20)->nullable();
            $table->string('relationship', 50);
            $table->string('target_department', 100)->comment('Khoa phòng vào thăm (ICU, Hồi tỉnh, Sơ sinh...)');
            $table->dateTime('valid_from');
            $table->dateTime('valid_to');
            $table->boolean('infection_screening_passed')->default(true)->comment('Đã tầm soát sốt/ho/hô hấp');
            $table->string('status', 30)->default('active')->comment('active, checked_out, expired');
            $table->timestamp('checked_out_at')->nullable();
            $table->foreignId('issued_by_user_id')->constrained('users');
            $table->timestamps();

            $table->index(['patient_profile_id', 'status'], 'vp_patient_status_idx');
        });

        // 7. Báo Cáo Sự Cố Y Khoa Suýt Xảy Ra (Near-Miss Incident)
        Schema::create('medical_incidents', function (Blueprint $table) {
            $table->id();
            $table->string('incident_code', 30)->unique();
            $table->string('reporter_type', 30)->default('named')->comment('named: định danh, anonymous: ẩn danh');
            $table->foreignId('reporter_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('location', 150)->comment('Nơi xảy ra nguy cơ');
            $table->dateTime('occurred_at');
            $table->string('severity_level', 20)->default('NC0')->comment('NC0: Suýt xảy ra, NC1: Nhẹ, NC2: Trung bình, NC3: Nặng');
            $table->string('incident_category', 150)->comment('Nhầm lẫn bệnh nhân, Nhầm thuốc, Sai mẫu xét nghiệm, Nhầm thiết bị, Té ngã...');
            $table->text('description')->comment('Mô tả sự việc');
            $table->text('immediate_action')->nullable()->comment('Hành động ngăn chặn tức thì');
            $table->text('root_cause_analysis')->nullable()->comment('Phân tích nguyên nhân gốc rễ (5 Whys / RCA)');
            $table->string('status', 30)->default('reported')->comment('reported, investigating, resolved, closed');
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('severity_level', 'mi_severity_idx');
        });

        // 8. Công Tác Xã Hội & Miễn Giảm Viện Phí
        Schema::create('social_work_cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_code', 30)->unique();
            $table->foreignId('patient_profile_id')->constrained('patient_profiles')->cascadeOnDelete();
            $table->string('difficulty_type', 50)->comment('Hộ nghèo, Neo đơn, Bệnh hiểm nghèo, Tai nạn vô thừa nhận...');
            $table->decimal('proposed_discount_amount', 14, 2)->default(0)->comment('Số tiền đề xuất hỗ trợ');
            $table->decimal('approved_discount_amount', 14, 2)->default(0);
            $table->string('funding_source', 100)->default('Quỹ Từ Thiện Tâm Anh Heart');
            $table->text('situation_assessment')->comment('Đánh giá hoàn cảnh gia đình & bệnh lý');
            $table->string('status', 30)->default('pending')->comment('pending, approved, rejected, disbursed');
            $table->foreignId('officer_user_id')->constrained('users');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['patient_profile_id', 'status'], 'sw_patient_status_idx');
        });

        // Yêu cầu Thông dịch viên Y khoa
        Schema::create('medical_interpreters', function (Blueprint $table) {
            $table->id();
            $table->string('request_code', 30)->unique();
            $table->foreignId('patient_profile_id')->constrained('patient_profiles')->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->string('language', 50)->comment('Tiếng Anh, Tiếng Trung, Tiếng Hàn, Tiếng Nhật, Ngôn ngữ ký hiệu...');
            $table->string('interpreter_name', 100);
            $table->string('interpreter_phone', 20)->nullable();
            $table->string('status', 30)->default('assigned')->comment('assigned, in_progress, completed, cancelled');
            $table->text('notes')->nullable();
            $table->foreignId('assigned_by_user_id')->constrained('users');
            $table->timestamps();
        });

        // 9. Đội Xe Cấp Cứu & Điều Phối Ngoại Viện
        Schema::create('ambulances', function (Blueprint $table) {
            $table->id();
            $table->string('license_plate', 20)->unique()->comment('Biển số xe');
            $table->string('ambulance_type', 30)->default('standard')->comment('standard: tiêu chuẩn, mobile_icu: hồi sức cấp cứu di động');
            $table->string('driver_name', 100);
            $table->string('driver_phone', 20);
            $table->string('status', 30)->default('available')->comment('available, dispatched, maintenance');
            $table->string('current_location', 150)->default('Sảnh Cấp cứu Bệnh viện Tâm Anh');
            $table->timestamps();
        });

        Schema::create('ambulance_missions', function (Blueprint $table) {
            $table->id();
            $table->string('mission_code', 30)->unique();
            $table->foreignId('ambulance_id')->constrained('ambulances')->cascadeOnDelete();
            $table->foreignId('patient_profile_id')->nullable()->constrained('patient_profiles')->nullOnDelete();
            $table->string('caller_name', 100);
            $table->string('caller_phone', 20);
            $table->string('pickup_address', 255);
            $table->string('destination_address', 255)->default('Khoa Cấp Cứu - Bệnh Viện Tâm Anh');
            $table->string('mission_type', 30)->default('home_pickup')->comment('home_pickup: đón tại nhà, transfer_out: chuyển viện tuyến trên, repatriation: hồi hương');
            $table->decimal('distance_km', 8, 2)->default(0);
            $table->decimal('total_cost', 14, 2)->default(0);
            $table->string('status', 30)->default('dispatched')->comment('dispatched, en_route, on_scene, transporting, completed, cancelled');
            $table->string('doctor_in_charge', 100)->nullable();
            $table->string('nurse_in_charge', 100)->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('dispatched_by_user_id')->constrained('users');
            $table->timestamps();

            $table->index('status', 'am_status_idx');
        });

        // 10. Khám Sức Khỏe Doanh Nghiệp (Corporate Health Screening)
        Schema::create('corporate_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_code', 30)->unique();
            $table->string('company_name', 200);
            $table->string('company_tax_code', 30)->nullable();
            $table->string('contact_person', 100);
            $table->string('contact_phone', 20);
            $table->date('checkup_start_date');
            $table->date('checkup_end_date');
            $table->unsignedInteger('total_employees')->default(0);
            $table->string('package_name', 100)->default('Gói Khám Tiêu Chuẩn Thông Tư 32/2023/TT-BYT');
            $table->decimal('cost_per_person', 14, 2)->default(0);
            $table->string('status', 30)->default('active')->comment('draft, active, completed');
            $table->timestamps();
        });

        Schema::create('corporate_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corporate_contract_id')->constrained('corporate_contracts')->cascadeOnDelete();
            $table->foreignId('patient_profile_id')->nullable()->constrained('patient_profiles')->nullOnDelete();
            $table->string('employee_code', 50);
            $table->string('full_name', 100);
            $table->date('dob');
            $table->string('gender', 10);
            $table->string('phone', 20)->nullable();
            $table->string('department', 100)->nullable();
            $table->string('screening_status', 30)->default('waiting')->comment('waiting, in_progress, completed');
            $table->string('health_classification', 10)->nullable()->comment('Loại I, Loại II, Loại III, Loại IV, Loại V (Chuẩn BYT)');
            $table->text('conclusion')->nullable();
            $table->timestamps();

            $table->index(['corporate_contract_id', 'screening_status'], 'ce_contract_status_idx');
        });

        // 11. Tiếp Nhận Tiền Phẫu & Phẫu Thuật Trong Ngày (Day Surgery)
        Schema::create('day_surgeries', function (Blueprint $table) {
            $table->id();
            $table->string('surgery_code', 30)->unique();
            $table->foreignId('patient_profile_id')->constrained('patient_profiles')->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->string('procedure_name', 200)->comment('Tên thủ thuật/phẫu thuật trong ngày (Phaco, Tán sỏi, Nội soi can thiệp...)');
            $table->foreignId('surgeon_doctor_id')->constrained('doctors');
            $table->dateTime('scheduled_at');
            $table->string('operating_room', 50)->default('Phòng Mổ Ngoại Trú 01');
            $table->unsignedSmallInteger('fasting_hours')->default(8)->comment('Số giờ nhịn ăn uống');
            $table->boolean('checklist_passed')->default(false)->comment('WHO Surgical Safety Checklist Bước Tiếp Nhận');
            $table->string('status', 30)->default('scheduled')->comment('scheduled, pre_op_ready, in_surgery, recovery, discharged, cancelled');
            $table->text('pre_op_notes')->nullable();
            $table->foreignId('checked_by_user_id')->constrained('users');
            $table->timestamps();

            $table->index(['patient_profile_id', 'status'], 'ds_patient_status_idx');
        });

        // 12. Tái Khám Tự Động Bệnh Mãn Tính (Chronic Disease Recall)
        Schema::create('chronic_disease_recalls', function (Blueprint $table) {
            $table->id();
            $table->string('recall_code', 30)->unique();
            $table->foreignId('patient_profile_id')->constrained('patient_profiles')->cascadeOnDelete();
            $table->string('chronic_condition', 150)->comment('Tăng huyết áp, Đái tháo đường Type 2, COPD, Hen suyễn, Tim mạch...');
            $table->date('last_visit_date');
            $table->unsignedSmallInteger('prescription_days')->default(28)->comment('Số ngày thuốc cấp');
            $table->date('due_recall_date')->comment('Ngày tái khám dự kiến');
            $table->string('status', 30)->default('scheduled')->comment('scheduled: chờ nhắc, reminder_sent: đã nhắc, attended: đã tái khám, missed: quá hạn');
            $table->timestamp('reminder_sent_at')->nullable();
            $table->string('reminder_channel', 30)->default('zalo_zns')->comment('zalo_zns, sms, phone_call');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['patient_profile_id', 'status'], 'cdr_patient_status_idx');
            $table->index('due_recall_date', 'cdr_due_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chronic_disease_recalls');
        Schema::dropIfExists('day_surgeries');
        Schema::dropIfExists('corporate_employees');
        Schema::dropIfExists('corporate_contracts');
        Schema::dropIfExists('ambulance_missions');
        Schema::dropIfExists('ambulances');
        Schema::dropIfExists('medical_interpreters');
        Schema::dropIfExists('social_work_cases');
        Schema::dropIfExists('medical_incidents');
        Schema::dropIfExists('visitor_passes');
        Schema::dropIfExists('lost_found_items');
        Schema::dropIfExists('emergency_custodies');
    }
};
