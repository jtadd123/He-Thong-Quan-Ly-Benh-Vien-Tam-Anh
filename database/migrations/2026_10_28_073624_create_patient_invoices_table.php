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
        Schema::create('patient_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_code', 50)->unique();
            $table->foreignId('patient_profile_id')->constrained('patient_profiles')->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->foreignId('medical_record_id')->nullable()->constrained('medical_records')->nullOnDelete();

            // Các khoản phí chi tiết
            $table->decimal('consultation_fee', 12, 2)->default(0);
            $table->decimal('medication_fee', 12, 2)->default(0);
            $table->decimal('procedure_fee', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);

            // BHYT & Đồng chi trả
            $table->decimal('insurance_covered_amount', 12, 2)->default(0);
            $table->decimal('patient_co_pay_amount', 12, 2)->default(0);

            // Trạng thái & Phương thức thanh toán
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
            $table->enum('payment_method', ['cash', 'vietqr', 'card', 'insurance_direct'])->default('cash');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('received_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('vietqr_ref', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['patient_profile_id', 'payment_status'], 'inv_patient_status_idx');
            $table->index(['created_at', 'payment_status'], 'inv_date_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_invoices');
    }
};
