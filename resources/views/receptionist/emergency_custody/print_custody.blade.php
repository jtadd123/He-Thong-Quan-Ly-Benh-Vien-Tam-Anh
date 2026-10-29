<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Biên Bản Niêm Phong Tài Sản Cấp Cứu - {{ $custody->custody_code }}</title>
    <style>
        @page { size: A5; margin: 12mm; }
        body { font-family: 'Times New Roman', serif; font-size: 13px; line-height: 1.4; color: #000; margin: 0; padding: 0; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
        .title { text-align: center; font-size: 16px; font-weight: bold; text-transform: uppercase; margin: 10px 0; }
        .row { display: flex; margin-bottom: 6px; }
        .label { font-weight: bold; width: 150px; }
        .val { flex: 1; }
        .box { border: 1px dashed #333; padding: 10px; margin: 12px 0; background: #fafafa; }
        .footer { display: flex; justify-content: space-between; margin-top: 25px; text-align: center; }
        .footer-col { width: 30%; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="background: #e0f2fe; padding: 8px; margin-bottom: 12px; border-radius: 4px; text-align: center;">
        <button onclick="window.print()" style="padding: 6px 12px; background: #dc2626; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
            In Biên Bản Niêm Phong Tài Sản (A5)
        </button>
    </div>

    <div class="header">
        <div>
            <strong>BỆNH VIỆN ĐA KHOA TÂM ANH</strong><br>
            <small>Khoa Cấp Cứu - Đội Bảo Vệ An Ninh</small>
        </div>
        <div style="text-align: right;">
            <strong>MÃ BB: {{ $custody->custody_code }}</strong><br>
            <small>Túi: {{ $custody->bag_seal_number }}</small>
        </div>
    </div>

    <div class="title">BIÊN BẢN BÀN GIAO & NIÊM PHONG TÀI SẢN NGƯỜI BỆNH CẤP CỨU</div>

    <div class="row">
        <div class="label">Người bệnh cấp cứu:</div>
        <div class="val"><strong>{{ mb_strtoupper($custody->patientProfile->full_name) }}</strong> (Mã: {{ $custody->patientProfile->patient_code }})</div>
    </div>
    <div class="row">
        <div class="label">Thời điểm tiếp nhận:</div>
        <div class="val">{{ $custody->created_at->format('H:i d/m/Y') }}</div>
    </div>
    <div class="row">
        <div class="label">Mã niêm phong túi:</div>
        <div class="val"><strong>{{ $custody->bag_seal_number }}</strong></div>
    </div>

    <div class="box">
        <strong>DANH MỤC TÀI SẢN BÀN GIAO NIÊM PHONG:</strong><br>
        <p style="margin: 5px 0;">{{ $custody->item_description }}</p>
        @if($custody->cash_amount > 0)
            <strong>Tiền mặt kiểm đếm:</strong> {{ number_format($custody->cash_amount, 0, ',', '.') }} VNĐ
        @endif
    </div>

    <p style="font-size: 11px; font-style: italic;">Biên bản được lập thành 02 bản có giá trị pháp lý như nhau, một bản lưu tại Khoa Cấp Cứu, một bản dán cùng túi niêm phong tại két an toàn Bệnh viện.</p>

    <div class="footer">
        <div class="footer-col">
            <strong>ĐIỀU DƯỠNG CẤP CỨU</strong><br>
            <small>(Ký và ghi họ tên)</small><br><br><br>
            <strong>{{ $custody->nurse?->name }}</strong>
        </div>
        <div class="footer-col">
            <strong>NHÂN VIÊN AN NINH</strong><br>
            <small>(Ký và ghi họ tên)</small><br><br><br>
            <strong>{{ $custody->security?->name }}</strong>
        </div>
        <div class="footer-col">
            <strong>NGƯỜI NHẬN LẠI TÀI SẢN</strong><br>
            <small>(Ký khi nhận lại)</small><br><br><br>
            <strong>{{ $custody->return_recipient_name ?: '..........................' }}</strong>
        </div>
    </div>
</body>
</html>
