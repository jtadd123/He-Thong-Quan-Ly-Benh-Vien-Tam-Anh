<?php

namespace Database\Seeders;

use App\Models\BloodUnit;
use Illuminate\Database\Seeder;

class BloodUnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            [
                'unit_code' => 'BLD-202610-001',
                'blood_type_abo' => 'O',
                'blood_type_rh' => 'positive',
                'product_type' => 'prbc',
                'volume_ml' => 350,
                'collection_date' => now()->subDays(5),
                'expiry_date' => now()->addDays(30),
                'status' => 'in_stock',
                'storage_location' => 'Tủ bảo quản Ngân hàng Máu P.104 - Ngăn A1 (2-6°C)',
            ],
            [
                'unit_code' => 'BLD-202610-002',
                'blood_type_abo' => 'A',
                'blood_type_rh' => 'positive',
                'product_type' => 'prbc',
                'volume_ml' => 250,
                'collection_date' => now()->subDays(3),
                'expiry_date' => now()->addDays(32),
                'status' => 'in_stock',
                'storage_location' => 'Tủ bảo quản Ngân hàng Máu P.104 - Ngăn A2 (2-6°C)',
            ],
            [
                'unit_code' => 'BLD-202610-003',
                'blood_type_abo' => 'B',
                'blood_type_rh' => 'positive',
                'product_type' => 'prbc',
                'volume_ml' => 350,
                'collection_date' => now()->subDays(7),
                'expiry_date' => now()->addDays(28),
                'status' => 'in_stock',
                'storage_location' => 'Tủ bảo quản Ngân hàng Máu P.104 - Ngăn A3 (2-6°C)',
            ],
            [
                'unit_code' => 'BLD-202610-004',
                'blood_type_abo' => 'AB',
                'blood_type_rh' => 'positive',
                'product_type' => 'ffp',
                'volume_ml' => 250,
                'collection_date' => now()->subDays(10),
                'expiry_date' => now()->addDays(350),
                'status' => 'in_stock',
                'storage_location' => 'Tủ đông sâu Ngân hàng Máu P.104 - Ngăn D1 (-25°C)',
            ],
            [
                'unit_code' => 'BLD-202610-005',
                'blood_type_abo' => 'O',
                'blood_type_rh' => 'negative', // Máu hiếm O Rh(-)
                'product_type' => 'prbc',
                'volume_ml' => 250,
                'collection_date' => now()->subDays(2),
                'expiry_date' => now()->addDays(33),
                'status' => 'in_stock',
                'storage_location' => 'Tủ bảo quản Ngân hàng Máu P.104 - Ngăn VIP Máu Hiếm',
            ],
        ];

        foreach ($units as $u) {
            BloodUnit::updateOrCreate(
                ['unit_code' => $u['unit_code']],
                $u
            );
        }
    }
}
