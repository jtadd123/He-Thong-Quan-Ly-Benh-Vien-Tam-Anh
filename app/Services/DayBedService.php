<?php

namespace App\Services;

use App\Models\DayBed;
use App\Models\DayBedAdmission;
use App\Models\IvInfusion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DayBedService
{
    /**
     * Tiếp nhận bệnh nhân vào giường lưu bệnh ngoại trú
     */
    public function admitPatient(array $data): DayBedAdmission
    {
        return DB::transaction(function () use ($data) {
            $bed = DayBed::findOrFail($data['day_bed_id']);
            
            if ($bed->status === DayBed::STATUS_OCCUPIED) {
                throw new \InvalidArgumentException("Giường {$bed->bed_code} hiện đang có bệnh nhân lưu!");
            }

            $admittedAt = isset($data['admitted_at']) ? Carbon::parse($data['admitted_at']) : Carbon::now();
            $stayHours = isset($data['stay_hours']) ? (int) $data['stay_hours'] : 4;
            $expectedDischargeAt = (clone $admittedAt)->addHours($stayHours);

            $admission = DayBedAdmission::create([
                'admission_code' => DayBedAdmission::generateAdmissionCode(),
                'day_bed_id' => $bed->id,
                'patient_profile_id' => $data['patient_profile_id'],
                'appointment_id' => $data['appointment_id'] ?? null,
                'attending_doctor_id' => $data['attending_doctor_id'] ?? null,
                'attending_nurse_name' => $data['attending_nurse_name'] ?? auth()->user()->name,
                'admission_reason' => $data['admission_reason'],
                'admitted_at' => $admittedAt,
                'expected_discharge_at' => $expectedDischargeAt,
                'status' => DayBedAdmission::STATUS_ACTIVE,
            ]);

            // Cập nhật trạng thái giường sang Occupied
            $bed->update(['status' => DayBed::STATUS_OCCUPIED]);

            // Nếu có chỉ định truyền dịch kèm theo lúc tiếp nhận
            if (!empty($data['fluid_name'])) {
                $volume = (int) ($data['volume_ml'] ?? 500);
                $rate = (int) ($data['drip_rate_drops_per_min'] ?? 30);
                $durationMinutes = IvInfusion::calculateDurationMinutes($volume, $rate);

                IvInfusion::create([
                    'day_bed_admission_id' => $admission->id,
                    'fluid_name' => $data['fluid_name'],
                    'volume_ml' => $volume,
                    'drip_rate_drops_per_min' => $rate,
                    'started_at' => $admittedAt,
                    'estimated_finish_at' => (clone $admittedAt)->addMinutes($durationMinutes),
                    'status' => IvInfusion::STATUS_RUNNING,
                    'nurse_name' => $admission->attending_nurse_name,
                    'notes' => $data['infusion_notes'] ?? null,
                ]);
            }

            return $admission;
        });
    }

    /**
     * Bổ sung chỉ định truyền dịch tại giường
     */
    public function addIvInfusion(DayBedAdmission $admission, array $data): IvInfusion
    {
        $startedAt = isset($data['started_at']) ? Carbon::parse($data['started_at']) : Carbon::now();
        $volume = (int) ($data['volume_ml'] ?? 500);
        $rate = (int) ($data['drip_rate_drops_per_min'] ?? 30);
        $durationMinutes = IvInfusion::calculateDurationMinutes($volume, $rate);

        return IvInfusion::create([
            'day_bed_admission_id' => $admission->id,
            'fluid_name' => $data['fluid_name'],
            'volume_ml' => $volume,
            'drip_rate_drops_per_min' => $rate,
            'started_at' => $startedAt,
            'estimated_finish_at' => (clone $startedAt)->addMinutes($durationMinutes),
            'status' => IvInfusion::STATUS_RUNNING,
            'nurse_name' => $data['nurse_name'] ?? auth()->user()->name,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Xuất giường cho bệnh nhân (Hoàn tất theo dõi ngoại trú)
     */
    public function dischargePatient(DayBedAdmission $admission, ?string $dischargeNotes = null, string $nextBedStatus = DayBed::STATUS_CLEANING): void
    {
        DB::transaction(function () use ($admission, $dischargeNotes, $nextBedStatus) {
            $admission->update([
                'status' => DayBedAdmission::STATUS_DISCHARGED,
                'discharged_at' => Carbon::now(),
                'discharge_notes' => $dischargeNotes,
            ]);

            // Đánh dấu các chai dịch truyền chưa xong thành Completed
            $admission->ivInfusions()->where('status', IvInfusion::STATUS_RUNNING)->update([
                'status' => IvInfusion::STATUS_COMPLETED,
            ]);

            // Đổi trạng thái giường sang vệ sinh/khử khuẩn hoặc sẵn sàng
            $admission->dayBed->update(['status' => $nextBedStatus]);
        });
    }

    /**
     * Hoàn tất vệ sinh khử khuẩn giường -> Chuyển sang sẵn sàng tiếp nhận
     */
    public function finishCleaning(DayBed $bed): void
    {
        $bed->update(['status' => DayBed::STATUS_AVAILABLE]);
    }
}
