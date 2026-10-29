<?php

namespace App\Services;

class HealthInsuranceService
{
    /**
     * BHYT Object Codes Dictionary according to Vietnam Social Security (BHXH).
     */
    protected static array $objectCodes = [
        'CC' => ['name' => 'Người có công với cách mạng', 'default_rate' => 100],
        'TE' => ['name' => 'Trẻ em dưới 6 tuổi', 'default_rate' => 100],
        'QN' => ['name' => 'Sĩ quan, quân nhân chuyên nghiệp QĐND', 'default_rate' => 100],
        'CA' => ['name' => 'Sĩ quan, hạ sĩ quan CAND', 'default_rate' => 100],
        'CY' => ['name' => 'Người làm công tác cơ yếu', 'default_rate' => 100],
        'HT' => ['name' => 'Người đang hưởng lương hưu, trợ cấp mất sức', 'default_rate' => 95],
        'TC' => ['name' => 'Thân nhân người có công với cách mạng', 'default_rate' => 100],
        'CN' => ['name' => 'Người thuộc hộ nghèo', 'default_rate' => 100],
        'DN' => ['name' => 'Người lao động làm việc trong doanh nghiệp', 'default_rate' => 80],
        'HX' => ['name' => 'Người lao động làm việc trong hợp tác xã', 'default_rate' => 80],
        'NN' => ['name' => 'Người lao động trong cơ quan nhà nước, đơn vị sự nghiệp', 'default_rate' => 80],
        'TK' => ['name' => 'Người lao động làm việc tại tổ chức khác', 'default_rate' => 80],
        'HC' => ['name' => 'Cán bộ, công chức, viên chức', 'default_rate' => 80],
        'XK' => ['name' => 'Người hoạt động không chuyên trách cấp xã', 'default_rate' => 80],
        'TB' => ['name' => 'Người đang hưởng trợ cấp bảo hiểm xã hội hàng tháng', 'default_rate' => 95],
        'NO' => ['name' => 'Người đang hưởng trợ cấp tai nạn lao động, bệnh nghề nghiệp', 'default_rate' => 95],
        'CT' => ['name' => 'Người từ đủ 80 tuổi trở lên đang hưởng trợ cấp tuất', 'default_rate' => 100],
        'XB' => ['name' => 'Cán bộ xã, phường, thị trấn đã nghỉ việc', 'default_rate' => 95],
        'TN' => ['name' => 'Người đang hưởng trợ cấp thất nghiệp', 'default_rate' => 80],
        'CS' => ['name' => 'Công nhân cao su nghỉ việc', 'default_rate' => 95],
        'CH' => ['name' => 'Người thuộc diện hưởng trợ cấp bảo trợ xã hội hàng tháng', 'default_rate' => 100],
        'CB' => ['name' => 'Cựu chiến binh', 'default_rate' => 100],
        'KC' => ['name' => 'Người tham gia kháng chiến', 'default_rate' => 100],
        'HN' => ['name' => 'Người thuộc hộ gia đình nghèo', 'default_rate' => 100],
        'DT' => ['name' => 'Người dân tộc thiểu số sinh sống tại vùng khó khăn', 'default_rate' => 100],
        'DK' => ['name' => 'Người sinh sống tại vùng có điều kiện kinh tế đặc biệt khó khăn', 'default_rate' => 100],
        'XD' => ['name' => 'Người sinh sống tại xã đảo, huyện đảo', 'default_rate' => 100],
        'BT' => ['name' => 'Người thuộc diện bảo trợ xã hội', 'default_rate' => 100],
        'TS' => ['name' => 'Thân nhân sĩ quan, hạ sĩ quan QĐND', 'default_rate' => 100],
        'TC' => ['name' => 'Thân nhân sĩ quan, hạ sĩ quan CAND', 'default_rate' => 100],
        'TQ' => ['name' => 'Thân nhân người có công với cách mạng', 'default_rate' => 80],
        'TA' => ['name' => 'Thân nhân người có công (cha mẹ đẻ, vợ/chồng, con liệt sĩ)', 'default_rate' => 100],
        'TY' => ['name' => 'Thân nhân sĩ quan CAND', 'default_rate' => 100],
        'HG' => ['name' => 'Người thuộc hộ gia đình cận nghèo', 'default_rate' => 100],
        'HS' => ['name' => 'Học sinh đang theo học tại các cơ sở giáo dục', 'default_rate' => 80],
        'SV' => ['name' => 'Sinh viên đang theo học tại các trường đại học, cao đẳng', 'default_rate' => 80],
        'GB' => ['name' => 'Người thuộc hộ gia đình làm nông, lâm, ngư, diêm nghiệp', 'default_rate' => 80],
        'GD' => ['name' => 'Người tham gia BHYT theo hộ gia đình', 'default_rate' => 80],
    ];

