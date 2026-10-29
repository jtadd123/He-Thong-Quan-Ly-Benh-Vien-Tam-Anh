<?php

namespace Database\Seeders;

use App\Models\MobilityEquipment;
use Illuminate\Database\Seeder;

class MobilityEquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $devices = [
            [
                'device_code' => 'XL-001',
                'name' => 'Xe lăn cơ tiêu chuẩn Tâm Anh #01 (Có đệm chống loét)',
                'device_type' => 'wheelchair',
                'current_location' => 'Sảnh Tiếp Đón Tầng 1 (Cửa Số 01)',
                'status' => 'available',
                'last_disinfected_at' => now()->subHours(2),
                'notes' => 'Tải trọng tối đa 120kg, phanh tay hoạt động tốt',
            ],
            [
                'device_code' => 'XL-002',
                'name' => 'Xe lăn cơ tiêu chuẩn Tâm Anh #02',
                'device_type' => 'wheelchair',
                'current_location' => 'Sảnh Tiếp Đón Tầng 1 (Cửa Số 01)',
                'status' => 'available',
                'last_disinfected_at' => now()->subHours(1),
                'notes' => 'Khung nhôm hợp kim nhẹ',
            ],
            [
                'device_code' => 'XLE-001',
                'name' => 'Xe lăn điện thông minh điều khiển joystick #E1',
                'device_type' => 'electric_wheelchair',
                'current_location' => 'Khu Khám Bệnh Chuyên Khoa Tầng 2',
                'status' => 'available',
                'last_disinfected_at' => now()->subHours(3),
                'notes' => 'Pin 100%, dành cho người hạn chế vận động chi trên',
            ],
            [
                'device_code' => 'CG-001',
                'name' => 'Cáng đẩy cấp cứu đa năng có cọc truyền dịch #C1',
                'device_type' => 'stretcher',
                'current_location' => 'Sảnh Cấp Cứu 115 Tầng 1',
                'status' => 'available',
                'last_disinfected_at' => now()->subHours(4),
                'notes' => 'Hạ/nâng đầu 0-75 độ, có đai an toàn',
            ],
            [
                'device_code' => 'O2-001',
                'name' => 'Bình Oxy y tế di động mini 2L kèm đồng hồ lưu lượng',
                'device_type' => 'oxygen_tank',
                'current_location' => 'Trạm Điều Dưỡng Sảnh Tầng 1',
                'status' => 'available',
                'last_disinfected_at' => now()->subHours(2),
                'notes' => 'Áp suất bình 130 bar, sẵn sàng sử dụng',
            ],
        ];

        foreach ($devices as $dev) {
            MobilityEquipment::updateOrCreate(
                ['device_code' => $dev['device_code']],
                $dev
            );
        }
    }
}
