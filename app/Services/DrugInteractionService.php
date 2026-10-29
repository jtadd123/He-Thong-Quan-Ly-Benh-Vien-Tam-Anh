<?php

namespace App\Services;

class DrugInteractionService
{
    /**
     * Standard Medical Drug-Drug Interaction Rules (DDI Matrix).
     */
    protected static array $rules = [
        [
            'drug_a' => ['aspirin', 'aspilet'],
            'drug_b' => ['ibuprofen', 'diclofenac', 'meloxicam', 'celecoxib', 'naproxen'],
            'severity' => 'danger',
            'title' => 'Tương tác chống chỉ định: Tăng nguy cơ xuất huyết tiêu hóa nghiêm trọng',
            'description' => 'Phối hợp Aspirin với NSAID làm tăng gấp 4-6 lần nguy cơ viêm loét, thủng dạ dày - tá tràng và ức chế kết tập tiểu cầu kéo dài.',
            'recommendation' => 'Tránh phối hợp. Nếu bắt buộc giảm đau kháng viêm, cân nhắc dùng Paracetamol hoặc bổ sung thuốc bảo vệ dạ dày (PPI như Nexium).',
        ],
        [
            'drug_a' => ['paracetamol', 'panadol', 'efferalgan', 'hapacol'],
            'drug_b' => ['paracetamol', 'panadol', 'efferalgan', 'hapacol', 'tylenol'],
            'severity' => 'danger',
            'title' => 'Trùng lặp hoạt chất Paracetamol: Nguy cơ quá liều độc gan cấp',
            'description' => 'Đơn thuốc chứa từ 2 chế phẩm cùng có Paracetamol, tổng liều dễ vượt quá 4g/ngày dẫn đến hoại tử tế bào gan không hồi phục.',
            'recommendation' => 'Chỉ giữ lại 1 loại thuốc chứa Paracetamol trong đơn và dặn bệnh nhân không tự ý uống thêm thuốc hạ sốt bên ngoài.',
        ],
        [
            'drug_a' => ['clarithromycin', 'erythromycin'],
            'drug_b' => ['amlodipine', 'amlodipin', 'simvastatin', 'atorvastatin'],
            'severity' => 'danger',
            'title' => 'Tương tác dược động học CYP3A4 nguy cơ cao',
            'description' => 'Kháng sinh nhóm Macrolid ức chế mạnh men gan CYP3A4, làm tăng vọt nồng độ Amlodipine/Statin trong máu, gây tụt huyết áp nặng hoặc tiêu cơ vân cấp.',
            'recommendation' => 'Cân nhắc chuyển sang kháng sinh nhóm Beta-lactam (Augmentin) hoặc tạm ngưng thuốc tim mạch/mỡ máu trong thời gian dùng Macrolid.',
        ],
        [
            'drug_a' => ['ciprofloxacin', 'levofloxacin'],
            'drug_b' => ['antacid', 'canxi', 'calcium', 'sat', 'iron', 'magie'],
            'severity' => 'warning',
            'title' => 'Tương tác giảm hấp thu kháng sinh do tạo phức Chelate',
            'description' => 'Các ion kim loại hóa trị 2 và 3 liên kết với kháng sinh Quinolone làm giảm hấp thu qua đường tiêu hóa tới 70-90%.',
            'recommendation' => 'Uống kháng sinh Quinolone trước ít nhất 2 giờ hoặc sau 4 giờ so với thuốc chứa canxi, sắt hoặc thuốc kháng acid dạ dày.',
        ],
        [
            'drug_a' => ['augmentin', 'amoxicillin'],
            'drug_b' => ['allopurinol'],
            'severity' => 'warning',
            'title' => 'Tăng nguy cơ dị ứng phát ban da nghiêm trọng',
            'description' => 'Dùng đồng thời Allopurinol và Amoxicillin làm tăng đáng kể tỷ lệ phát ban dạng sởi và dị ứng da.',
            'recommendation' => 'Theo dõi sát các triệu chứng ngoài da. Ngừng thuốc và báo ngay cho bác sĩ nếu xuất hiện mẩn đỏ.',
        ],
        [
            'drug_a' => ['amlodipine', 'amlodipin'],
            'drug_b' => ['bisoprolol', 'metoprolol', 'atenolol', 'nebivolol'],
            'severity' => 'info',
            'title' => 'Hiệp đồng hạ huyết áp & chậm nhịp tim',
            'description' => 'Phối hợp thuốc chẹn kênh calci và thuốc chẹn beta giao cảm làm tăng cường hiệu quả hạ áp nhưng cần kiểm soát nhịp tim.',
            'recommendation' => 'Theo dõi mạch và huyết áp định kỳ hàng ngày, báo bác sĩ nếu nhịp tim dưới 55 lần/phút.',
        ],
    ];

    /**
     * Analyze an array of medication names for pairwise drug interactions.
     */
    public static function analyze(array $medicationNames): array
    {
        $alerts = [];
        $cleanNames = array_map(fn($n) => mb_strtolower(trim($n)), $medicationNames);
        $count = count($cleanNames);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $medA = $cleanNames[$i];
                $medB = $cleanNames[$j];

                foreach (self::$rules as $rule) {
                    $matchA = false;
                    $matchB = false;

                    foreach ($rule['drug_a'] as $keywordA) {
                        if (str_contains($medA, $keywordA)) {
                            $matchA = true;
                            break;
                        }
                    }

                    foreach ($rule['drug_b'] as $keywordB) {
                        if (str_contains($medB, $keywordB)) {
                            $matchB = true;
                            break;
                        }
                    }

                    // Check reverse match as well
                    if (!$matchA || !$matchB) {
                        $matchARev = false;
                        $matchBRev = false;
                        foreach ($rule['drug_a'] as $keywordA) {
                            if (str_contains($medB, $keywordA)) {
                                $matchARev = true;
                                break;
                            }
                        }
                        foreach ($rule['drug_b'] as $keywordB) {
                            if (str_contains($medA, $keywordB)) {
                                $matchBRev = true;
                                break;
                            }
                        }
                        if ($matchARev && $matchBRev) {
                            $matchA = true;
                            $matchB = true;
                        }
                    }

                    if ($matchA && $matchB) {
                        $alerts[] = [
                            'severity' => $rule['severity'],
                            'drug_pair' => $medicationNames[$i] . ' + ' . $medicationNames[$j],
                            'title' => $rule['title'],
                            'description' => $rule['description'],
                            'recommendation' => $rule['recommendation'],
                        ];
                    }
                }
            }
        }

        return $alerts;
    }
}
