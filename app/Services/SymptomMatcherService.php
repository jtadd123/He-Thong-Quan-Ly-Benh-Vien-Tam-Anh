<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Specialty;
use Illuminate\Support\Collection;

class SymptomMatcherService
{
    /**
     * Medical knowledge base mapping symptom patterns to specialty search terms.
     */
    protected array $symptomRules = [
        'cardio' => [
            'specialty_keywords' => ['Tim mạch', 'Tim mạch học'],
            'terms' => [
                'đau ngực', 'thắt ngực', 'đau thắt ngực', 'khó thở khi gắng sức',
                'hồi hộp', 'đánh trống ngực', 'tim đập nhanh', 'huyết áp cao',
                'tăng huyết áp', 'hạ huyết áp', 'loạn nhịp', 'suy tim', 'phù chân',
                'ngất', 'nặng ngực', 'nhói ngực'
            ],
            'advice' => 'Các triệu chứng liên quan đến hệ tim mạch cần được thăm khám điện tâm đồ (ECG), siêu âm tim và xét nghiệm men tim sớm.',
            'is_emergency_trigger' => ['đau thắt ngực dữ dội', 'đau ngực lan ra tay trái', 'ngất xỉu đột ngột'],
        ],
        'pediatric' => [
            'specialty_keywords' => ['Nhi khoa', 'Nhi'],
            'terms' => [
                'trẻ em', 'sốt ở trẻ', 'bé sốt', 'co giật', 'biếng ăn', 'quấy khóc',
                'nôn trớ', 'tiêu chảy ở trẻ', 'phát ban trẻ em', 'sởi', 'tay chân miệng',
                'thủy đậu', 'chậm lớn', 'viêm phế quản trẻ em', 'bé ho'
            ],
            'advice' => 'Khoa Nhi Tâm Anh cung cấp dịch vụ khám toàn diện cho trẻ sơ sinh và trẻ nhỏ với phòng khám thân thiện không gian vui chơi.',
            'is_emergency_trigger' => ['sốt cao co giật', 'trẻ li bì khó thở', 'tím tái'],
        ],
        'orthopedic' => [
            'specialty_keywords' => ['Chấn thương chỉnh hình', 'Cơ xương khớp', 'Chỉnh hình'],
            'terms' => [
                'đau khớp', 'khớp gối', 'thoát vị đĩa đệm', 'thoái hóa cột sống',
                'đau lưng', 'gãy xương', 'bong gân', 'trật khớp', 'đau cổ vai gáy',
                'tê bì chân tay', 'khó vận động', 'cứng khớp', 'viêm khớp', 'đứt dây chằng'
            ],
            'advice' => 'Khám chuyên khoa Chấn thương chỉnh hình giúp chụp X-quang/MRI và phục hồi chức năng vận động nhanh chóng.',
            'is_emergency_trigger' => ['gãy xương hở', 'mất cảm giác chi sau chấn thương'],
        ],
        'ent' => [
            'specialty_keywords' => ['Tai Mũi Họng'],
            'terms' => [
                'đau họng', 'viêm họng', 'nghẹt mũi', 'sổ mũi', 'chảy mũi',
                'ù tai', 'viêm tai giữa', 'chảy mủ tai', 'khàn tiếng', 'mất giọng',
                'viêm xoang', 'amidan', 'nuốt vướng', 'ngáy', 'ngủ ngáy'
            ],
            'advice' => 'Nội soi Tai Mũi Họng ống mềm không đau giúp phát hiện chính xác các tổn thương vùng tai, mũi xoang và thanh quản.',
            'is_emergency_trigger' => ['khó thở thanh quản', 'dị vật đường thở'],
        ],
        'gastro' => [
            'specialty_keywords' => ['Tiêu hóa - Gan mật', 'Tiêu hóa', 'Gan mật'],
            'terms' => [
                'đau dạ dày', 'đau bao tử', 'ợ chua', 'trào ngược', 'đau thượng vị',
                'đầy bụng', 'khó tiêu', 'viêm loét dạ dày', 'tiêu chảy', 'táo bón',
                'đi ngoài ra máu', 'men gan cao', 'viêm gan', 'vàng da', 'sỏi mật'
            ],
            'advice' => 'Khoa Tiêu hóa Tâm Anh ứng dụng kỹ thuật nội soi dạ dày - đại tràng không đau, phát hiện sớm polyp và ung thư tiêu hóa.',
            'is_emergency_trigger' => ['nôn ra máu', 'đau bụng dữ dội liên tục'],
        ],
        'ophthalmic' => [
            'specialty_keywords' => ['Mắt (Nhãn khoa)', 'Mắt', 'Nhãn khoa'],
            'terms' => [
                'đau mắt', 'mờ mắt', 'giảm thị lực', 'đỏ mắt', 'chảy nước mắt',
                'ngứa mắt', 'khô mắt', 'cận thị', 'loạn thị', 'cườm mắt',
                'đục thủy tinh thể', 'nhìn đôi', 'cộm mắt', 'mỏi mắt'
            ],
            'advice' => 'Khám mắt định kỳ và đo khúc xạ chuyên sâu giúp bảo vệ thị lực và phát hiện sớm các bệnh lý đáy mắt.',
            'is_emergency_trigger' => ['mất thị lực đột ngột', 'chấn thương rách mắt'],
        ],
    ];

