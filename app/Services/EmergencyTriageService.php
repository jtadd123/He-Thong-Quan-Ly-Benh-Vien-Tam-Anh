<?php

namespace App\Services;

use App\Models\PatientProfile;
use App\Models\PatientVital;
use Illuminate\Support\Str;

class EmergencyTriageService
{
    /**
     * Thang phân loại cấp cứu ESI 5 cấp độ chuẩn quốc tế (Emergency Severity Index).
     */
    public const ESI_LEVELS = [
        1 => [
            'level' => 1,
            'name' => 'Cấp 1 - Hồi Sức Khẩn Cấp (Resuscitation)',
            'short_name' => 'Cấp 1: Hồi Sức',
            'severity' => 'critical',
            'color' => 'danger',
            'badge_class' => 'bg-danger text-white animate-pulse',
            'max_wait_minutes' => 0,
            'target_wait_text' => 'XỬ TRÍ NGAY LẬP TỨC (0 phút)',
            'description' => 'Bệnh nhân nguy kịch, đe dọa tính mạng ngay trước mắt. Cần hồi sinh tim phổi hoặc can thiệp đường thở khẩn cấp.',
            'action_protocol' => 'Báo động đỏ nội viện, đưa thẳng vào phòng Hồi Sức Cấp Cứu, bác sĩ tiếp nhận ngay lập tức!',
        ],
        2 => [
            'level' => 2,
            'name' => 'Cấp 2 - Cấp Cứu / Nguy Cơ Cao (Emergent)',
            'short_name' => 'Cấp 2: Cấp Cứu',
            'severity' => 'danger',
            'color' => 'danger',
            'badge_class' => 'bg-danger text-white',
            'max_wait_minutes' => 10,
            'target_wait_text' => 'Ưu tiên tối đa trong 10 phút',
            'description' => 'Tình trạng nguy hiểm, có nguy cơ diễn tiến trụy mạch, đột quỵ, nhồi máu cơ tim hoặc sốt cao sơ sinh.',
            'action_protocol' => 'Xếp lên đầu hàng đợi phòng cấp cứu, đo ECG, thiết lập đường truyền tĩnh mạch và bác sĩ khám trong vòng 10 phút.',
        ],
        3 => [
            'level' => 3,
            'name' => 'Cấp 3 - Khẩn Cấp (Urgent)',
            'short_name' => 'Cấp 3: Khẩn Cấp',
            'severity' => 'warning',
            'color' => 'warning',
            'badge_class' => 'bg-warning text-dark border border-warning',
            'max_wait_minutes' => 30,
            'target_wait_text' => 'Khám trong vòng 30 phút',
            'description' => 'Bệnh nhân có sinh hiệu tạm ổn nhưng cần can thiệp nhiều nguồn lực cận lâm sàng (xét nghiệm máu, CT, Siêu âm...).',
            'action_protocol' => 'Bố trí vào phòng khám chuyên khoa ưu tiên, hoàn thiện chỉ định xét nghiệm trong 30 phút.',
        ],
        4 => [
            'level' => 4,
            'name' => 'Cấp 4 - Bán Khẩn (Less Urgent)',
            'short_name' => 'Cấp 4: Bán Khẩn',
            'severity' => 'info',
            'color' => 'info',
            'badge_class' => 'bg-info-subtle text-info border border-info',
            'max_wait_minutes' => 60,
            'target_wait_text' => 'Khám trong vòng 60 phút',
            'description' => 'Bệnh nhân cần 1 nguồn lực đơn giản (khâu vết thương nhỏ, chụp X-quang khớp, xét nghiệm nước tiểu).',
            'action_protocol' => 'Khám theo thứ tự tiếp nhận tiêu chuẩn tại phòng khám ngoại trú.',
        ],
        5 => [
            'level' => 5,
            'name' => 'Cấp 5 - Không Khẩn Cấp (Non-Urgent)',
            'short_name' => 'Cấp 5: Khám Thường',
            'severity' => 'secondary',
            'color' => 'secondary',
            'badge_class' => 'bg-light text-secondary border',
            'max_wait_minutes' => 120,
            'target_wait_text' => 'Khám thường trong vòng 120 phút',
            'description' => 'Khám sức khỏe, tái khám định kỳ, lấy thuốc mạn tính hoặc triệu chứng nhẹ không cần nguồn lực can thiệp gấp.',
            'action_protocol' => 'Tiếp nhận và khám theo số thứ tự hiển thị thông thường tại sảnh chờ.',
        ],
    ];

