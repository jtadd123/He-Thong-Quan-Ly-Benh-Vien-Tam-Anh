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
        Schema::table('appointments', function (Blueprint $table) {
            $table->unsignedTinyInteger('esi_level')->default(5)->after('status');
            $table->json('esi_criteria')->nullable()->after('esi_level');
            $table->string('triage_notes', 500)->nullable()->after('esi_criteria');
            $table->foreignId('triaged_by_user_id')->nullable()->after('triage_notes')->constrained('users')->nullOnDelete();
            $table->timestamp('triaged_at')->nullable()->after('triaged_by_user_id');
            $table->timestamp('called_at')->nullable()->after('triaged_at');
            $table->string('counter_number', 20)->nullable()->after('called_at');
            $table->index(['appointment_date', 'esi_level'], 'app_date_esi_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('app_date_esi_idx');
            $table->dropForeign(['triaged_by_user_id']);
            $table->dropColumn([
                'esi_level',
                'esi_criteria',
                'triage_notes',
                'triaged_by_user_id',
                'triaged_at',
                'called_at',
                'counter_number',
            ]);
        });
    }
};
