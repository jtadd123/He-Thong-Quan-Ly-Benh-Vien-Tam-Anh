<?php

namespace App\Services;

class AllergyCrossReactionService
{
    /**
     * Cross-Allergy Clinical Knowledge Base.
     * Maps known patient allergen keywords to potentially dangerous cross-reactive drug classes and ingredients.
     */
    protected static array $allergyRules = [
        // 1. Nhóm Penicillin / Beta-lactam
        'penicillin' => [
            'name' => 'Nhóm Penicillin / Beta-lactam',
            'direct_triggers' => ['penicillin', 'amoxicillin', 'ampicillin', 'augmentin', 'clavamox', 'unasyn', 'piperacillin', 'oxacillin'],
            'cross_triggers' => [
                'cephalexin' => 'Cephalosporin Thế hệ 1 (Nguy cơ dị ứng chéo 10-15%)',
                'cefalexin' => 'Cephalosporin Thế hệ 1 (Nguy cơ dị ứng chéo 10-15%)',
                'cefadroxil' => 'Cephalosporin Thế hệ 1 (Nguy cơ dị ứng chéo 10-15%)',
                'cefaclor' => 'Cephalosporin Thế hệ 2 (Nguy cơ dị ứng chéo 5-8%)',
                'cefuroxime' => 'Cephalosporin Thế hệ 2 (Nguy cơ dị ứng chéo 5-8%)',
                'cefixime' => 'Cephalosporin Thế hệ 3 (Nguy cơ phản ứng quá mẫn)',
                'ceftriaxone' => 'Cephalosporin Thế hệ 3 (Nguy cơ phản ứng quá mẫn)',
                'meropenem' => 'Carbapenem (Nguy cơ dị ứng chéo chéo thấp ~1%, cần theo dõi sát)',
                'imipenem' => 'Carbapenem (Nguy cơ dị ứng chéo)',
            ],
            'anaphylaxis_risk' => 'RẤT CAO (Nguy cơ sốc phản vệ Độ III-IV, phù mạch Quinke, co thắt thanh quản cấp)',
            'recommendation' => 'Tránh chỉ định kháng sinh nhóm Beta-lactam/Cephalosporin thế hệ đầu. Thay thế bằng nhóm Macrolid (Azithromycin/Clarithromycin), Fluoroquinolone hoặc Lincosamide (Clindamycin).',
        ],

        // 2. Nhóm Aspirin / Salicylate
        'aspirin' => [
            'name' => 'Aspirin & Các dẫn xuất Salicylate',
            'direct_triggers' => ['aspirin', 'acid acetylsalicylic', 'acetylsalicylic acid'],
            'cross_triggers' => [
                'ibuprofen' => 'Nhóm NSAID ức chế COX-1 (Hội chứng không dung nạp Aspirin/NSAID chéo)',
                'diclofenac' => 'Nhóm NSAID ức chế COX-1/COX-2 (Nguy cơ cơn hen ác tính)',
                'meloxicam' => 'Nhóm NSAID oxicam (Nguy cơ phản ứng quá mẫn)',
                'naproxen' => 'Nhóm NSAID dẫn xuất acid propionic',
                'celecoxib' => 'Nhóm ức chế chọn lọc COX-2 (Cần thận trọng theo dõi)',
                'piroxicam' => 'Nhóm NSAID oxicam',
                'ketoprofen' => 'Nhóm NSAID propionic',
            ],
            'anaphylaxis_risk' => 'CAO (Cơn hen phế quản co thắt ác tính kịch phát, mày đay phù mạch phù thanh quản)',
            'recommendation' => 'Tuyệt đối không dùng các thuốc giảm đau kháng viêm NSAID không chọn lọc. Giảm đau ưu tiên dùng Paracetamol liều vừa phải hoặc giảm đau thần kinh.',
        ],

        // 3. Nhóm Sulfonamide (Sulfa drugs)
        'sulfonamide' => [
            'name' => 'Nhóm Kháng sinh Sulfonamide',
            'direct_triggers' => ['sulfamethoxazole', 'bactrim', 'cotrimoxazole', 'biseptol', 'sulfadiazine'],
            'cross_triggers' => [
                'furosemide' => 'Thuốc lợi tiểu quai dẫn xuất Sulfonamide (Nguy cơ dị ứng chéo cấu trúc)',
                'furosemid' => 'Thuốc lợi tiểu quai dẫn xuất Sulfonamide (Nguy cơ dị ứng chéo cấu trúc)',
                'hydrochlorothiazide' => 'Thuốc lợi tiểu thiazide có nhóm sulfa',
                'gliclazide' => 'Thuốc hạ đường huyết nhóm Sulfonylurea',
                'glimepiride' => 'Thuốc hạ đường huyết nhóm Sulfonylurea',
                'celecoxib' => 'Thuốc NSAID chứa nhóm benzenesulfonamide',
            ],
            'anaphylaxis_risk' => 'TRUNG BÌNH - CAO (Hội chứng Stevens-Johnson, hoại tử thượng bì nhiễm độc TEN, phát ban bóng nước)',
            'recommendation' => 'Cân nhắc tránh các thuốc chứa nhóm Sulfonamide không phải kháng sinh nếu tiền sử dị ứng nặng.',
        ],

        // 4. Nhóm Paracetamol / Acetaminophen
        'paracetamol' => [
            'name' => 'Paracetamol (Acetaminophen)',
            'direct_triggers' => ['paracetamol', 'acetaminophen', 'efferalgan', 'panadol', 'hapacol', 'tylenol', 'ultracet', 'decolgen'],
            'cross_triggers' => [],
            'anaphylaxis_risk' => 'TRUNG BÌNH (Mày đay, phù Quinke, hiếm gặp sốc phản vệ; nguy cơ độc tế bào gan)',
            'recommendation' => 'Không kê đơn bất kỳ biệt dược nào có chứa thành phần Paracetamol. Xem xét dùng thuốc giảm đau khác.',
        ],

        // 5. Nhóm Kháng sinh Macrolid
        'macrolid' => [
            'name' => 'Nhóm Kháng sinh Macrolid',
            'direct_triggers' => ['erythromycin', 'clarithromycin', 'azithromycin', 'roxithromycin', 'spiramycin'],
            'cross_triggers' => [
                'clarithromycin' => 'Dị ứng chéo cùng nhóm Macrolid',
                'azithromycin' => 'Dị ứng chéo cùng nhóm Macrolid',
                'erythromycin' => 'Dị ứng chéo cùng nhóm Macrolid',
            ],
            'anaphylaxis_risk' => 'TRUNG BÌNH (Dị ứng mày đay, kéo dài khoảng QT, rối loạn nhịp thất)',
            'recommendation' => 'Dị ứng chéo giữa các Macrolid gần như hoàn toàn. Đổi sang nhóm kháng sinh khác (Beta-lactam nếu không dị ứng, hoặc Quinolone).',
        ],

        // 6. Nhóm Kháng sinh Quinolone / Fluoroquinolone
        'quinolone' => [
            'name' => 'Nhóm Kháng sinh Quinolone / Fluoroquinolone',
            'direct_triggers' => ['ciprofloxacin', 'levofloxacin', 'ofloxacin', 'moxifloxacin', 'norfloxacin', 'ciprobay', 'tavanic'],
            'cross_triggers' => [
                'levofloxacin' => 'Dị ứng chéo cùng họ Fluoroquinolone',
                'moxifloxacin' => 'Dị ứng chéo cùng họ Fluoroquinolone',
                'ciprofloxacin' => 'Dị ứng chéo cùng họ Fluoroquinolone',
            ],
            'anaphylaxis_risk' => 'CAO (Phản vệ, viêm đứt gân gót Achilles, nhiễm độc thần kinh trung ương)',
            'recommendation' => 'Dị ứng chéo giữa các Fluoroquinolone rất cao. Cấm chỉ định lại toàn bộ nhóm Quinolone.',
        ],
    ];