    /**
     * Validate 15-character Vietnam Health Insurance Card Format.
     * Pattern: [2 letters Object Code][1 digit Benefit Level][2 digits Province Code][10 digits Social Security ID]
     */
    public static function validate(string $code): bool
    {
        $code = strtoupper(trim($code));
        return (bool) preg_match('/^[A-Z]{2}[1-5][0-9]{2}[0-9]{10}$/', $code);
    }

    /**
     * Parse BHYT components and calculate co-payment.
     */
    public static function calculate(string $code, float $totalFee, bool $isCorrectRoute = true): array
    {
        $code = strtoupper(trim($code));
        $isValid = self::validate($code);

        if (!$isValid) {
            return [
                'is_valid' => false,
                'code' => $code,
                'object_code' => null,
                'object_name' => 'Mã BHYT không hợp lệ hoặc sai định dạng Bộ Y Tế',
                'benefit_level' => 0,
                'benefit_rate_pct' => 0,
                'is_correct_route' => $isCorrectRoute,
                'route_text' => 'Không áp dụng',
                'total_fee' => $totalFee,
                'insurance_paid_amount' => 0.0,
                'patient_copay_amount' => $totalFee,
                'formatted_insurance_paid' => '0 đ',
                'formatted_patient_copay' => number_format($totalFee, 0, ',', '.') . ' đ',
            ];
        }

        $objCode = substr($code, 0, 2);
        $benefitDigit = (int) substr($code, 2, 1);
        $provinceCode = substr($code, 3, 2);
        $idPart = substr($code, 5, 10);

        $objInfo = self::$objectCodes[$objCode] ?? [
            'name' => "Đối tượng {$objCode}",
            'default_rate' => 80,
        ];

        // Benefit level mapping according to Article 22 Law on Health Insurance
        $baseRate = match ($benefitDigit) {
            1, 2, 5 => 100,
            3 => 95,
            4 => 80,
            default => $objInfo['default_rate'],
        };

        // Out-of-route (Trái tuyến) calculation:
        // Khám ngoại trú trái tuyến tại bệnh viện tuyến tỉnh/trung ương theo luật BHYT: quỹ BHYT chi trả 0% khám ngoại trú trái tuyến
        // Nếu đúng tuyến hoặc thông tuyến: hưởng đủ tỷ lệ cơ bản
        $actualRate = $isCorrectRoute ? $baseRate : 0;

        $insurancePaid = round($totalFee * ($actualRate / 100));
        $patientCopay = max(0.0, $totalFee - $insurancePaid);

        return [
            'is_valid' => true,
            'code' => $code,
            'object_code' => $objCode,
            'object_name' => $objInfo['name'],
            'province_code' => $provinceCode,
            'social_security_id' => $idPart,
            'benefit_level' => $benefitDigit,
            'benefit_rate_pct' => $actualRate,
            'is_correct_route' => $isCorrectRoute,
            'route_text' => $isCorrectRoute ? 'Đúng tuyến / Thông tuyến' : 'Trái tuyến (Không chi trả ngoại trú)',
            'total_fee' => $totalFee,
            'insurance_paid_amount' => $insurancePaid,
            'patient_copay_amount' => $patientCopay,
            'formatted_insurance_paid' => number_format($insurancePaid, 0, ',', '.') . ' đ',
            'formatted_patient_copay' => number_format($patientCopay, 0, ',', '.') . ' đ',
        ];
    }
}
