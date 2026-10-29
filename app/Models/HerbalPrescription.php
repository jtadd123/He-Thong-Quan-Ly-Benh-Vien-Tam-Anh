<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HerbalPrescription extends Model
{
    use HasFactory;

    protected $table = 'herbal_prescriptions';

    protected $fillable = [
        'patient_id',
        'remedy_name',
        'herbal_ingredients',
        'has_incompatibility_contraindication',
        'incompatible_pair',
        'decoction_bags_count',
        'traditional_doctor',
    ];

    protected $casts = [
        'has_incompatibility_contraindication' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    /**
     * Quy tắc Thập Bát Phản (18 vị kỵ nhau trong Dược điển YHCT):
     * - Cam thảo kỵ Cam toại, Đại kích, Nguyên hoa, Hải tảo
     * - Ô đầu kỵ Bán hạ, Qua lâu, Bối mẫu, Bạch cập, Bạch liễm
     */
    public static function checkIncompatibility(string $ingredients): array
    {
        $ingredientsLower = mb_strtolower($ingredients, 'UTF-8');
        
        if (str_contains($ingredientsLower, 'cam thảo') && 
            (str_contains($ingredientsLower, 'cam toại') || str_contains($ingredientsLower, 'hải tảo') || str_contains($ingredientsLower, 'đại kích') || str_contains($ingredientsLower, 'nguyên hoa'))) {
            return [true, 'Cam Thảo phản Cam Toại / Hải Tảo (Độc tính cao - Tuyệt đối không dùng chung)'];
        }

        if (str_contains($ingredientsLower, 'ô đầu') && 
            (str_contains($ingredientsLower, 'bán hạ') || str_contains($ingredientsLower, 'bối mẫu') || str_contains($ingredientsLower, 'bạch cập'))) {
            return [true, 'Ô Đầu phản Bán Hạ / Bối Mẫu (Tăng độc tính gây trụy tim mạch)'];
        }

        return [false, null];
    }

    /**
     * Kiểm tra tương kỵ mảng thảo dược
     */
    public static function checkEighteenIncompatibilities(array $herbList): array
    {
        $names = [];
        foreach ($herbList as $herb) {
            $names[] = is_array($herb) ? ($herb['name'] ?? '') : (string)$herb;
        }
        $combined = implode(' ', $names);
        [$has, $reason] = self::checkIncompatibility($combined);

        return $has ? [$reason] : [];
    }
}