    /**
     * Match user symptoms and return structured diagnosis recommendations.
     */
    public function match(string $symptomText): array
    {
        $normalizedText = mb_strtolower(trim($symptomText), 'UTF-8');
        if (empty($normalizedText)) {
            return [
                'has_match' => false,
                'message' => 'Vui lòng nhập mô tả các triệu chứng hoặc cảm giác khó chịu của bạn.',
            ];
        }

        $bestCategory = null;
        $highestScore = 0;
        $matchedKeywords = [];
        $isEmergency = false;

        foreach ($this->symptomRules as $categoryKey => $rule) {
            $score = 0;
            $currentMatched = [];

            // Check emergency triggers first
            foreach ($rule['is_emergency_trigger'] as $emergencyTrigger) {
                if (str_contains($normalizedText, mb_strtolower($emergencyTrigger, 'UTF-8'))) {
                    $isEmergency = true;
                    $score += 20;
                    $currentMatched[] = $emergencyTrigger;
                }
            }

            // Check regular symptom terms
            foreach ($rule['terms'] as $term) {
                if (str_contains($normalizedText, mb_strtolower($term, 'UTF-8'))) {
                    $score += mb_strlen($term) > 6 ? 5 : 3;
                    $currentMatched[] = $term;
                }
            }

            if ($score > $highestScore) {
                $highestScore = $score;
                $bestCategory = $categoryKey;
                $matchedKeywords = array_unique($currentMatched);
            }
        }

        // If no specific match found, fallback to General Internal / Outpatient
        if (!$bestCategory || $highestScore === 0) {
            $defaultSpecialty = Specialty::where('is_active', true)->first();
            return [
                'has_match' => false,
                'is_emergency' => false,
                'message' => 'Hệ thống chưa tìm thấy từ khóa đặc hiệu cho triệu chứng bạn mô tả. Chúng tôi khuyến nghị bạn đăng ký khám Đa khoa / Nội tổng quát để bác sĩ khám sàng lọc ban đầu.',
                'fallback_specialty' => $defaultSpecialty ? [
                    'id' => $defaultSpecialty->id,
                    'name' => $defaultSpecialty->name,
                    'slug' => $defaultSpecialty->slug,
                ] : null,
            ];
        }

        $rule = $this->symptomRules[$bestCategory];
        
        // Find matching active Specialty in Database
        $specialty = Specialty::where('is_active', true)
            ->where(function ($query) use ($rule) {
                foreach ($rule['specialty_keywords'] as $keyword) {
                    $query->orWhere('name', 'LIKE', "%{$keyword}%");
                }
            })->first();

        // If specific specialty not active in DB, fallback to any active
        if (!$specialty) {
            $specialty = Specialty::where('is_active', true)->first();
        }

        // Retrieve top available doctors in this specialty
        $recommendedDoctors = [];
        if ($specialty) {
            $doctors = Doctor::with('user')
                ->where('specialty_id', $specialty->id)
                ->where('is_available', true)
                ->take(4)
                ->get();

            $recommendedDoctors = $doctors->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'name' => $doc->full_name_with_title,
                    'qualification' => $doc->qualification,
                    'avatar_url' => $doc->avatar_url,
                    'room_number' => $doc->room_number,
                    'consultation_fee' => number_format($doc->consultation_fee, 0, ',', '.') . ' VNĐ',
                    'profile_url' => route('public.doctors.show', $doc->user->username ?? $doc->id),
                    'booking_url' => url('/booking?doctor_id=' . $doc->id . '&specialty_id=' . $doc->specialty_id),
                    'is_on_duty_today' => $doc->is_on_duty_today,
                    'today_shifts_text' => $doc->today_shifts_text,
                ];
            })->toArray();
        }

        return [
            'has_match' => true,
            'is_emergency' => $isEmergency,
            'matched_keywords' => array_values($matchedKeywords),
            'confidence_score' => min(98, 70 + ($highestScore * 2)),
            'specialty' => $specialty ? [
                'id' => $specialty->id,
                'name' => $specialty->name,
                'slug' => $specialty->slug,
                'description' => $specialty->description,
            ] : null,
            'advice' => $rule['advice'],
            'emergency_alert' => $isEmergency ? 'Triệu chứng có dấu hiệu cảnh báo nguy cơ cấp cứu! Nếu bạn cảm thấy đau dữ dội, khó thở nặng hoặc suy giảm ý thức, vui lòng gọi cấp cứu 115 hoặc Hotline Bệnh viện Tâm Anh: 1800 6858 ngay lập tức.' : null,
            'recommended_doctors' => $recommendedDoctors,
        ];
    }
}
