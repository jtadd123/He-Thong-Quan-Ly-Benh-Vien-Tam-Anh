<?php

namespace App\Services;

use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabService as LabServiceModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LabOrderService
{
    /**
     * Tạo phiếu chỉ định Cận Lâm Sàng & Xét nghiệm
     */
    public function createOrder(array $data): LabOrder
    {
        return DB::transaction(function () use ($data) {
            $serviceIds = $data['service_ids'] ?? [];
            $services = LabServiceModel::whereIn('id', $serviceIds)->get();
            $totalAmount = $services->sum('price');

            $order = LabOrder::create([
                'order_code' => LabOrder::generateOrderCode(),
                'appointment_id' => $data['appointment_id'] ?? null,
                'patient_profile_id' => $data['patient_profile_id'],
                'doctor_id' => $data['doctor_id'] ?? null,
                'clinical_diagnosis' => $data['clinical_diagnosis'] ?? 'Khám và theo dõi cận lâm sàng',
                'status' => LabOrder::STATUS_PENDING,
                'total_amount' => $totalAmount,
                'ordered_at' => Carbon::now(),
            ]);

            foreach ($services as $srv) {
                LabOrderItem::create([
                    'lab_order_id' => $order->id,
                    'lab_service_id' => $srv->id,
                    'result_status' => LabOrderItem::RESULT_NORMAL,
                    'is_panic_alert' => false,
                ]);
            }

            return $order->load(['patientProfile', 'doctor.user', 'items.labService']);
        });
    }

    /**
     * Nhập kết quả xét nghiệm / CĐHA và phát hiện Giá Trị Nguy Hiểm (Critical Panic Values)
     */
    public function recordResult(
        LabOrderItem $item,
        string $resultValue,
        ?int $technicianUserId = null,
        ?string $notes = null,
        ?string $dicomUrl = null
    ): LabOrderItem {
        return DB::transaction(function () use ($item, $resultValue, $technicianUserId, $notes, $dicomUrl) {
            $service = $item->labService;

            // Thử trích xuất giá trị số
            $resultNumber = null;
            if (is_numeric(trim($resultValue))) {
                $resultNumber = (float) trim($resultValue);
            } elseif (preg_match('/^([0-9]+(?:\.[0-9]+)?)/', trim($resultValue), $matches)) {
                $resultNumber = (float) $matches[1];
            }

            // Phân tích trạng thái kết quả & Ngưỡng Panic Value
            $status = LabOrderItem::RESULT_NORMAL;
            $isPanic = false;

            if ($resultNumber !== null) {
                if ($service->panic_low !== null && $resultNumber < $service->panic_low) {
                    $status = LabOrderItem::RESULT_CRITICAL_PANIC;
                    $isPanic = true;
                } elseif ($service->panic_high !== null && $resultNumber > $service->panic_high) {
                    $status = LabOrderItem::RESULT_CRITICAL_PANIC;
                    $isPanic = true;
                }
            }

            $item->update([
                'result_value' => $resultValue,
                'result_number' => $resultNumber,
                'result_status' => $status,
                'technician_notes' => $notes,
                'dicom_image_url' => $dicomUrl,
                'is_panic_alert' => $isPanic,
                'panic_alerted_at' => $isPanic ? Carbon::now() : null,
                'technician_user_id' => $technicianUserId,
                'completed_at' => Carbon::now(),
            ]);

            // Cập nhật trạng thái phiếu chỉ định chung
            $order = $item->labOrder;
            $allCompleted = $order->items()->whereNull('completed_at')->count() === 0;

            if ($allCompleted) {
                $order->update([
                    'status' => LabOrder::STATUS_COMPLETED,
                    'completed_at' => Carbon::now(),
                ]);
            } else {
                $order->update(['status' => LabOrder::STATUS_PROCESSING]);
            }

            return $item;
        });
    }
}
