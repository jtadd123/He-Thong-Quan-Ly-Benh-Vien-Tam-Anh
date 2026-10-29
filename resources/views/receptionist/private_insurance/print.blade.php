<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thư Bảo Lãnh Viện Phí - {{ $privateInsurance->gop_number }}</title>
    <style>
        @page { size: A4; margin: 15mm; }
        body { font-family: 'Times New Roman', serif; font-size: 14px; line-height: 1.5; color: #000; margin: 0; padding: 0; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
        .title { text-align: center; font-size: 18px; font-weight: bold; text-transform: uppercase; margin: 15px 0 5px 0; }
        .subtitle { text-align: center; font-size: 13px; font-style: italic; margin-bottom: 20px; }
        .table-data { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 15px; }
        .table-data th, .table-data td { border: 1px solid #333; padding: 8px; text-align: left; }
        .table-data th { background-color: #f2f2f2; }
        .footer { display: flex; justify-content: space-between; margin-top: 40px; text-align: center; }
        .footer-col { width: 45%; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="background: #e0f2fe; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #0284c7; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
            In Thư Bảo Lãnh Viện Phí GOP (A4)
        </button>
    </div>

    <div class="header">
        <div>
            <strong>BỆNH VIỆN ĐA KHOA TÂM ANH</strong><br>
            <small>Phòng Bảo Lãnh Viện Phí Tư Nhân & Quốc Tế (TPA Billing)</small><br>
            <small>Điện thoại: 1800 6858 - Email: tpa@tamanhhospital.vn</small>
        </div>
        <div style="text-align: right;">
            <strong>SỐ GOP: {{ $privateInsurance->gop_number }}</strong><br>
            <small>Hạng VIP: {{ strtoupper($privateInsurance->vip_level) }}</small>
        </div>
    </div>

    <div class="title">THƯ BẢO LÃNH THANH TOÁN VIỆN PHÍ TRỰC TIẾP</div>
    <div class="subtitle">(GUARANTEE OF PAYMENT - GOP CONFIRMATION)</div>

    <p>Kính gửi: <strong>Cán bộ Thanh toán Viện phí & Người bệnh {{ $privateInsurance->patientProfile->full_name }}</strong></p>

    <table class="table-data">
        <tr>
            <th width="35%">Họ và tên người bệnh:</th>
            <td><strong>{{ mb_strtoupper($privateInsurance->patientProfile->full_name) }}</strong></td>
        </tr>
        <tr>
            <th>Mã định danh bệnh nhân:</th>
            <td>{{ $privateInsurance->patientProfile->patient_code }}</td>
        </tr>
        <tr>
            <th>Đơn vị bảo hiểm / TPA:</th>
            <td><strong>{{ $privateInsurance->tpa_company }}</strong></td>
        </tr>
        <tr>
            <th>Số thẻ bảo hiểm:</th>
            <td><strong style="letter-spacing: 1px;">{{ $privateInsurance->card_number }}</strong></td>
        </tr>
        <tr>
            <th>Số tiền bảo hiểm chấp thuận bảo lãnh (GOP):</th>
            <td><strong style="color: green; font-size: 16px;">{{ number_format($privateInsurance->approved_amount, 0, ',', '.') }} VNĐ</strong></td>
        </tr>
        <tr>
            <th>Số tiền người bệnh tự chi trả (Đồng bảo hiểm/Vượt hạn mức):</th>
            <td>{{ number_format($privateInsurance->patient_copay_amount, 0, ',', '.') }} VNĐ</td>
        </tr>
        <tr>
            <th>Điều khoản loại trừ & Ghi chú:</th>
            <td>{{ $privateInsurance->remarks ?: 'Áp dụng theo hợp đồng bảo hiểm sức khỏe hiện hành.' }}</td>
        </tr>
    </table>

    <div class="footer">
        <div class="footer-col">
            <strong>NGƯỜI BỆNH / ĐẠI DIỆN BẢO HIỂM</strong><br>
            <small>(Ký và ghi rõ họ tên)</small>
        </div>
        <div class="footer-col">
            <em>Hà Nội, Ngày {{ date('d') }} tháng {{ date('m') }} năm {{ date('Y') }}</em><br>
            <strong>ĐẠI DIỆN BỆNH VIỆN ĐA KHOA TÂM ANH</strong><br>
            <small>(Ký và đóng dấu)</small><br><br><br>
            <strong>{{ $privateInsurance->handledBy?->name ?? 'Cán bộ Tiếp nhận GOP' }}</strong>
        </div>
    </div>
</body>
</html>
