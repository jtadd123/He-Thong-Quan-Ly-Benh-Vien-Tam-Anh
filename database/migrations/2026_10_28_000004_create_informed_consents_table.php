<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('informed_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_profile_id')->constrained('patient_profiles')->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('consent_code', 50)->unique();
            $table->string('procedure_name');
            $table->string('department_name')->default('Khoa Ngoại Tổng Quát');
            $table->string('signer_name');
            $table->enum('signer_relationship', ['patient', 'family', 'guardian'])->default('patient');
            $table->string('signer_phone', 20)->nullable();
            $table->string('signer_id_card', 30)->nullable();
            
            $table->text('diagnosis_description')->nullable();
            $table->text('risks_explained')->nullable();
            $table->text('patient_undertaking')->nullable();
            $table->longText('signature_data'); // Base64 Canvas PNG image
            
            $table->dateTime('signed_at');
            $table->string('ip_address', 45)->nullable();
            $table->enum('status', ['active', 'revoked'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('informed_consents');
    }
};
