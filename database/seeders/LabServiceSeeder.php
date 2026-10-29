<?php

namespace Database\Seeders;

use App\Models\LabService;
use Illuminate\Database\Seeder;

class LabServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'code' => 'XN-CBC',
                'name' => 'Tổng phân tích tế bào máu ngoại vi (24 thông số CBC)',
                'category' => 'laboratory',
                'sample_type' => 'Máu toàn phần (Ống EDTA tím)',
                'normal_range' => 'RBC: 4.0-5.5 T/L; WBC: 4.0-10.0 G/L; PLT: 150-450 G/L',
                'unit' => 'G/L',
                'price' => 150000,
                'execution_room' => 'Khoa Xét Nghiệm - Phòng Huyết Học P.102',
                'panic_low' => 2.0, // WBC < 2.0 hoặc PLT < 20 là nguy cơ xuất huyết não/nhiễm trùng huyết
                'panic_high' => 30.0,
            ],
            [
                'code' => 'XN-GLU',
                'name' => 'Định lượng Glucose máu mao mạch/tĩnh mạch lúc đói',
                'category' => 'laboratory',
                'sample_type' => 'Huyết tương (Ống Fluoride xám)',
                'normal_range' => '3.9 - 6.4',
                'unit' => 'mmol/L',
                'price' => 70000,
                'execution_room' => 'Khoa Xét Nghiệm - Phòng Sinh Hóa P.103',
                'panic_low' => 2.8, // Hạ đường huyết hôn mê
                'panic_high' => 20.0, // Nhiễm toan ceton do đái tháo đường
            ],
            [
                'code' => 'XN-AST',
                'name' => 'Định lượng Men gan AST (SGOT)',
                'category' => 'laboratory',
                'sample_type' => 'Huyết thanh (Ống Serum đỏ/vàng)',
                'normal_range' => '0 - 40',
                'unit' => 'U/L',
                'price' => 75000,
                'execution_room' => 'Khoa Xét Nghiệm - Phòng Sinh Hóa P.103',
                'panic_low' => null,
                'panic_high' => 1000.0, // Hoại tử tế bào gan cấp
            ],
            [
                'code' => 'XN-ALT',
                'name' => 'Định lượng Men gan ALT (SGPT)',
                'category' => 'laboratory',
                'sample_type' => 'Huyết thanh (Ống Serum đỏ/vàng)',
                'normal_range' => '0 - 45',
                'unit' => 'U/L',
                'price' => 75000,
                'execution_room' => 'Khoa Xét Nghiệm - Phòng Sinh Hóa P.103',
                'panic_low' => null,
                'panic_high' => 1000.0,
            ],
            [
                'code' => 'XN-CREAT',
                'name' => 'Định lượng Creatinine máu & Đánh giá mức lọc cầu thận eGFR',
                'category' => 'laboratory',
                'sample_type' => 'Huyết thanh (Ống Serum đỏ/vàng)',
                'normal_range' => '53 - 106',
                'unit' => 'µmol/L',
                'price' => 85000,
                'execution_room' => 'Khoa Xét Nghiệm - Phòng Sinh Hóa P.103',
                'panic_low' => null,
                'panic_high' => 450.0, // Suy thận cấp đe dọa toan chuyển hóa
            ],
            [
                'code' => 'XN-KALI',
                'name' => 'Điện giải đồ máu (Kali K+)',
                'category' => 'laboratory',
                'sample_type' => 'Huyết thanh (Ống Serum đỏ/vàng)',
                'normal_range' => '3.5 - 5.0',
                'unit' => 'mmol/L',
                'price' => 120000,
                'execution_room' => 'Khoa Xét Nghiệm - Phòng Sinh Hóa P.103',
                'panic_low' => 2.5, // Rung thất / Ngừng tim do hạ kali
                'panic_high' => 6.5, // Ngừng tim kỳ tâm trương do tăng kali
            ],
            [
                'code' => 'CDHA-XQ-CHEST',
                'name' => 'Chụp X-quang tim phổi thẳng kỹ thuật số (CR/DR)',
                'category' => 'imaging',
                'sample_type' => null,
                'normal_range' => 'Tim phổi bình thường, không thấy thâm nhiễm tổn thương phế trường',
                'unit' => null,
                'price' => 220000,
                'execution_room' => 'Khoa Chẩn Đoán Hình Ảnh - Phòng X-Quang P.108',
                'panic_low' => null,
                'panic_high' => null,
            ],
            [
                'code' => 'CDHA-US-ABDOMEN',
                'name' => 'Siêu âm Doppler màu ổ bụng tổng quát cao cấp',
                'category' => 'imaging',
                'sample_type' => null,
                'normal_range' => 'Các tạng trong ổ bụng hình thái và kích thước bình thường, không dịch tự do',
                'unit' => null,
                'price' => 300000,
                'execution_room' => 'Khoa Chẩn Đoán Hình Ảnh - Phòng Siêu Âm 01 P.204',
                'panic_low' => null,
                'panic_high' => null,
            ],
            [
                'code' => 'TDCN-ECG',
                'name' => 'Ghi điện tâm đồ 12 chuyển đạo (ECG lúc nghỉ)',
                'category' => 'functional',
                'sample_type' => null,
                'normal_range' => 'Nhịp xoang đều, tần số 60-90 ck/phút, không rối loạn dẫn truyền hay thiếu máu cơ tim',
                'unit' => 'ck/phút',
                'price' => 130000,
                'execution_room' => 'Phòng Thăm Dò Chức Năng Tim Mạch P.106',
                'panic_low' => null,
                'panic_high' => null,
            ],
        ];

        foreach ($services as $srv) {
            LabService::updateOrCreate(
                ['code' => $srv['code']],
                $srv
            );
        }
    }
}
