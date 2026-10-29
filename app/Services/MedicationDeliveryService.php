<?php

namespace App\Services;

use App\Models\MedicalRecord;
use App\Models\MedicationDelivery;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MedicationDeliveryService
{
    /**
     * Danh mục hoạt chất / thuốc cần bảo quản lạnh 2-8°C
     */
    protected const COLD_CHAIN_KEYWORDS = [
        'insulin', 'mixtard', 'novorapid', 'lantus', 'humalog', 'vắc xin', 'vaccine',
        'oxytocin', 'calcitonin', 'erythropoietin', 'interferon', 'tatanol'
    ];

    /**
     * Tạo đơn giao thuốc tận nhà cho bệnh nhân
     */
    public function createDeliveryOrder(array $data): MedicationDelivery
    {
        return DB::transaction(function () use ($data) {
            $record = MedicalRecord::with('prescriptions')->findOrFail($data['medical_record_id']);
            $patient = $record->patientProfile;

            // Kiểm tra xem đơn thuốc có loại nào bắt buộc bảo quản lạnh không
            $requiresColdChain = false;
            foreach ($record->prescriptions as $p) {
                $pName = mb_strtolower($p->medication_name, 'UTF-8');
                foreach (self::COLD_CHAIN_KEYWORDS as $keyword) {
                    if (str_contains($pName, $keyword)) {
                        $requiresColdChain = true;
                        break 2;
                    }
                }
            }

            if (isset($data['requires_cold_chain'])) {
                $requiresColdChain = (bool) $data['requires_cold_chain'];
            }

            $waybill = $data['waybill_number'] ?? ('VN-' . date('Ymd') . '-' . rand(10000, 99999));

            return MedicationDelivery::create([
                'tracking_code' => MedicationDelivery::generateTrackingCode(),
                'medical_record_id' => $record->id,
                'patient_profile_id' => $patient->id,
                'recipient_name' => $data['recipient_name'] ?? $patient->full_name,
                'recipient_phone' => $data['recipient_phone'] ?? $patient->phone,
                'delivery_address' => $data['delivery_address'] ?? ($patient->address ?: 'TP. Hà Nội'),
                'carrier_name' => $data['carrier_name'] ?? MedicationDelivery::CARRIER_INTERNAL,
                'waybill_number' => $waybill,
                'shipping_fee' => $data['shipping_fee'] ?? 30000,
                'requires_cold_chain' => $requiresColdChain,
                'status' => MedicationDelivery::STATUS_PENDING_PACKAGING,
                'pharmacist_notes' => $data['pharmacist_notes'] ?? null,
            ]);
        });
    }

    public function markPackaged(MedicationDelivery $delivery, ?string $pharmacistNotes = null): void
    {
        $delivery->update([
            'status' => MedicationDelivery::STATUS_PACKAGED,
            'pharmacist_notes' => $pharmacistNotes ?? $delivery->pharmacist_notes,
        ]);
    }

    public function markShipped(MedicationDelivery $delivery, ?string $waybillNumber = null): void
    {
        $delivery->update([
            'status' => MedicationDelivery::STATUS_IN_TRANSIT,
            'waybill_number' => $waybillNumber ?? $delivery->waybill_number,
            'shipped_at' => Carbon::now(),
        ]);
    }

    public function markDelivered(MedicationDelivery $delivery): void
    {
        $delivery->update([
            'status' => MedicationDelivery::STATUS_DELIVERED,
            'delivered_at' => Carbon::now(),
        ]);
    }
}
