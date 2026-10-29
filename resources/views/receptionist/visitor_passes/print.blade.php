<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thẻ Đeo Khách Thăm Bệnh - {{ $visitorPass->pass_code }}</title>
    <style>
        @page { size: 90mm 130mm; margin: 5mm; }
        body { font-family: 'Arial', sans-serif; text-align: center; margin: 0; padding: 10px; color: #000; }
        .badge-card { border: 2px solid #0284c7; border-radius: 8px; padding: 12px; background: #fff; }
        .logo { font-size: 14px; font-weight: bold; color: #0284c7; margin-bottom: 5px; }
        .title { font-size: 16px; font-weight: bold; background: #0284c7; color: #fff; padding: 6px; border-radius: 4px; margin-bottom: 12px; text-transform: uppercase; }
        .qr-placeholder { margin: 15px auto; width: 100px; height: 100px; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; }
        .visitor-name { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
        .info-row { font-size: 12px; margin-bottom: 4px; text-align: left; }
        .time-box { background: #fef3c7; border: 1px solid #f59e0b; padding: 6px; border-radius: 4px; margin-top: 10px; font-weight: bold; font-size: 12px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 10px;">
        <button onclick="window.print()" style="padding: 6px 12px; background: #0284c7; color: white; border: none; border-radius: 4px; cursor: pointer;">
            In Thẻ Đeo Ngực
        </button>
    </div>

    <div class="badge-card">
        <div class="logo">BỆNH VIỆN ĐA KHOA TÂM ANH</div>
        <div class="title">THẺ KHÁCH THĂM BỆNH</div>

        <div class="visitor-name">{{ mb_strtoupper($visitorPass->visitor_name) }}</div>
        <div style="font-size: 12px; color: #666; margin-bottom: 8px;">Mã Thẻ: <strong>{{ $visitorPass->pass_code }}</strong></div>

        <div class="qr-placeholder">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=95x95&data={{ urlencode($visitorPass->pass_code) }}" alt="QR" style="width: 95px; height: 95px;">
        </div>

        <div class="info-row"><strong>Thăm người bệnh:</strong> {{ $visitorPass->patientProfile->full_name }}</div>
        <div class="info-row"><strong>Khoa phòng:</strong> {{ $visitorPass->target_department }}</div>
        <div class="info-row"><strong>Mối quan hệ:</strong> {{ $visitorPass->relationship }}</div>

        <div class="time-box">
            HIỆU LỰC: {{ $visitorPass->valid_from->format('H:i') }} - {{ $visitorPass->valid_to->format('H:i d/m/Y') }}
        </div>

        <div style="font-size: 10px; color: #888; margin-top: 8px;">
            * Vui lòng đeo thẻ trước ngực trong suốt thời gian thăm bệnh và trả lại tại quầy tiếp nhận khi ra về.
        </div>
    </div>
</body>
</html>
