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
        Schema::create('medication_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_code', 30)->unique(); // TRK-20261028-0001
            $table->foreignId('medical_record_id')->constrained('medical_records')->cascadeOnDelete();
            $table->foreignId('patient_profile_id')->constrained('patient_profiles')->cascadeOnDelete();
            $table->string('recipient_name');
            $table->string('recipient_phone', 20);
            $table->string('delivery_address');
            $table->string('carrier_name')->default('tam_anh_express'); // tam_anh_express, viettel_post, grab_express
            $table->string('waybill_number', 50)->nullable();
            $table->decimal('shipping_fee', 10, 2)->default(30000);
            $table->boolean('requires_cold_chain')->default(false); // Yêu cầu bảo quản lạnh 2-8°C (Insulin, Vắc-xin)
            $table->string('status')->default('pending_packaging'); // pending_packaging, packaged, in_transit, delivered, failed
            $table->text('pharmacist_notes')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('patient_profile_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_deliveries');
    }
};
