<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu Kết Quả Cận Lâm Sàng - {{ $labOrder->order_code }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            background: #cbd5e1;
            margin: 0;
            padding: 20px;
        }
        .report-page {
            max-width: 210mm;
            min-height: 297mm;
            background: #fff;
            margin: 0 auto;
            padding: 20mm 15mm;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        @media print {
            body { background: white !important; padding: 0 !important; }
            .no-print { display: none !important; }
            .report-page { box-shadow: none !important; padding: 10mm !important; margin: 0 !important; max-width: 100% !important; }
            @page { size: A4 portrait; margin: 10mm; }
        }
    </style>
</head>
<body>

    <div class="no-print text-center mb-3">
        <button onclick="window.print()" class="btn btn-primary px-4 shadow-sm">
            🖨️ In Phiếu Kết Quả (A4)
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary px-3 ms-2">
            Đóng
        </button>
    </div>

    <div class="report-page">
        <!-- Header -->
        <table style="width: 100%; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px;">
            <tr>
                <td style="width: 55%; vertical-align: top;">
                    <strong style="font-size: 13pt; color: #0284c7; text-transform: uppercase;">BỆNH VIỆN ĐA KHOA TÂM ANH</strong><br>
                    <span style="font-size: 10.5pt; font-weight: bold;">KHOA XÉT NGHIỆM & CHẨN ĐOÁN HÌNH ẢNH</span><br>
                    <small style="font-size: 9.5pt;">108 Hoàng Như Tiếp, P. Bồ Đề, Q. Long Biên, TP. Hà Nội</small><br>
                    <small style="font-size: 9.5pt;">Hotline CSKH: 1800 6858 • Website: tamanhhospital.vn</small>
                </td>
                <td style="width: 45%; text-align: right; vertical-align: top;">
                    <strong style="font-size: 10pt;">MẪU SỐ: 04/BV-XN</strong><br>
                    <span style="font-size: 9.5pt;">Mã phiếu: <strong style="font-family: monospace;">{{ $labOrder->order_code }}</strong></span><br>
                    <span style="font-size: 9.5pt;">Mã BN: <strong style="font-family: monospace;">{{ $labOrder->patientProfile->patient_code }}</strong></span><br>
                    <span style="font-size: 9.5pt;">Thời gian: {{ $labOrder->ordered_at->format('H:i d/m/Y') }}</span>
                </td>
            </tr>
        </table>

        <!-- Title -->
        <div class="text-center my-3">
            <h4 style="font-weight: bold; margin-bottom: 2px; text-transform: uppercase;">PHIẾU KẾT QUẢ CẬN LÂM SÀNG</h4>
            <span style="font-size: 10.5pt; font-style: italic;">(Bao gồm kết quả Xét nghiệm Huyết học - Sinh hóa & Chẩn đoán hình ảnh)</span>
        </div>

        <!-- Patient Demographics -->
        <table style="width: 100%; font-size: 11pt; margin-bottom: 15px; border-collapse: collapse;">
            <tr>
                <td style="width: 60%; padding: 4px 0;">Họ và tên người bệnh: <strong style="text-transform: uppercase; font-size: 11.5pt;">{{ $labOrder->patientProfile->full_name }}</strong></td>
                <td style="width: 25%; padding: 4px 0;">Năm sinh: <strong>{{ $labOrder->patientProfile->dob ? \Carbon\Carbon::parse($labOrder->patientProfile->dob)->format('Y') : 'Chưa rõ' }}</strong></td>
                <td style="width: 15%; padding: 4px 0;">Giới tính: <strong>{{ $labOrder->patientProfile->gender === 'male' ? 'Nam' : 'Nữ' }}</strong></td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 4px 0;">Địa chỉ: <strong>{{ $labOrder->patientProfile->address ?: 'TP. Hà Nội' }}</strong></td>
                <td style="padding: 4px 0;">SĐT: <strong>{{ $labOrder->patientProfile->phone }}</strong></td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 4px 0;">Chẩn đoán lâm sàng: <strong>{{ $labOrder->clinical_diagnosis }}</strong></td>
                <td style="padding: 4px 0;">Bác sĩ chỉ định: <strong>{{ $labOrder->doctor ? $labOrder->doctor->user->name : 'Ngoại trú' }}</strong></td>
            </tr>
        </table>

        <!-- Results Table -->
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 10.5pt; margin-bottom: 20px;">
            <thead>
                <tr style="background: #f1f5f9; text-align: center;">
                    <th style="border: 1px solid #000; padding: 6px; width: 6%;">STT</th>
                    <th style="border: 1px solid #000; padding: 6px; text-align: left; width: 38%;">Tên Xét Nghiệm / CĐHA</th>
                    <th style="border: 1px solid #000; padding: 6px; width: 18%;">Kết Quả</th>
                    <th style="border: 1px solid #000; padding: 6px; width: 12%;">Đơn Vị</th>
                    <th style="border: 1px solid #000; padding: 6px; width: 26%;">Khoảng Tham Chiếu</th>
                </tr>
            </thead>
            <tbody>
                @foreach($labOrder->items as $idx => $item)
                    <tr>
                        <td style="border: 1px solid #000; padding: 6px; text-align: center;">{{ $idx + 1 }}</td>
                        <td style="border: 1px solid #000; padding: 6px;">
                            <strong>{{ $item->labService->name }}</strong>
                            @if($item->technician_notes)
                                <div style="font-size: 9pt; color: #475569; font-style: italic;">• {{ $item->technician_notes }}</div>
                            @endif
                        </td>
                        <td style="border: 1px solid #000; padding: 6px; text-align: center;">
                            <strong style="font-size: 11pt; {{ $item->is_panic_alert ? 'color: red;' : '' }}">
                                {{ $item->result_value ?: 'Đang chờ' }}
                            </strong>
                            @if($item->is_panic_alert)
                                <span style="font-size: 8.5pt; color: red; display: block; font-weight: bold;">(NGUY HIỂM)</span>
                            @endif
                        </td>
                        <td style="border: 1px solid #000; padding: 6px; text-align: center;">{{ $item->labService->unit ?: '-' }}</td>
                        <td style="border: 1px solid #000; padding: 6px; text-align: center; font-size: 9.5pt;">
                            {{ $item->labService->normal_range ?: 'Bình thường' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary & Footer Signatures -->
        <table style="width: 100%; font-size: 11pt; margin-top: 30px;">
            <tr>
                <td style="width: 50%; text-align: center;">
                    <div style="font-style: italic; font-size: 10pt; color: #475569;">Phiếu trả kết quả điện tử có giá trị pháp lý</div>
                    <div style="margin-top: 10px; font-weight: bold;">KỸ THUẬT VIÊN XÉT NGHIỆM</div>
                    <div style="font-size: 9.5pt; font-style: italic;">(Ký và ghi rõ họ tên)</div>
                    <div style="height: 60px;"></div>
                    <span>{{ $labOrder->items->first()?->technician?->name ?? 'KTV. Khoa Xét Nghiệm' }}</span>
                </td>
                <td style="width: 50%; text-align: center;">
                    <div style="font-size: 10pt;">Hà Nội, ngày {{ now()->format('d') }} tháng {{ now()->format('m') }} năm {{ now()->format('Y') }}</div>
                    <div style="font-weight: bold; margin-top: 5px;">TRƯỞNG KHOA XÉT NGHIỆM / BÁC SĨ</div>
                    <div style="font-size: 9.5pt; font-style: italic;">(Ký số và đóng dấu)</div>
                    <div style="height: 60px;"></div>
                    <strong>BS. CKI. NGUYỄN VĂN AN</strong>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
