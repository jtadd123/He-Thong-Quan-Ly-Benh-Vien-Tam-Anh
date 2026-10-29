<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng quản lý đơn vị túi máu và kiểm soát an toàn truyền máu lâm sàng
     */
    public function up(): void
    {
        Schema::create('blood_units', function (Blueprint $table) {
            $table->id();
            $table->string('unit_code', 50)->unique();
            $table->enum('blood_type_abo', ['A', 'B', 'AB', 'O']);
            $table->enum('blood_type_rh', ['positive', 'negative'])->default('positive');
            $table->enum('product_type', ['prbc', 'ffp', 'platelet', 'cryo'])->default('prbc'); // Khối HC, Huyết tương, Tiểu cầu, Tủa
            $table->integer('volume_ml')->default(250);
            $table->date('collection_date');
            $table->date('expiry_date');
            $table->enum('status', ['in_stock', 'reserved', 'transfusing', 'completed', 'discarded'])->default('in_stock');
            $table->string('storage_location', 100)->default('Tủ bảo quản Ngân hàng Máu P.104');
            $table->timestamps();
        });

        Schema::create('blood_transfusions', function (Blueprint $table) {
            $table->id();
            $table->string('transfusion_code', 50)->unique();
            $table->foreignId('patient_profile_id')->constrained('patient_profiles')->cascadeOnDelete();
            $table->foreignId('blood_unit_id')->constrained('blood_units')->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->string('indication', 255); // Thiếu máu cấp, xuất huyết tiêu hóa, giảm tiểu cầu...
            $table->enum('patient_abo', ['A', 'B', 'AB', 'O']);
            $table->enum('patient_rh', ['positive', 'negative'])->default('positive');
            $table->boolean('is_crossmatch_compatible')->default(true); // Phản ứng chéo hòa hợp
            $table->text('crossmatch_notes')->nullable();
            $table->foreignId('nurse_user_id_1')->nullable()->constrained('users')->nullOnDelete(); // Điều dưỡng đối chiếu 1
            $table->foreignId('nurse_user_id_2')->nullable()->constrained('users')->nullOnDelete(); // Điều dưỡng đối chiếu 2 (Dual Sign-off)
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->json('vitals_baseline')->nullable(); // Sinh hiệu trước truyền: BP, HR, SpO2, Temp
            $table->json('vitals_15min')->nullable(); // Sinh hiệu phút 15
            $table->boolean('reaction_occurred')->default(false); // Có phản ứng tai biến không
            $table->text('reaction_details')->nullable();
            $table->enum('status', ['prepared', 'transfusing', 'completed', 'aborted'])->default('prepared');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_transfusions');
        Schema::dropIfExists('blood_units');
    }
};
