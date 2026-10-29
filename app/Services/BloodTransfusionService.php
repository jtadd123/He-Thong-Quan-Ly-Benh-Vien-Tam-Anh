<?php

namespace App\Services;

use App\Models\BloodTransfusion;
use App\Models\BloodUnit;
use App\Models\PatientProfile;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class BloodTransfusionService
{
    /**
     * Kiểm tra tính hòa hợp miễn dịch hệ nhóm máu ABO và Rh
     * @return array [compatible => bool, reason => string]
     */
    public function checkCompatibility(string $patientAbo, string $patientRh, BloodUnit $unit): array
    {
        $donorAbo = $unit->blood_type_abo;
        $donorRh = $unit->blood_type_rh;
        $product = $unit->product_type;

        // 1. Kiểm tra Rhesus
        if ($patientRh === 'negative' && $donorRh === 'positive') {
            return [
                'compatible' => false,
                'reason' => "BẤT ĐỒNG RHESUS NGUY HIỂM: Bệnh nhân nhóm máu Rh(-) tuyệt đối KHÔNG ĐƯỢC truyền túi máu Rh(+) vì sinh kháng thể chống D gây tai biến ngưng kết tiêu huyết cấp!",
            ];
        }

        // 2. Kiểm tra ABO đối với Khối Hồng Cầu (PRBC) hoặc Khối Tiểu Cầu
        if ($product === BloodUnit::PRODUCT_PRBC || $product === BloodUnit::PRODUCT_PLATELET) {
            $isAboOk = match ($patientAbo) {
                'O' => $donorAbo === 'O',
                'A' => in_array($donorAbo, ['A', 'O'], true),
                'B' => in_array($donorAbo, ['B', 'O'], true),
                'AB' => in_array($donorAbo, ['A', 'B', 'AB', 'O'], true),
                default => false,
            };

            if (!$isAboOk) {
                return [
                    'compatible' => false,
                    'reason' => "BẤT ĐỒNG HỆ NHÓM MÁU ABO: Bệnh nhân nhóm {$patientAbo} không thể nhận khối hồng cầu nhóm {$donorAbo} (Nguy cơ ngưng kết kháng nguyên - kháng thể trong lòng mạch đe dọa tính mạng)!",
                ];
            }
        }

        // 3. Kiểm tra ABO đối với Huyết Tương Tươi (FFP) hoặc Tủa Lạnh (Cryo)
        if ($product === BloodUnit::PRODUCT_FFP || $product === BloodUnit::PRODUCT_CRYO) {
            $isFfpOk = match ($patientAbo) {
                'O' => true,
                'A' => in_array($donorAbo, ['A', 'AB'], true),
                'B' => in_array($donorAbo, ['B', 'AB'], true),
                'AB' => $donorAbo === 'AB',
                default => false,
            };

            if (!$isFfpOk) {
                return [
                    'compatible' => false,
                    'reason' => "BẤT ĐỒNG HUYẾT TƯƠNG ABO: Bệnh nhân nhóm {$patientAbo} không thể nhận huyết tương chứa kháng thể chống nhóm {$donorAbo}!",
                ];
            }
        }

        return [
            'compatible' => true,
            'reason' => "HÒA HỢP MIỄN DỊCH: Túi máu {$unit->full_group} hoàn toàn tương thích an toàn với người bệnh nhóm {$patientAbo} Rh(" . ($patientRh === 'positive' ? '+' : '-') . ").",
        ];
    }

    /**
     * Chuẩn bị ca truyền máu & Ký duyệt kép điều dưỡng (Dual Sign-Off)
     */
    public function prepareTransfusion(array $data): BloodTransfusion
    {
        return DB::transaction(function () use ($data) {
            $unit = BloodUnit::findOrFail($data['blood_unit_id']);

            if ($unit->status !== BloodUnit::STATUS_IN_STOCK) {
                throw new Exception("Túi máu {$unit->unit_code} hiện không có sẵn trong kho (Trạng thái: {$unit->status})!");
            }

            // Kiểm tra tương thích
            $check = $this->checkCompatibility($data['patient_abo'], $data['patient_rh'], $unit);

            if (!$check['compatible']) {
                throw new Exception($check['reason']);
            }

            // Đặt giữ túi máu
            $unit->update(['status' => BloodUnit::STATUS_RESERVED]);

            return BloodTransfusion::create([
                'transfusion_code' => BloodTransfusion::generateTransfusionCode(),
                'patient_profile_id' => $data['patient_profile_id'],
                'blood_unit_id' => $unit->id,
                'doctor_id' => $data['doctor_id'] ?? null,
                'indication' => $data['indication'],
                'patient_abo' => $data['patient_abo'],
                'patient_rh' => $data['patient_rh'],
                'is_crossmatch_compatible' => true,
                'crossmatch_notes' => $check['reason'],
                'nurse_user_id_1' => $data['nurse_user_id_1'],
                'nurse_user_id_2' => $data['nurse_user_id_2'],
                'vitals_baseline' => $data['vitals_baseline'] ?? null,
                'status' => BloodTransfusion::STATUS_PREPARED,
            ]);
        });
    }

    /**
     * Bắt đầu cắm kim truyền máu
     */
    public function startTransfusion(BloodTransfusion $transfusion): BloodTransfusion
    {
        return DB::transaction(function () use ($transfusion) {
            $transfusion->update([
                'status' => BloodTransfusion::STATUS_TRANSFUSING,
                'started_at' => Carbon::now(),
            ]);

            $transfusion->bloodUnit->update(['status' => BloodUnit::STATUS_TRANSFUSING]);

            return $transfusion;
        });
    }

    /**
     * Ghi nhận sinh hiệu giám sát tại phút 15 (thời điểm then chốt phát hiện phản ứng cấp)
     */
    public function record15MinVitals(BloodTransfusion $transfusion, array $vitals): BloodTransfusion
    {
        $transfusion->update([
            'vitals_15min' => $vitals,
        ]);

        return $transfusion;
    }

    /**
     * Hoàn tất ca truyền máu an toàn
     */
    public function completeTransfusion(BloodTransfusion $transfusion): BloodTransfusion
    {
        return DB::transaction(function () use ($transfusion) {
            $transfusion->update([
                'status' => BloodTransfusion::STATUS_COMPLETED,
                'completed_at' => Carbon::now(),
            ]);

            $transfusion->bloodUnit->update(['status' => BloodUnit::STATUS_COMPLETED]);

            return $transfusion;
        });
    }

    /**
     * Báo cáo tai biến / phản ứng truyền máu cấp
     */
    public function reportReaction(BloodTransfusion $transfusion, string $reactionDetails): BloodTransfusion
    {
        return DB::transaction(function () use ($transfusion, $reactionDetails) {
            $transfusion->update([
                'reaction_occurred' => true,
                'reaction_details' => $reactionDetails,
                'status' => BloodTransfusion::STATUS_ABORTED,
                'completed_at' => Carbon::now(),
            ]);

            $transfusion->bloodUnit->update(['status' => BloodUnit::STATUS_DISCARDED]);

            return $transfusion;
        });
    }
}
