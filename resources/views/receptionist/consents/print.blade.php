<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bản Cam Đoan Chấp Nhận Phẫu Thuật / Thủ Thuật - {{ $consent->consent_code }} - BV Tâm Anh</title>

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Times New Roman', Times, serif;
            color: #000000;
        }

        .document-page {
            max-width: 800px;
            margin: 2rem auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 45px 50px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            line-height: 1.6;
            font-size: 14pt;
        }

        .doc-header-left {
            font-size: 11pt;
            line-height: 1.3;
        }

        .doc-header-right {
            font-size: 11pt;
            line-height: 1.3;
            text-align: center;
        }

        .doc-title {
            font-size: 16pt;
            font-weight: bold;
            text-align: center;
            margin: 25px 0 10px 0;
            text-transform: uppercase;
        }

        .doc-subtitle {
            font-size: 12pt;
            font-style: italic;
            text-align: center;
            margin-bottom: 20px;
        }

        .signature-box {
            min-height: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .signature-img {
            max-height: 80px;
            max-width: 180px;
        }

        .digital-stamp {
            border: 1px solid #005baa;
            background-color: #f0f7ff;
            color: #005baa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 8pt;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 5px;
        }

        @media print {
            body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .document-page {
                max-width: 100% !important;
                margin: 0 !important;
                border: none !important;
                box-shadow: none !important;
                padding: 15mm 20mm !important;
            }
        }
    </style>
</head>
<body>

    <!-- Print Action Bar -->
    <div class="no-print bg-white border-bottom py-2 px-3 sticky-top shadow-sm text-center">
        <div class="container d-flex justify-content-between align-items-center" style="max-width: 800px;">
            <a href="{{ route('reception.patients.show', $consent->patientProfile) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Quay lại hồ sơ bệnh nhân
            </a>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm fw-bold">
                    <i class="bi bi-printer-fill"></i> In Bản Cam Đoan (Ctrl + P)
                </button>
            </div>
        </div>
    </div>

    <!-- Legal Medical Document -->
    <div class="document-page">
        <!-- Header -->
        <div class="row align-items-start pb-2">
            <div class="col-6 doc-header-left">
                <strong>SỞ Y TẾ TP. HÀ NỘI</strong><br>
                <strong>BỆNH VIỆN ĐA KHOA TÂM ANH</strong><br>
                <span>Khoa: {{ $consent->department_name }}</span><br>
                <span class="text-muted" style="font-size: 9pt;">Số lưu trữ: {{ $consent->consent_code }}</span>
            </div>
            <div class="col-6 doc-header-right">
                <strong>CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</strong><br>
                <div style="font-weight: bold; text-decoration: underline;">Độc lập - Tự do - Hạnh phúc</div>
                <div class="text-muted" style="font-size: 10pt; margin-top: 4px;">Hà Nội, ngày {{ $consent->signed_at->format('d') }} tháng {{ $consent->signed_at->format('m') }} năm {{ $consent->signed_at->format('Y') }}</div>
            </div>
        </div>

        <!-- Document Title -->
        <div class="doc-title">GIẤY CAM ĐOAN CHẤP NHẬN PHẪU THUẬT, THỦ THUẬT VÀ GÂY MÊ HỒI SỨC</div>
        <div class="doc-subtitle">(Ban hành kèm theo Thông tư số 46/2018/TT-BYT của Bộ Y tế)</div>

        <!-- Demographics -->
        <div class="mb-3">
            <p class="mb-1">
                Tên tôi là (Người ký cam đoan): <strong class="text-uppercase">{{ $consent->signer_name }}</strong>
                &bull; Giới tính: <strong>{{ $consent->patientProfile->gender_name }}</strong>
            </p>
            <p class="mb-1">
                Số CCCD/Định danh: <strong>{{ $consent->signer_id_card ?: '---' }}</strong>
                &bull; Điện thoại liên hệ: <strong>{{ $consent->signer_phone ?: '---' }}</strong>
            </p>
            <p class="mb-1">
                Quan hệ với người bệnh: <strong>{{ $consent->signer_relationship_text }}</strong>
            </p>
            @if($consent->signer_relationship !== 'patient')
                <p class="mb-1">
                    Họ và tên người bệnh: <strong class="text-uppercase">{{ $consent->patientProfile->full_name }}</strong>
                    &bull; Mã BN: <strong>{{ $consent->patientProfile->patient_code }}</strong>
                    &bull; Năm sinh: <strong>{{ $consent->patientProfile->dob ? $consent->patientProfile->dob->format('Y') : '--' }}</strong>
                </p>
            @endif
            <p class="mb-1">
                Địa chỉ thường trú: <span>{{ $consent->patientProfile->address ?: 'Tại Hà Nội' }}</span>
            </p>
            <p class="mb-1">
                Được chỉ định thực hiện thủ thuật / phẫu thuật: <strong class="text-primary text-uppercase">{{ $consent->procedure_name }}</strong>
            </p>
            <p class="mb-1">
                Bác sĩ trực tiếp giải thích / thực hiện: <strong>{{ $consent->doctor ? $consent->doctor->full_name_with_title : 'Bác sĩ chuyên khoa phụ trách' }}</strong>
            </p>
        </div>

        <!-- Medical Explanations -->
        <div class="mb-3">
            <p class="fw-bold mb-1">1. Về tình trạng bệnh và các rủi ro đã được giải thích:</p>
            <p class="text-justify mb-2" style="text-indent: 20px;">
                {{ $consent->risks_explained }}
            </p>

            <p class="fw-bold mb-1">2. Lời cam kết của người bệnh / người đại diện:</p>
            <p class="text-justify mb-2" style="text-indent: 20px;">
                {{ $consent->patient_undertaking }}
            </p>
            <p class="text-justify mb-2" style="text-indent: 20px;">
                Tôi đã hiểu rõ mục đích, sự cần thiết của thủ thuật/phẫu thuật cũng như những rủi ro bất khả kháng có thể xảy ra ngoài ý muốn của y bác sĩ. Tôi hoàn toàn tự nguyện ký vào bản cam đoan này và không khiếu nại nếu rủi ro y khoa xảy ra đã được giải thích trước.
            </p>
        </div>

        <!-- Signatures Table -->
        <div class="row text-center mt-4 pt-2">
            <div class="col-6">
                <div class="fw-bold">BÁC SĨ GIẢI THÍCH / THỰC HIỆN</div>
                <div class="small text-muted" style="font-size: 10pt;">(Ký và ghi rõ họ tên)</div>
                <div class="signature-box">
                    <span class="text-muted" style="font-size: 11pt; font-style: italic;">Đã giải thích kỹ</span>
                </div>
                <div class="fw-bold text-dark">{{ $consent->doctor ? $consent->doctor->full_name_with_title : 'BS. Chuyên khoa' }}</div>
            </div>

            <div class="col-6">
                <div class="fw-bold">NGƯỜI BỆNH / ĐẠI DIỆN GIA ĐÌNH</div>
                <div class="small text-muted" style="font-size: 10pt;">(Ký số điện tử và ghi rõ họ tên)</div>
                <div class="signature-box flex-column">
                    <img src="{{ $consent->signature_data }}" alt="Chữ ký điện tử" class="signature-img">
                    <div class="digital-stamp">
                        <i class="bi bi-shield-check"></i> Chữ Ký Điện Tử Số Hóa Xác Thực: {{ $consent->signed_at->format('H:i:s d/m/Y') }}
                    </div>
                </div>
                <div class="fw-bold text-dark mt-1">{{ $consent->signer_name }}</div>
            </div>
        </div>

        <!-- Footer Notice -->
        <div class="text-center pt-4 mt-4 border-top text-muted" style="font-size: 9pt; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
            Mã văn bản số hóa: <code>{{ $consent->consent_code }}</code> &bull; Tiêu chuẩn an toàn phẫu thuật WHO & JCI &bull; Lưu trữ tại EMR Bệnh Viện Tâm Anh
        </div>
    </div>

</body>
</html>
