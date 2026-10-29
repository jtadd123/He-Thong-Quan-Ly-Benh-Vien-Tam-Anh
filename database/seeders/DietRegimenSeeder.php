<?php

namespace Database\Seeders;

use App\Models\DietRegimen;
use Illuminate\Database\Seeder;

class DietRegimenSeeder extends Seeder
{
    public function run(): void
    {
        $regimens = [
            [
                'code' => 'DIET-DIABETES',
                'title' => 'Chế độ ăn cho người Đái tháo đường & Rối loạn dung nạp Glucose',
                'target_disease' => 'Đái tháo đường type 1, type 2, Đái tháo đường thai kỳ',
                'daily_calorie_target' => 1600,
                'macronutrient_ratio' => ['carb' => 50, 'protein' => 20, 'fat' => 30],
                'foods_recommended' => 'Gạo lứt, yến mạch, bánh mì nguyên cám, ức gà bỏ da, cá hồi, rau xanh lá đậm (cải bó xôi, súp lơ xanh), quả mọng ít ngọt (bưởi, dâu tây, ổi). Uống đủ nước.',
                'foods_restricted' => 'Hoa quả quá ngọt sấy khô (nhãn, vải, mít sấy), tinh bột tinh chế (cơm trắng, bánh phở trắng), dầu mỡ chiên xào nhiều lần.',
                'foods_avoided' => 'Đường kính, bánh kẹo ngọt, nước ngọt có ga, trà sữa, mật ong nguyên chất, chè ngọt đặc.',
                'sample_meal_plan' => [
                    'sang' => 'Bún gạo lứt ức gà nấm hương (350 kcal)',
                    'phu_sang' => '1/2 quả bưởi da xanh (60 kcal)',
                    'trua' => '1 chén cơm gạo lứt + 100g cá thu hấp gừng + 1 đĩa bông cải xanh luộc (550 kcal)',
                    'phu_chieu' => '1 hộp sữa chua không đường + 5 hạt óc chó (120 kcal)',
                    'toi' => '1 chén nhỏ cơm lứt + 100g đậu phụ nhồi thịt nạc hấp + canh rau mồng tơi (450 kcal)',
                ],
            ],
            [
                'code' => 'DIET-DASH-HYPERTENSION',
                'title' => 'Chế độ ăn DASH kiểm soát Tăng Huyết Áp & Phòng ngừa Đột quỵ',
                'target_disease' => 'Tăng huyết áp nguyên phát, Xơ vữa mạch máu, Bệnh mạch vành',
                'daily_calorie_target' => 1800,
                'macronutrient_ratio' => ['carb' => 55, 'protein' => 18, 'fat' => 27],
                'foods_recommended' => 'Thực phẩm giàu Kali, Canxi, Magie: Chuối, khoai lang nướng nguyên vỏ, cá hồi, hạt chia, sữa ít béo, rau dền, bí đỏ, dầu olive nguyên chất.',
                'foods_restricted' => 'Gia vị mặn (tối đa 1 muỗng cà phê muối < 5g/ngày), đồ hộp chế biến sẵn, xúc xích, phô mai mặn, lòng đỏ trứng.',
                'foods_avoided' => 'Mắm tôm, mắm tép, cá khô, dưa chua, cà muối, thịt hun khói, mì ăn liền, rượu bia, cà phê đậm đặc.',
                'sample_meal_plan' => [
                    'sang' => 'Cháo yến mạch hạt sen cá lóc ít muối (380 kcal)',
                    'phu_sang' => '1 quả chuối sứ chín + 1 ly sữa hạt hạnh nhân không đường (150 kcal)',
                    'trua' => 'Cơm gạo tám thơm + cá basa áp chảo sốt cà chua ít muối + canh bí đỏ thịt bằm (600 kcal)',
                    'phu_chieu' => '1 quả táo xanh giòn (80 kcal)',
                    'toi' => 'Cơm trắng + ức gà hấp lá chanh + đĩa rau lang luộc chấm nước tương tỏi loãng (500 kcal)',
                ],
            ],
            [
                'code' => 'DIET-GOUT',
                'title' => 'Chế độ ăn giảm Axit Uric máu & Kiểm soát Cơn Gout cấp',
                'target_disease' => 'Bệnh Gout cấp và mạn tính, Tăng acid uric máu không triệu chứng',
                'daily_calorie_target' => 1700,
                'macronutrient_ratio' => ['carb' => 60, 'protein' => 15, 'fat' => 25],
                'foods_recommended' => 'Sữa và chế phẩm sữa ít béo (hỗ trợ thải acid uric), trứng gà, đậu phụ, dưa chuột, cà chua, cherry (anh đào), cần tây tươi, uống từ 2.5 - 3.0 lít nước kiềm/ngày.',
                'foods_restricted' => 'Thịt bò, thịt bê, cá biển sâu, các loại hạt họ đậu nguyên hạt, măng tây, nấm rơm.',
                'foods_avoided' => 'Phủ tạng động vật (tim, gan, lòng, dồi, cật, óc), hải sản có vỏ (tôm, cua, ốc, sò, điệp), bia rượu, nước hầm xương đặc sánh.',
                'sample_meal_plan' => [
                    'sang' => 'Bánh mì sandwich phết bơ hạt đậu nành + 1 quả trứng ốp la + 1 ly nước ép dưa chuột cần tây (380 kcal)',
                    'phu_sang' => '1 đĩa cherry hoặc ổi giòn (70 kcal)',
                    'trua' => 'Cơm trắng + đậu hũ sốt nấm hương nhẹ + canh cải cúc thịt nạc dăm mỏng (580 kcal)',
                    'phu_chieu' => '1 ly sữa tách béo bổ sung vi chất (120 kcal)',
                    'toi' => 'Cơm + cá lóc kho nghệ + bầu luộc thanh mát (480 kcal)',
                ],
            ],
            [
                'code' => 'DIET-RENAL-PRE-DIALYSIS',
                'title' => 'Chế độ ăn cho người Suy Thận Mạn (Giai đoạn bảo tồn tiền lọc máu)',
                'target_disease' => 'Suy thận mạn độ 2, 3a, 3b chưa chỉ định chạy thận nhân tạo',
                'daily_calorie_target' => 1750,
                'macronutrient_ratio' => ['carb' => 65, 'protein' => 10, 'fat' => 25], // Giảm đạm Low-Protein
                'foods_recommended' => 'Đạm sinh học giá trị cao (trứng, sữa cho người suy thận Nepro), miến dong, khoai củ ít kali, sắn dây, dầu thực vật.',
                'foods_restricted' => 'Đạm từ thịt cá (giới hạn 0.6g/kg/ngày), các loại quả mọng nhiều kali (chuối, cam, nho, thanh long) nếu có ứ kali.',
                'foods_avoided' => 'Thực phẩm giàu phospho (lòng đỏ, ngũ cốc thô, phô mai), muối mặn, thực phẩm đóng hộp chứa phụ gia photphat.',
                'sample_meal_plan' => [
                    'sang' => 'Miến dong nấu sườn nạc lọc kỹ + hành hoa (350 kcal, đạm thấp)',
                    'phu_sang' => '1 ly sữa y tế Nepro 1 (150 kcal)',
                    'trua' => 'Cơm trắng + 50g thịt nạc rim nhạt + mướp hương xào dầu mè (550 kcal)',
                    'phu_chieu' => 'Bánh khoai mì hấp nước cốt dừa loãng (150 kcal)',
                    'toi' => 'Cơm trắng + 50g cá quả hấp thì là + bí xanh luộc (480 kcal)',
                ],
            ],
        ];

        foreach ($regimens as $reg) {
            DietRegimen::updateOrCreate(
                ['code' => $reg['code']],
                $reg
            );
        }
    }
}
