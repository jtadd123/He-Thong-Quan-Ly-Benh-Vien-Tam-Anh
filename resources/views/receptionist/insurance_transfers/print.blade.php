<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phiếu Tiếp Nhận Giấy Chuyển Tuyến BHYT - {{ $insuranceTransfer->transfer_code }}</title>
    <style>
        @page { size: A5 landscape; margin: 12mm; }
        body { font-family: 'Times New Roman', serif; font-size: 13px; line-height: 1.4; color: #000; margin: 0; padding: 0; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
        .title { text-align: center; text-transform: uppercase; font-size: 16px; font-weight: bold; margin: 10px 0; }
        .subtitle { text-align: center; font-style: italic; font-size: 11px; margin-bottom: 15px; }
        .row { display: flex; margin-bottom: 6px; }
        .label { font-weight: bold; width: 180px; }
        .val { flex: 1; }
        .barcode { text-align: right; font-family: monospace; font-size: 14px; font-weight: bold; }
        .box { border: 1px solid #333; padding: 8px; border-radius: 4px; margin: 10px 0; background-color: #fafafa; }
        .footer { display: flex; justify-content: space-between; margin-top: 30px; text-align: center; }
        .footer-col { width: 45%; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="background: #e0f2fe; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #0284c7; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
            In Phiếu Xác Nhận Chuyển Tuyến BHYT (A5)
        </button>
    </div>

    <div class="header">
        <div>
            <strong>BỆNH VIỆN ĐA KHOA TÂM ANH</strong><br>
            <small>Khoa Khám Bệnh - Phòng Giám Định BHYT</small><br>
            <small>Hotline: 1800 6858 - tamanhhospital.vn</small>
        </div>
        <div class="barcode">
            MÃ GIẤY: {{ $insuranceTransfer->transfer_code }}<br>
            <small style="font-weight: normal;">Mẫu số: 40/BYT</small>
        </div>
    </div>

    <div class="title">PHIẾU TIẾP NHẬN GIẤY CHUYỂN TUYẾN BẢO HIỂM Y TẾ</div>
    <div class="subtitle">(Dùng cho đối soát giám định chi phí KCB BHYT ngoại trú & nội trú)</div>

    <div class="row">
        <div class="label">Họ và tên người bệnh:</div>
        <div class="val"><strong>{{ mb_strtoupper($insuranceTransfer->patientProfile->full_name) }}</strong></div>
        <div class="label" style="width: 120px;">Mã bệnh nhân:</div>
        <div class="val" style="width: 100px;"><strong>{{ $insuranceTransfer->patientProfile->patient_code }}</strong></div>
    </div>

    <div class="row">
        <div class="label">Số thẻ BHYT:</div>
        <div class="val"><strong style="letter-spacing: 1px;">{{ $insuranceTransfer->patientProfile->insurance_code }}</strong></div>
        <div class="label" style="width: 120px;">Mức hưởng BHYT:</div>
        <div class="val" style="width: 100px;"><strong>{{ $insuranceTransfer->bhyt_reimbursement_rate }}% (ĐÚNG TUYẾN)</strong></div>
    </div>

    <div class="row">
        <div class="label">Cơ sở KCB chuyển đến:</div>
        <div class="val">{{ $insuranceTransfer->issuing_hospital_name }} (Mã: {{ $insuranceTransfer->issuing_hospital_code }})</div>
    </div>

    <div class="row">
        <div class="label">Chẩn đoán nơi chuyển:</div>
        <div class="val">{{ $insuranceTransfer->icd10_diagnosis }} (Mã ICD-10: {{ $insuranceTransfer->icd10_code ?? '---' }})</div>
    </div>

    <div class="box">
        <strong>Thời hạn giá trị giấy chuyển viện:</strong> Từ ngày {{ $insuranceTransfer->valid_from->format('d/m/Y') }} đến hết ngày {{ $insuranceTransfer->valid_to->format('d/m/Y') }}.<br>
        <strong>Phân loại lý do:</strong> {{ $insuranceTransfer->transfer_type === 'right_route' ? 'Vượt khả năng chuyên môn kỹ thuật của tuyến dưới' : 'Chuyển viện cấp cứu ngoại viện' }}.
    </div>

    <div class="footer">
        <div class="footer-col">
            <strong>NGƯỜI BỆNH / ĐẠI DIỆN THÂN NHÂN</strong><br>
            <small>(Ký và ghi rõ họ tên)</small>
        </div>
        <div class="footer-col">
            <em>Ngày {{ date('d') }} tháng {{ date('m') }} năm {{ date('Y') }}</em><br>
            <strong>CÁN BỘ TIẾP NHẬN BHYT TÂM ANH</strong><br>
            <small>(Ký, đóng dấu xác nhận)</small><br><br><br>
            <strong>{{ $insuranceTransfer->registeredBy?->name ?? 'Cán bộ Tiếp nhận' }}</strong>
        </div>
    </div>
</body>
</html>
