<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu Hướng Dẫn Thực Đơn Dinh Dưỡng - {{ $assessment->assessment_code }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            background: #cbd5e1;
            margin: 0;
            padding: 20px;
        }
        .plan-page {
            max-width: 210mm;
            min-height: 297mm;
            background: #fff;
            margin: 0 auto;
            padding: 15mm 15mm;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        @media print {
            body { background: white !important; padding: 0 !important; }
            .no-print { display: none !important; }
            .plan-page { box-shadow: none !important; padding: 10mm !important; margin: 0 !important; max-width: 100% !important; }
            @page { size: A4 portrait; margin: 10mm; }
        }
    </style>
</head>
<body>

    <div class="no-print text-center mb-3">
        <button onclick="window.print()" class="btn btn-success px-4 shadow-sm">
            🖨️ In Thực Đơn Hướng Dẫn (A4)
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary px-3 ms-2">
            Đóng
        </button>
    </div>

    <div class="plan-page">
        <!-- Header -->
        <table style="width: 100%; border-bottom: 2px solid #15803d; padding-bottom: 8px; margin-bottom: 12px;">
            <tr>
                <td style="width: 60%; vertical-align: top;">
                    <strong style="font-size: 13pt; color: #15803d; text-transform: uppercase;">BỆNH VIỆN ĐA KHOA TÂM ANH</strong><br>
                    <span style="font-size: 10.5pt; font-weight: bold;">KHOA DINH DƯỠNG LÂM SÀNG & TIẾT CHẾ</span><br>
                    <small style="font-size: 9pt;">108 Hoàng Như Tiếp, P. Bồ Đề, Q. Long Biên, TP. Hà Nội</small><br>
                    <small style="font-size: 9pt;">Tư vấn Dinh dưỡng: 1800 6858 • dinhduong@tamanhhospital.vn</small>
                </td>
                <td style="width: 40%; text-align: right; vertical-align: top;">
                    <strong style="font-size: 10pt;">MẪU: 08/BV-DD</strong><br>
                    <span style="font-size: 9pt;">Mã hồ sơ: <strong style="font-family: monospace;">{{ $assessment->assessment_code }}</strong></span><br>
                    <span style="font-size: 9pt;">Mã BN: <strong style="font-family: monospace;">{{ $assessment->patientProfile->patient_code }}</strong></span><br>
                    <span style="font-size: 9pt;">Ngày tư vấn: {{ $assessment->assessed_at->format('d/m/Y') }}</span>
                </td>
            </tr>
        </table>

        <!-- Title -->
        <div class="text-center my-3">
            <h4 style="font-weight: bold; margin-bottom: 2px; color: #15803d; text-transform: uppercase;">HƯỚNG DẪN THỰC ĐƠN DINH DƯỠNG ĐIỀU TRỊ</h4>
            <span style="font-size: 10.5pt; font-style: italic;">(Thiết lập cá thể hóa theo khuyến cáo Viện Dinh Dưỡng Quốc Gia)</span>
        </div>

        <!-- Patient Demographics & Anthropometrics -->
        <table style="width: 100%; font-size: 10.5pt; margin-bottom: 12px; border: 1px solid #cbd5e1; padding: 6px; background: #f8fafc;">
            <tr>
                <td style="width: 50%; padding: 3px 6px;">Họ tên: <strong style="text-transform: uppercase;">{{ $assessment->patientProfile->full_name }}</strong></td>
                <td style="width: 25%; padding: 3px 6px;">Năm sinh: <strong>{{ $assessment->patientProfile->dob ? \Carbon\Carbon::parse($assessment->patientProfile->dob)->format('Y') : '--' }}</strong></td>
                <td style="width: 25%; padding: 3px 6px;">Giới tính: <strong>{{ $assessment->patientProfile->gender === 'male' ? 'Nam' : 'Nữ' }}</strong></td>
            </tr>
            <tr>
                <td style="padding: 3px 6px;">Chiều cao: <strong>{{ $assessment->height_cm }} cm</strong> | Cân nặng: <strong>{{ $assessment->weight_kg }} kg</strong></td>
                <td style="padding: 3px 6px;">Chỉ số BMI: <strong style="color: #15803d;">{{ $assessment->bmi }}</strong></td>
                <td style="padding: 3px 6px;">Thể trạng: <strong>{{ $assessment->bmi_category }}</strong></td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 3px 6px;">Năng lượng khuyến nghị: <strong style="color: #15803d;">{{ number_format($assessment->tdee_kcal) }} kcal/ngày</strong></td>
                <td style="padding: 3px 6px;">Nhu cầu nước: <strong>{{ number_format($assessment->water_intake_ml) }} ml/ngày</strong></td>
            </tr>
        </table>

        <!-- Diet Regimen Title -->
        @if($assessment->dietRegimen)
            <div style="background: #ecfdf5; border-left: 4px solid #10b981; padding: 8px 12px; margin-bottom: 12px;">
                <strong style="color: #065f46; font-size: 11pt; text-transform: uppercase;">Phác đồ: {{ $assessment->dietRegimen->title }}</strong><br>
                <small style="color: #047857;">Mục tiêu trị liệu: {{ $assessment->dietRegimen->target_disease }}</small>
            </div>

            <!-- Food Recommendations 3 Boxes -->
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 10pt;">
                <tr>
                    <td style="width: 33.3%; vertical-align: top; border: 1px solid #10b981; padding: 8px; background: #f0fdf4;">
                        <strong style="color: #15803d; text-transform: uppercase;">1. THỰC PHẨM NÊN DÙNG:</strong>
                        <p style="margin-top: 5px; margin-bottom: 0;">{{ $assessment->dietRegimen->foods_recommended }}</p>
                    </td>
                    <td style="width: 33.3%; vertical-align: top; border: 1px solid #f59e0b; padding: 8px; background: #fffbeb;">
                        <strong style="color: #b45309; text-transform: uppercase;">2. CẦN HẠN CHẾ ĂN:</strong>
                        <p style="margin-top: 5px; margin-bottom: 0;">{{ $assessment->dietRegimen->foods_restricted }}</p>
                    </td>
                    <td style="width: 33.3%; vertical-align: top; border: 1px solid #ef4444; padding: 8px; background: #fef2f2;">
                        <strong style="color: #b91c1c; text-transform: uppercase;">3. TUYỆT ĐỐI KIÊNG:</strong>
                        <p style="margin-top: 5px; margin-bottom: 0;">{{ $assessment->dietRegimen->foods_avoided }}</p>
                    </td>
                </tr>
            </table>

            <!-- Sample Daily Meal Plan -->
            @if($assessment->dietRegimen->sample_meal_plan)
                <div style="font-weight: bold; font-size: 10.5pt; margin-bottom: 5px; color: #15803d;">GỢI Ý PHÂN BỔ THỰC ĐƠN 5 BỮA TRONG NGÀY:</div>
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 9.5pt; margin-bottom: 15px;">
                    <thead>
                        <tr style="background: #f1f5f9; text-align: center;">
                            <th style="border: 1px solid #000; padding: 5px; width: 16%;">Bữa Sáng (07:00)</th>
                            <th style="border: 1px solid #000; padding: 5px; width: 18%;">Phụ Sáng (09:30)</th>
                            <th style="border: 1px solid #000; padding: 5px; width: 26%;">Bữa Trưa (12:00)</th>
                            <th style="border: 1px solid #000; padding: 5px; width: 16%;">Phụ Chiều (15:30)</th>
                            <th style="border: 1px solid #000; padding: 5px; width: 24%;">Bữa Tối (18:30)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="border: 1px solid #000; padding: 5px; vertical-align: top;">{{ $assessment->dietRegimen->sample_meal_plan['sang'] ?? '--' }}</td>
                            <td style="border: 1px solid #000; padding: 5px; vertical-align: top;">{{ $assessment->dietRegimen->sample_meal_plan['phu_sang'] ?? '--' }}</td>
                            <td style="border: 1px solid #000; padding: 5px; vertical-align: top;">{{ $assessment->dietRegimen->sample_meal_plan['trua'] ?? '--' }}</td>
                            <td style="border: 1px solid #000; padding: 5px; vertical-align: top;">{{ $assessment->dietRegimen->sample_meal_plan['phu_chieu'] ?? '--' }}</td>
                            <td style="border: 1px solid #000; padding: 5px; vertical-align: top;">{{ $assessment->dietRegimen->sample_meal_plan['toi'] ?? '--' }}</td>
                        </tr>
                    </tbody>
                </table>
            @endif
        @endif

        <!-- Doctor's Instructions -->
        @if($assessment->custom_instructions)
            <div style="border: 1px dashed #15803d; padding: 8px; margin-bottom: 15px; font-size: 10pt; background: #f0fdf4;">
                <strong>LỜI DẶN CỦA BÁC SĨ / CHUYÊN GIA DINH DƯỠNG:</strong>
                <p style="margin-top: 3px; margin-bottom: 0;">{{ $assessment->custom_instructions }}</p>
            </div>
        @endif

        <!-- Signatures -->
        <table style="width: 100%; font-size: 10.5pt; margin-top: 25px;">
            <tr>
                <td style="width: 50%; text-align: center;">
                    <div style="font-weight: bold;">NGƯỜI BỆNH / THÂN NHÂN</div>
                    <div style="font-size: 9pt; font-style: italic;">(Ký cam kết tuân thủ chế độ ăn)</div>
                    <div style="height: 50px;"></div>
                    <span>{{ $assessment->patientProfile->full_name }}</span>
                </td>
                <td style="width: 50%; text-align: center;">
                    <div style="font-size: 9.5pt;">Hà Nội, ngày {{ now()->format('d/m/Y') }}</div>
                    <div style="font-weight: bold;">CHUYÊN GIA DINH DƯỠNG LÂM SÀNG</div>
                    <div style="font-size: 9pt; font-style: italic;">(Ký và ghi rõ họ tên)</div>
                    <div style="height: 50px;"></div>
                    <strong>BS. CKII. TRẦN THỊ HỒNG NHUNG</strong>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