    /**
     * Tự động tính toán điểm phân loại ESI Triage dựa trên tiền sử, sinh hiệu MEWS và triệu chứng.
     *
     * @param PatientProfile|null $patient
     * @param string|null $symptoms
     * @param PatientVital|null $vital
     * @return array
     */
    public static function classify(?PatientProfile $patient, ?string $symptoms, ?PatientVital $vital = null): array
    {
        $rawSymptoms = Str::lower($symptoms ?? '');
        $mewsScore = $vital ? $vital->mews_score : 0;
        $ageMonths = ($patient && $patient->dob) ? (int) $patient->dob->diffInMonths(now()) : null;
        $ageYears = ($patient && $patient->dob) ? (int) $patient->dob->age : null;

        $criteria = [];
        $level = 5; // Mặc định không khẩn cấp

        // -------------------------------------------------------------
        // BƯỚC 1: Kiểm tra Tiêu chí Cấp 1 (Đe dọa tính mạng ngay tức khắc)
        // -------------------------------------------------------------
        $level1Keywords = ['hôn mê', 'hon me', 'ngưng thở', 'ngung tho', 'ngừng tim', 'ngung tim', 'sốc phản vệ', 'soc phan ve', 'co giật liên tục', 'tím tái toàn thân'];
        foreach ($level1Keywords as $kw) {
            if (str_contains($rawSymptoms, $kw)) {
                $criteria[] = "Triệu chứng nguy kịch: Chứa dấu hiệu '{$kw}'";
                $level = 1;
                break;
            }
        }

        if ($mewsScore >= 7) {
            $criteria[] = "Điểm MEWS = {$mewsScore} (Nguy cơ suy hô hấp / trụy tuần hoàn cực nặng)";
            $level = 1;
        }

        if ($level === 1) {
            return self::formatResult(1, $criteria);
        }

        // -------------------------------------------------------------
        // BƯỚC 2: Kiểm tra Tiêu chí Cấp 2 (Nguy cơ cao - Cần ưu tiên ngay)
        // -------------------------------------------------------------
        $level2Keywords = [
            'đau ngực dữ dội', 'dau nguc', 'khó thở nặng', 'kho tho', 'yếu liệt nửa người', 
            'yeu liet', 'tai biến', 'dot quy', 'đột quỵ', 'chấn thương sọ não', 'nôn ra máu', 'xuat huyet tieu hoa'
        ];
        foreach ($level2Keywords as $kw) {
            if (str_contains($rawSymptoms, $kw)) {
                $criteria[] = "Triệu chứng cảnh báo đỏ: '{$kw}'";
                $level = 2;
                break;
            }
        }

        if ($mewsScore >= 5) {
            $criteria[] = "Điểm MEWS = {$mewsScore} (Vượt ngưỡng cảnh báo đỏ lâm sàng)";
            $level = 2;
        }

        // Trẻ sơ sinh dưới 3 tháng tuổi có sốt >= 38.0°C là đối tượng cấp cứu nguy cơ nhiễm khuẩn huyết cao
        if ($ageMonths !== null && $ageMonths < 3 && ($vital && $vital->temperature >= 38.0 || str_contains($rawSymptoms, 'sốt') || str_contains($rawSymptoms, 'sot'))) {
            $criteria[] = "Trẻ sơ sinh < 3 tháng tuổi có sốt cao (Nguy cơ nhiễm trùng huyết sơ sinh)";
            $level = 2;
        }

        if ($level === 2) {
            return self::formatResult(2, $criteria);
        }

        // -------------------------------------------------------------
        // BƯỚC 3: Kiểm tra Tiêu chí Cấp 3 (Cần nhiều nguồn lực can thiệp)
        // -------------------------------------------------------------
        if ($mewsScore >= 3) {
            $criteria[] = "Điểm MEWS = {$mewsScore} (Cần theo dõi sát sinh hiệu)";
            $level = 3;
        }

        if ($ageYears !== null && $ageYears >= 80) {
            $criteria[] = "Bệnh nhân cao tuổi (>= 80 tuổi), thể trạng nhạy cảm";
            $level = min($level, 3);
        }

        $level3Keywords = ['sốt cao', 'gãy xương', 'đau bụng nhiều', 'nhiễm trùng', 'khó nuốt', 'chóng mặt nặng'];
        foreach ($level3Keywords as $kw) {
            if (str_contains($rawSymptoms, $kw)) {
                $criteria[] = "Triệu chứng cấp tính: '{$kw}' (Cần làm nhiều xét nghiệm)";
                $level = min($level, 3);
                break;
            }
        }

        if ($level === 3) {
            return self::formatResult(3, $criteria);
        }

        // -------------------------------------------------------------
        // BƯỚC 4: Kiểm tra Tiêu chí Cấp 4 (Cần 1 nguồn lực can thiệp)
        // -------------------------------------------------------------
        $level4Keywords = ['vết rách', 'khâu vết thương', 'trật khớp', 'đau cổ chân', 'xương khớp nhẹ', 'viêm tai'];
        foreach ($level4Keywords as $kw) {
            if (str_contains($rawSymptoms, $kw)) {
                $criteria[] = "Cần can thiệp 1 nguồn lực: '{$kw}'";
                $level = 4;
                break;
            }
        }

        if ($level === 4) {
            return self::formatResult(4, $criteria);
        }

        // -------------------------------------------------------------
        // BƯỚC 5: Mặc định Cấp 5 (Không khẩn cấp)
        // -------------------------------------------------------------
        $criteria[] = "Khám lâm sàng tiêu chuẩn, sinh hiệu bình thường, không ghi nhận dấu hiệu nguy cơ";
        return self::formatResult(5, $criteria);
    }

    /**
     * Format kết quả trả về kèm metadata hiển thị UI.
     */
    protected static function formatResult(int $level, array $criteria): array
    {
        $meta = self::ESI_LEVELS[$level] ?? self::ESI_LEVELS[5];

        return array_merge($meta, [
            'criteria' => $criteria,
            'criteria_summary' => implode(' &bull; ', $criteria),
            'is_urgent' => $level <= 2,
        ]);
    }

    /**
     * Tạo chuỗi HTML Badge hiển thị trên danh sách hàng đợi hoặc hồ sơ.
     */
    public static function renderBadge(int $level): string
    {
        $meta = self::ESI_LEVELS[$level] ?? self::ESI_LEVELS[5];
        $icon = match($level) {
            1 => 'bi-exclamation-triangle-fill animate-pulse',
            2 => 'bi-lightning-charge-fill',
            3 => 'bi-clock-fill',
            4 => 'bi-file-medical',
            default => 'bi-person-check',
        };

        return '<span class="badge ' . $meta['badge_class'] . ' px-2 py-1" title="' . e($meta['target_wait_text']) . '"><i class="bi ' . $icon . ' me-1"></i>' . e($meta['short_name']) . '</span>';
    }
}
