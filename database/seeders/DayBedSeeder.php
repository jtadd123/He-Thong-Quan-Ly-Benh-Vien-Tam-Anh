<?php

namespace Database\Seeders;

use App\Models\DayBed;
use Illuminate\Database\Seeder;

class DayBedSeeder extends Seeder
{
    public function run(): void
    {
        $beds = [
            // Phòng Lưu Ban Ngày 101 (Phòng đa khoa lưu bệnh ngắn hạn)
            ['room_name' => 'Phòng Lưu Ban Ngày 101', 'bed_code' => 'LBN101-01', 'bed_type' => DayBed::TYPE_GENERAL, 'status' => DayBed::STATUS_AVAILABLE, 'hourly_rate' => 50000],
            ['room_name' => 'Phòng Lưu Ban Ngày 101', 'bed_code' => 'LBN101-02', 'bed_type' => DayBed::TYPE_GENERAL, 'status' => DayBed::STATUS_AVAILABLE, 'hourly_rate' => 50000],
            ['room_name' => 'Phòng Lưu Ban Ngày 101', 'bed_code' => 'LBN101-03', 'bed_type' => DayBed::TYPE_GENERAL, 'status' => DayBed::STATUS_AVAILABLE, 'hourly_rate' => 50000],
            ['room_name' => 'Phòng Lưu Ban Ngày 101', 'bed_code' => 'LBN101-04', 'bed_type' => DayBed::TYPE_GENERAL, 'status' => DayBed::STATUS_CLEANING, 'hourly_rate' => 50000],

            // Phòng Truyền Dịch Ngoại Trú (Khu truyền tĩnh mạch)
            ['room_name' => 'Khu Truyền Dịch Ngoại Trú', 'bed_code' => 'TD-01', 'bed_type' => DayBed::TYPE_INFUSION, 'status' => DayBed::STATUS_AVAILABLE, 'hourly_rate' => 40000],
            ['room_name' => 'Khu Truyền Dịch Ngoại Trú', 'bed_code' => 'TD-02', 'bed_type' => DayBed::TYPE_INFUSION, 'status' => DayBed::STATUS_AVAILABLE, 'hourly_rate' => 40000],
            ['room_name' => 'Khu Truyền Dịch Ngoại Trú', 'bed_code' => 'TD-03', 'bed_type' => DayBed::TYPE_INFUSION, 'status' => DayBed::STATUS_AVAILABLE, 'hourly_rate' => 40000],

            // Phòng Hồi Tỉnh Nội Soi & Tiểu Phẫu
            ['room_name' => 'Phòng Hồi Tỉnh Sau Nội Soi', 'bed_code' => 'HTNS-01', 'bed_type' => DayBed::TYPE_RECOVERY, 'status' => DayBed::STATUS_AVAILABLE, 'hourly_rate' => 80000],
            ['room_name' => 'Phòng Hồi Tỉnh Sau Nội Soi', 'bed_code' => 'HTNS-02', 'bed_type' => DayBed::TYPE_RECOVERY, 'status' => DayBed::STATUS_AVAILABLE, 'hourly_rate' => 80000],

            // Khu Cấp Cứu Lưu & Theo Dõi Triage
            ['room_name' => 'Khu Theo Dõi Cấp Cứu Lưu', 'bed_code' => 'CC-LBN-01', 'bed_type' => DayBed::TYPE_OBSERVATION, 'status' => DayBed::STATUS_AVAILABLE, 'hourly_rate' => 100000],
            ['room_name' => 'Khu Theo Dõi Cấp Cứu Lưu', 'bed_code' => 'CC-LBN-02', 'bed_type' => DayBed::TYPE_OBSERVATION, 'status' => DayBed::STATUS_AVAILABLE, 'hourly_rate' => 100000],
        ];

        foreach ($beds as $bedData) {
            DayBed::firstOrCreate(
                ['bed_code' => $bedData['bed_code']],
                $bedData
            );
        }
    }
}
