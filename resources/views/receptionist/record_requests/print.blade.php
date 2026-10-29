<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bản Tóm Tắt Hồ Sơ Bệnh Án - {{ $recordRequest->request_code }}</title>
    <style>
        @page { size: A4; margin: 15mm; }
        body { font-family: 'Times New Roman', serif; font-size: 14px; line-height: 1.5; color: #000; margin: 0; padding: 0; position: relative; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 15px; }
        .title { text-align: center; font-size: 18px; font-weight: bold; text-transform: uppercase; margin: 10px 0 5px 0; }
        .subtitle { text-align: center; font-size: 12px; font-style: italic; margin-bottom: 20px; }
        .watermark {
            position: fixed;
            top: 40%;
            left: 10%;
            width: 80%;
            text-align: center;
            font-size: 42px;
            color: rgba(2, 132, 199, 0.12);
            transform: rotate(-30deg);
            font-weight: bold;
            pointer-events: none;
            z-index: 1000;
            text-transform: uppercase;
            border: 5px dashed rgba(2, 132, 199, 0.12);
            padding: 20px;
        }
        .section-title { font-weight: bold; text-transform: uppercase; margin-top: 15px; margin-bottom: 5px; font-size: 14px; border-bottom: 1px solid #ddd; }
        .row { display: flex; margin-bottom: 6px; }
        .label { font-weight: bold; width: 180px; }
        .val { flex: 1; }
        .record-box { border: 1px solid #ccc; padding: 10px; margin-bottom: 12px; background: #fafafa; border-radius: 4px; }
        .footer { display: flex; justify-content: space-between; margin-top: 40px; text-align: center; }
        .footer-col { width: 45%; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="watermark">
        BẢN SAO BỆNH ÁN ĐIỆN TỬ<br>
        TÂM ANH SECURE ARCHIVE<br>
        <span style="font-size: 20px;">MÃ ĐƠN: {{ $recordRequest->request_code }}</span>
    </div>

    <div class="no-print" style="background: #e0f2fe; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #0284c7; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
            In Bản Tóm Tắt Hồ Sơ Bệnh Án Có Watermark (A4)
        </button>
    </div>

    <div class="header">
        <div>
            <strong>BỆNH VIỆN ĐA KHOA TÂM ANH</strong><br>
            <small>Phòng Kế Hoạch Tổng Hợp & Lưu Trữ Bệnh Án</small><br>
            <small>Hotline: 1800 6858 - info@tamanhhospital.vn</small>
        </div>
        <div style="text-align: right;">
            <strong>MÃ SAO LỤC: {{ $recordRequest->request_code }}</strong><br>
            <small>Ngày trích xuất: {{ date('d/m/Y H:i') }}</small>
        </div>
    </div>

    <div class="title">BẢN TÓM TẮT HỒ SƠ BỆNH ÁN</div>
    <div class="subtitle">(Cung cấp theo Điều 69 Luật Khám bệnh, chữa bệnh số 15/2023/QH15)</div>

    <div class="section-title">I. THÔNG TIN NGƯỜI BỆNH</div>
    <div class="row">
        <div class="label">Họ và tên:</div>
        <div class="val"><strong>{{ mb_strtoupper($recordRequest->patientProfile->full_name) }}</strong></div>
        <div class="label" style="width: 100px;">Mã BN:</div>
        <div class="val" style="width: 120px;"><strong>{{ $recordRequest->patientProfile->patient_code }}</strong></div>
    </div>
    <div class="row">
        <div class="label">Ngày tháng năm sinh:</div>
        <div class="val">{{ $recordRequest->patientProfile->formatted_dob }} ({{ $recordRequest->patientProfile->age_text }})</div>
        <div class="label" style="width: 100px;">Giới tính:</div>
        <div class="val" style="width: 120px;">{{ $recordRequest->patientProfile->gender_name }}</div>
    </div>
    <div class="row">
        <div class="label">Số CCCD / CMND:</div>
        <div class="val">{{ $recordRequest->patientProfile->identity_card ?: 'Chưa cập nhật' }}</div>
        <div class="label" style="width: 100px;">Mã thẻ BHYT:</div>
        <div class="val" style="width: 120px;">{{ $recordRequest->patientProfile->insurance_code ?: 'Không có' }}</div>
    </div>

    <div class="section-title">II. CĂN CỨ VÀ NGƯỜI NHẬN BẢN SAO</div>
    <div class="row">
        <div class="label">Họ tên người nhận:</div>
        <div class="val"><strong>{{ $recordRequest->requester_name }}</strong> (CCCD: {{ $recordRequest->requester_identity_card }})</div>
    </div>
    <div class="row">
        <div class="label">Tư cách pháp lý:</div>
        <div class="val">
            {{ match($recordRequest->requester_type) {
                'patient_self' => 'Người bệnh chính chủ',
                'guardian' => 'Người giám hộ / Thân nhân hợp pháp',
                'law_enforcement' => 'Cơ quan tiến hành tố tụng',
                default => 'Doanh nghiệp bảo hiểm có giấy ủy quyền'
            } }}
        </div>
    </div>
    <div class="row">
        <div class="label">Mục đích sử dụng:</div>
        <div class="val">{{ $recordRequest->purpose }}</div>
    </div>

    <div class="section-title">III. TÓM TẮT DIỄN BIẾN LÂM SÀNG & ĐIỀU TRỊ</div>
    @forelse($recordRequest->patientProfile->medicalRecords->take(3) as $mr)
        <div class="record-box">
            <div class="row">
                <div class="label" style="width: 140px;">Mã bệnh án:</div>
                <div class="val"><strong>{{ $mr->record_code }}</strong> (Ngày khám: {{ $mr->created_at->format('d/m/Y') }})</div>
            </div>
            <div class="row">
                <div class="label" style="width: 140px;">Bác sĩ điều trị:</div>
                <div class="val">{{ $mr->doctor?->user?->name ?? 'Bác sĩ chuyên khoa Tâm Anh' }}</div>
            </div>
            <div class="row">
                <div class="label" style="width: 140px;">Chẩn đoán chính:</div>
                <div class="val"><strong>{{ $mr->diagnosis ?: 'Đang theo dõi điều trị ngoại trú' }}</strong></div>
            </div>
            <div class="row">
                <div class="label" style="width: 140px;">Lời dặn & Hướng xử trí:</div>
                <div class="val">{{ $mr->treatment_plan ?: 'Điều trị nội khoa theo đơn thuốc chỉ định.' }}</div>
            </div>
        </div>
    @empty
        <p>Hồ sơ lưu trữ ghi nhận bệnh nhân chưa có bệnh án ngoại trú hoàn tất.</p>
    @endforelse

    <div class="footer">
        <div class="footer-col">
            <strong>NGƯỜI NHẬN BẢN SAO</strong><br>
            <small>(Ký và ghi rõ họ tên)</small><br><br><br>
            <strong>{{ $recordRequest->requester_name }}</strong>
        </div>
        <div class="footer-col">
            <em>Hà Nội, Ngày {{ date('d') }} tháng {{ date('m') }} năm {{ date('Y') }}</em><br>
            <strong>TRƯỞNG PHÒNG KẾ HOẠCH TỔNG HỢP</strong><br>
            <small>(Ký, đóng dấu số hóa bệnh viện)</small><br><br><br>
            <strong>{{ $recordRequest->approvedBy?->name ?? 'Lãnh đạo Phòng KHTH' }}</strong>
        </div>
    </div>
</body>
</html>
