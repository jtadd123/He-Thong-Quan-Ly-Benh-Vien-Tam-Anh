<?php

namespace App\Services;

use App\Models\EquipmentLoan;
use App\Models\MobilityEquipment;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class MobilityEquipmentService
{
    /**
     * Cho mượn phương tiện di chuyển / xe lăn tại sảnh
     */
    public function borrowEquipment(int $equipmentId, array $data, int $staffUserId): EquipmentLoan
    {
        return DB::transaction(function () use ($equipmentId, $data, $staffUserId) {
            $equipment = MobilityEquipment::findOrFail($equipmentId);

            if ($equipment->status !== MobilityEquipment::STATUS_AVAILABLE) {
                throw new Exception("Thiết bị {$equipment->device_code} hiện không sẵn sàng (Trạng thái: {$equipment->status})!");
            }

            $loan = EquipmentLoan::create([
                'loan_code' => EquipmentLoan::generateLoanCode(),
                'mobility_equipment_id' => $equipment->id,
                'patient_profile_id' => $data['patient_profile_id'] ?? null,
                'borrower_name' => $data['borrower_name'],
                'borrower_phone' => $data['borrower_phone'],
                'id_card_held' => $data['id_card_held'] ?? null,
                'destination_department' => $data['destination_department'] ?? 'Khoa Khám Bệnh',
                'borrowed_at' => Carbon::now(),
                'dispatched_by_user_id' => $staffUserId,
                'status' => EquipmentLoan::STATUS_BORROWED,
            ]);

            $equipment->update([
                'status' => MobilityEquipment::STATUS_IN_USE,
                'current_location' => $data['destination_department'] ?? $equipment->current_location,
            ]);

            return $loan;
        });
    }

    /**
     * Nhận trả phương tiện & đưa vào quy trình khử khuẩn
     */
    public function returnEquipment(EquipmentLoan $loan, ?string $notes, int $staffUserId): EquipmentLoan
    {
        return DB::transaction(function () use ($loan, $notes, $staffUserId) {
            $loan->update([
                'status' => EquipmentLoan::STATUS_RETURNED,
                'returned_at' => Carbon::now(),
                'received_by_user_id' => $staffUserId,
                'disinfection_notes' => $notes,
            ]);

            $equipment = $loan->equipment;
            $equipment->update([
                'status' => MobilityEquipment::STATUS_DISINFECTING,
                'current_location' => 'Khu Khử Khuẩn Thiết Bị Sảnh Tầng 1',
            ]);

            return $loan;
        });
    }

    /**
     * Ký xác nhận hoàn tất khử khuẩn chống nhiễm khuẩn bệnh viện (Infection Control Sign-Off)
     */
    public function completeDisinfection(MobilityEquipment $equipment, int $staffUserId): MobilityEquipment
    {
        $equipment->update([
            'status' => MobilityEquipment::STATUS_AVAILABLE,
            'last_disinfected_at' => Carbon::now(),
            'last_disinfected_by_user_id' => $staffUserId,
            'current_location' => 'Sảnh Tiếp Đón Tầng 1 (Sẵn Sàng Phục Vụ)',
        ]);

        return $equipment;
    }
}
