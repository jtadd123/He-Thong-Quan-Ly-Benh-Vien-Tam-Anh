<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Quản lý Giấy Chuyển Tuyến BHYT Điện Tử (Mẫu 40/BYT)
        Schema::create('insurance_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_code', 30)->unique();
            $table->foreignId('patient_profile_id')->constrained('patient_profiles')->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->string('issuing_hospital_code', 20)->comment('Mã cơ sở KCB ban đầu nơi chuyển đi');
            $table->string('issuing_hospital_name', 200)->comment('Tên cơ sở KCB nơi chuyển');
            $table->string('transfer_type', 30)->default('right_route')->comment('right_route, emergency, patient_request');
            $table->string('icd10_code', 20)->nullable();
            $table->string('icd10_diagnosis', 255)->comment('Chẩn đoán y khoa nơi chuyển');
            $table->text('clinical_summary')->nullable()->comment('Tóm tắt triệu chứng & lý do chuyển viện');
            $table->date('valid_from');
            $table->date('valid_to');
            $table->unsignedTinyInteger('bhyt_reimbursement_rate')->default(100)->comment('Mức hưởng BHYT: 100%, 80%, 40%');
            $table->string('status', 30)->default('valid')->comment('valid, used, expired, rejected');
            $table->foreignId('registered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('rejection_reason', 255)->nullable();
            $table->timestamps();

            $table->index(['patient_profile_id', 'status']);
            $table->index('transfer_code');
        });

        // 2. Ví Ký Quỹ Tạm Ứng Viện Phí Thông Minh
        Schema::create('patient_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_profile_id')->unique()->constrained('patient_profiles')->cascadeOnDelete();
            $table->string('wallet_code', 30)->unique();
            $table->decimal('balance', 14, 2)->default(0);
            $table->string('status', 20)->default('active')->comment('active, frozen, closed');
            $table->timestamps();
        });

        // Lịch sử giao dịch ví ký quỹ (Nạp, Trừ viện phí, Hoàn ứng)
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_wallet_id')->constrained('patient_wallets')->cascadeOnDelete();
            $table->string('transaction_code', 30)->unique();
            $table->string('type', 20)->comment('deposit, deduction, refund');
            $table->decimal('amount', 14, 2);
            $table->decimal('balance_after', 14, 2);
            $table->string('payment_method', 30)->default('vietqr')->comment('vietqr, cash, pos, bank_transfer');
            $table->foreignId('reference_invoice_id')->nullable()->constrained('patient_invoices')->nullOnDelete();
            $table->string('notes', 255)->nullable();
            $table->foreignId('processed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['patient_wallet_id', 'created_at']);
        });

        // 3. Phân Hệ Tiếp Đón VIP & Bảo Lãnh Viện Phí Tư Nhân TPA
        Schema::create('private_insurance_guarantees', function (Blueprint $table) {
            $table->id();
            $table->string('gop_number', 30)->unique()->comment('Số Thư Bảo Lãnh GOP - Guarantee of Payment');
            $table->foreignId('patient_profile_id')->constrained('patient_profiles')->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->string('tpa_company', 100)->comment('Tên công ty bảo hiểm/TPA (Insmart, Bảo Việt, PTI, Liberty...)');
            $table->string('card_number', 50)->comment('Số thẻ bảo hiểm sức khỏe tư nhân');
            $table->decimal('coverage_limit', 14, 2)->default(0)->comment('Hạn mức tối đa được bảo lãnh');
            $table->decimal('approved_amount', 14, 2)->default(0)->comment('Số tiền bảo hiểm đồng ý thanh toán');
            $table->decimal('patient_copay_amount', 14, 2)->default(0)->comment('Số tiền bệnh nhân tự thanh toán phần ngoài bảo lãnh');
            $table->string('guarantee_status', 30)->default('pending')->comment('pending, approved, rejected, settled');
            $table->string('vip_level', 20)->default('standard')->comment('standard, silver, gold, platinum, diamond');
            $table->string('contact_officer', 100)->nullable()->comment('Chuyên viên bồi thường phụ trách');
            $table->string('contact_phone', 20)->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('handled_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['patient_profile_id', 'guarantee_status'], 'pig_patient_status_idx');
        });

        // 4. Cổng Đề Nghị Sao Lục Bệnh Án Điện Tử
        Schema::create('medical_record_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code', 30)->unique();
            $table->foreignId('patient_profile_id')->constrained('patient_profiles')->cascadeOnDelete();
            $table->foreignId('medical_record_id')->nullable()->constrained('medical_records')->nullOnDelete();
            $table->string('requester_type', 30)->default('patient_self')->comment('patient_self, guardian, law_enforcement, insurance_assessor');
            $table->string('requester_name', 100);
            $table->string('requester_phone', 20);
            $table->string('requester_identity_card', 20);
            $table->string('relationship_to_patient', 50)->nullable();
            $table->text('purpose')->comment('Mục đích xin trích sao hồ sơ');
            $table->unsignedSmallInteger('copies_count')->default(1);
            $table->string('status', 30)->default('submitted')->comment('submitted, reviewing, approved, printed, rejected');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('rejection_reason', 255)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['patient_profile_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_record_requests');
        Schema::dropIfExists('private_insurance_guarantees');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('patient_wallets');
        Schema::dropIfExists('insurance_transfers');
    }
};