    /**
     * Check prescribed medications against patient allergy history.
     *
     * @param string|null $patientAllergies Chuỗi tiền sử dị ứng lưu trong hồ sơ bệnh nhân
     * @param array $prescribedMedications Danh sách thuốc trong đơn (gồm name, active_ingredient)
     * @return array Kết quả kiểm tra gồm has_critical_allergy, alerts count và danh sách cảnh báo chi tiết
     */
    public static function checkAllergies(?string $patientAllergies, array $prescribedMedications): array
    {
        $rawAllergy = mb_strtolower(trim($patientAllergies ?? ''));

        // Kiểm tra nếu bệnh nhân không có dị ứng
        if (empty($rawAllergy) || in_array($rawAllergy, ['không', 'không có', 'chưa ghi nhận', 'none', 'k', 'ko', 'k có', 'bình thường'])) {
            return [
                'has_critical_allergy' => false,
                'alerts_count' => 0,
                'alerts' => [],
                'patient_allergy_text' => 'Không ghi nhận dị ứng',
            ];
        }

        $alerts = [];

        foreach (self::$allergyRules as $ruleKey => $rule) {
            $matchedPatientAllergen = null;

            // Kiểm tra xem tiền sử dị ứng của bệnh nhân có chứa từ khóa của nhóm này không
            if (str_contains($rawAllergy, $ruleKey)) {
                $matchedPatientAllergen = $rule['name'];
            } else {
                foreach ($rule['direct_triggers'] as $dt) {
                    if (str_contains($rawAllergy, $dt)) {
                        $matchedPatientAllergen = $dt;
                        break;
                    }
                }
            }

            if (!$matchedPatientAllergen) {
                continue;
            }

            // Nếu bệnh nhân có dị ứng với nhóm này, kiểm tra từng thuốc bác sĩ kê
            foreach ($prescribedMedications as $med) {
                $medName = is_array($med) ? ($med['name'] ?? '') : (string) $med;
                $activeIng = is_array($med) ? ($med['active_ingredient'] ?? '') : '';
                $searchTarget = mb_strtolower("{$medName} {$activeIng}");

                // 1. Kiểm tra dị ứng TRỰC TIẾP (Direct allergy trigger)
                foreach ($rule['direct_triggers'] as $directTrigger) {
                    if (str_contains($searchTarget, $directTrigger)) {
                        $alerts[] = [
                            'title' => "Cảnh báo: Dị ứng trực tiếp hoạt chất [{$directTrigger}] ({$rule['name']})",
                            'medication_name' => $medName,
                            'active_ingredient' => $activeIng ?: $directTrigger,
                            'allergen_detected' => $matchedPatientAllergen,
                            'reaction_type' => 'DIRECT_ALLERGY',
                            'reaction_label' => 'DỊ ỨNG TRỰC TIẾP HOẠT CHẤT',
                            'severity' => 'danger',
                            'severity_badge' => 'bg-danger text-white',
                            'anaphylaxis_risk' => $rule['anaphylaxis_risk'],
                            'explanation' => "Thuốc kê đơn chứa trực tiếp hoạt chất [{$directTrigger}], trùng khớp với tiền sử dị ứng [{$matchedPatientAllergen}] của bệnh nhân.",
                            'recommendation' => $rule['recommendation'],
                        ];
                        break;
                    }
                }

                // 2. Kiểm tra dị ứng CHÉO (Cross-reactive triggers)
                foreach ($rule['cross_triggers'] as $crossTrigger => $crossDesc) {
                    if (str_contains($searchTarget, $crossTrigger)) {
                        $alerts[] = [
                            'title' => "Nguy cơ dị ứng chéo {$crossDesc}: {$medName} với {$rule['name']}",
                            'medication_name' => $medName,
                            'active_ingredient' => $activeIng ?: $crossTrigger,
                            'allergen_detected' => $matchedPatientAllergen,
                            'reaction_type' => 'CROSS_REACTIVE',
                            'reaction_label' => 'NGUY CƠ DỊ ỨNG CHÉO (CROSS-ALLERGY)',
                            'severity' => 'danger',
                            'severity_badge' => 'bg-danger text-white animate-pulse',
                            'anaphylaxis_risk' => $rule['anaphylaxis_risk'],
                            'explanation' => "Thuốc [{$medName}] ({$crossTrigger}) có cấu trúc dị ứng chéo với nhóm [{$matchedPatientAllergen}]: {$crossDesc}. Nguy cơ cơn hen co thắt phế quản hoặc sốc phản vệ.",
                            'recommendation' => $rule['recommendation'],
                        ];
                        break;
                    }
                }
            }
        }

        return [
            'has_critical_allergy' => count($alerts) > 0,
            'alerts_count' => count($alerts),
            'alerts' => $alerts,
            'patient_allergy_text' => $patientAllergies,
        ];
    }
}
