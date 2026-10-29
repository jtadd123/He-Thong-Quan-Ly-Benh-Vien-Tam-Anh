<?php

namespace App\Services;

use App\Models\DietRegimen;
use App\Models\NutritionAssessment;
use App\Models\PatientProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ClinicalNutritionService
{
    /**
     * Tính toán các chỉ số nhân trắc & năng lượng chuyển hóa lâm sàng
     */
    public function calculateMetrics(float $heightCm, float $weightKg, ?string $gender = 'male', ?int $age = 35): array
    {
        $heightM = $heightCm / 100.0;
        $bmi = round($weightKg / ($heightM * $heightM), 1);

        // Phân loại theo tiêu chuẩn WPRO châu Á
        $category = match (true) {
            $bmi < 18.5 => 'Thiếu cân (Gầy)',
            $bmi <= 22.9 => 'Thể trạng Bình thường',
            $bmi <= 24.9 => 'Tiền béo phì (Thừa cân)',
            $bmi <= 29.9 => 'Béo phì độ I',
            default => 'Béo phì độ II (Nguy cơ tim mạch cao)',
        };

        // Công thức Mifflin-St Jeor tính BMR
        $bmr = (10 * $weightKg) + (6.25 * $heightCm) - (5 * $age);
        if ($gender === 'female') {
            $bmr -= 161;
        } else {
            $bmr += 5;
        }

        // TDEE mức vận động nhẹ (BMR * 1.375)
        $tdee = (int) round($bmr * 1.375);

        // Nhu cầu nước khuyến nghị (khoảng 35ml - 40ml / kg cân nặng)
        $waterMl = (int) round($weightKg * 35);

        return [
            'bmi' => $bmi,
            'category' => $category,
            'bmr' => (int) round($bmr),
            'tdee' => $tdee,
            'water_ml' => $waterMl,
        ];
    }

    /**
     * Tạo hồ sơ đánh giá & phiếu tư vấn thực đơn cá thể hóa
     */
    public function createAssessment(array $data, ?int $consultantUserId = null): NutritionAssessment
    {
        return DB::transaction(function () use ($data, $consultantUserId) {
            $patient = PatientProfile::findOrFail($data['patient_profile_id']);

            $age = $patient->dob ? Carbon::parse($patient->dob)->age : 35;
            $gender = $patient->gender ?? 'male';

            $metrics = $this->calculateMetrics(
                (float) $data['height_cm'],
                (float) $data['weight_kg'],
                $gender,
                $age
            );

            return NutritionAssessment::create([
                'assessment_code' => NutritionAssessment::generateAssessmentCode(),
                'patient_profile_id' => $patient->id,
                'doctor_id' => $data['doctor_id'] ?? null,
                'appointment_id' => $data['appointment_id'] ?? null,
                'height_cm' => (float) $data['height_cm'],
                'weight_kg' => (float) $data['weight_kg'],
                'bmi' => $metrics['bmi'],
                'bmi_category' => $metrics['category'],
                'tdee_kcal' => $data['tdee_kcal'] ?? $metrics['tdee'],
                'diet_regimen_id' => $data['diet_regimen_id'] ?? null,
                'water_intake_ml' => $metrics['water_ml'],
                'custom_instructions' => $data['custom_instructions'] ?? null,
                'consultant_user_id' => $consultantUserId,
                'assessed_at' => Carbon::now(),
            ]);
        });
    }
}
