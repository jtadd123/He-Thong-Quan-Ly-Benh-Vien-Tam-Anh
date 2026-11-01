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
        Schema::create('doctor_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->foreignId('substitute_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('reason');
            $table->string('status')->default('approved'); // approved, pending, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['doctor_id', 'start_date', 'end_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_leaves');
    }
};
