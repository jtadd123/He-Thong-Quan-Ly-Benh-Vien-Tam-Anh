<?php

namespace Database\Seeders;

use App\Models\Vaccine;
use Illuminate\Database\Seeder;

class VaccineSeeder extends Seeder
{
    public function run(): void
    {
        $vaccines = [
            [
                'name' => 'Vaxigrip Tetra (Cúm Tứ Giá)',
                'code' => 'VAC-VAXIGRIP',
                'disease_prevented' => 'Phòng 4 chủng cúm mùa nguy hiểm (A/H1N1, A/H3N2, B/Yamagata, B/Victoria)',
                'manufacturer' => 'Sanofi Pasteur',
                'origin_country' => 'Pháp',
                'standard_dosage' => '0.5ml',
                'route' => 'Tiêm bắp (IM)',
                'total_doses' => 1,
                'interval_days_between_doses' => 365, // Tiêm nhắc lại hàng năm
                'storage_temp' => '2°C - 8°C',
                'price' => 350000,
            ],
            [
                'name' => 'Prevenar 13 (Phế Cầu 13 Chủng)',
                'code' => 'VAC-PREVENAR13',
                'disease_prevented' => 'Phòng viêm phổi, viêm màng não, nhiễm khuẩn huyết do 13 tuýp phế cầu khuẩn',
                'manufacturer' => 'Pfizer',
                'origin_country' => 'Mỹ',
                'standard_dosage' => '0.5ml',
                'route' => 'Tiêm bắp (IM)',
                'total_doses' => 4,
                'interval_days_between_doses' => 60, // Cách nhau 2 tháng
                'storage_temp' => '2°C - 8°C',
                'price' => 1290000,
            ],
            [
                'name' => 'Gardasil 9 (Ung Thư CTC & HPV 9 Chủng)',
                'code' => 'VAC-GARDASIL9',
                'disease_prevented' => 'Phòng ung thư cổ tử cung, âm hộ, sùi mào gà do 9 chủng HPV (6, 11, 16, 18, 31, 33, 45, 52, 58)',
                'manufacturer' => 'MSD',
                'origin_country' => 'Mỹ',
                'standard_dosage' => '0.5ml',
                'route' => 'Tiêm bắp (IM)',
                'total_doses' => 3,
                'interval_days_between_doses' => 60, // Phác đồ 0-2-6 tháng
                'storage_temp' => '2°C - 8°C',
                'price' => 2950000,
            ],
            [
                'name' => 'Engerix-B 20mcg (Viêm Gan B Người Lớn)',
                'code' => 'VAC-ENGERIXB',
                'disease_prevented' => 'Phòng viêm gan virus B và xơ gan, ung thư gan nguyên phát',
                'manufacturer' => 'GlaxoSmithKline (GSK)',
                'origin_country' => 'Bỉ',
                'standard_dosage' => '1.0ml',
                'route' => 'Tiêm bắp (IM)',
                'total_doses' => 3,
                'interval_days_between_doses' => 30, // Phác đồ 0-1-6 tháng
                'storage_temp' => '2°C - 8°C',
                'price' => 230000,
            ],
            [
                'name' => 'Varivax (Vắc-xin Thủy Đậu)',
                'code' => 'VAC-VARIVAX',
                'disease_prevented' => 'Phòng bệnh thủy đậu (trái rạ) và các biến chứng viêm phổi, nhiễm trùng huyết',
                'manufacturer' => 'MSD',
                'origin_country' => 'Mỹ',
                'standard_dosage' => '0.5ml',
                'route' => 'Tiêm dưới da (SC)',
                'total_doses' => 2,
                'interval_days_between_doses' => 90,
                'storage_temp' => '2°C - 8°C',
                'price' => 920000,
            ],
            [
                'name' => 'Verorab (Vắc-xin Phòng Dại Tế Bào Vero)',
                'code' => 'VAC-VERORAB',
                'disease_prevented' => 'Dự phòng và điều trị bệnh dại sau khi bị súc vật cắn',
                'manufacturer' => 'Sanofi Pasteur',
                'origin_country' => 'Pháp',
                'standard_dosage' => '0.5ml',
                'route' => 'Tiêm bắp (IM)',
                'total_doses' => 5,
                'interval_days_between_doses' => 3, // Ngày 0-3-7-14-28
                'storage_temp' => '2°C - 8°C',
                'price' => 380000,
            ],
        ];

        foreach ($vaccines as $vac) {
            Vaccine::firstOrCreate(
                ['code' => $vac['code']],
                $vac
            );
        }
    }
}
