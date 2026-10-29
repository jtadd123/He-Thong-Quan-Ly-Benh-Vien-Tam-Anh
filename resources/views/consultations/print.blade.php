<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biên Bản Hội Chẩn - {{ $consultation->consultation_code }} - {{ $consultation->patientProfile->full_name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background: #f1f5f9;
            padding: 20px;
            font-size: 13.5pt;
            line-height: 1.4;
        }

        .paper-sheet {
            max-width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #fff;
            padding: 20mm 20mm 20mm 25mm;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            box-sizing: border-box;
        }

        .header-col {
            text-align: center;
            line-height: 1.25;
        }

        .header-title {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header-sub {
            font-size: 11pt;
        }

        .title-main {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 20px 0 10px 0;
        }

        .section-title {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 13.5pt;
            margin-top: 15px;
            margin-bottom: 5px;
        }

        .info-row {
            margin-bottom: 6px;
        }

        .signature-block {
            margin-top: 30px;
            text-align: center;
        }

        .signature-title {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12pt;
        }

        @media print {
            body {
                background: white !important;
                padding: 0 !important;
            }
            .paper-sheet {
                box-shadow: none !important;
                padding: 15mm 15mm 15mm 20mm !important;
                max-width: 100% !important;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: A4 portrait;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print text-center mb-3">
        <button onclick="window.print()" class="btn btn-primary px-4 shadow-sm">
            🖨️ In Biên Bản Hội Chẩn (A4)
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary px-3 ms-2">
            Đóng
        </button>
    </div>

    <div class="paper-sheet">
        <!-- Header -->
        <table style="width: 100%; margin-bottom: 10px;">
            <tr>
                <td style="width: 45%; vertical-align: top;" class="header-col">
                    <div class="header-title">SỞ Y TẾ TP. HÀ NỘI</div>
                    <div class="header-title">BỆNH VIỆN ĐA KHOA TÂM ANH</div>
                    <div class="header-sub">Khoa: <strong>{{ $consultation->requestingSpecialty?->name ?? 'Khoa Khám Bệnh' }}</strong></div>
                    <div style="font-size: 10pt; margin-top: 4px;">Mã số: <strong>{{ $consultation->consultation_code }}</strong></div>
                </td>
                <td style="width: 55%; vertical-align: top;" class="header-col">
                    <div class="header-title">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
                    <div class="header-sub" style="font-weight: bold;">Độc lập - Tự do - Hạnh phúc</div>
                    <div style="border-bottom: 1px solid #000; width: 140px; margin: 4px auto 8px auto;"></div>
                    <div style="font-style: italic; font-size: 11pt;">Hà Nội, ngày {{ $consultation->created_at->format('d') }} tháng {{ $consultation->created_at->format('m') }} năm {{ $consultation->created_at->format('Y') }}</div>
                </td>
            </tr>
        </table>

        <!-- Main Title -->
        <div class="title-main">
            TRÍCH BIÊN BẢN HỘI CHẨN CHUYÊN KHOA
            <div style="font-size: 11pt; font-weight: normal; font-style: italic; text-transform: none; margin-top: 3px;">
                (Mức độ: {{ match($consultation->urgency_level) { 'stat' => 'TỐI KHẨN (15 PHÚT)', 'urgent' => 'KHẨN CẤP (1 GIỜ)', default => 'THƯỜNG QUY' } }})
            </div>
        </div>

        <!-- I. Thông tin bệnh nhân -->
        <div class="section-title">I. PHẦN HÀNH CHÍNH & LÂM SÀNG BỆNH NHÂN:</div>
        <div class="info-row">
            - Họ và tên người bệnh: <strong style="text-transform: uppercase;">{{ $consultation->patientProfile->full_name }}</strong>
            &nbsp;&nbsp;&nbsp;&nbsp; Tuổi: <strong>{{ $consultation->patientProfile->age_text }}</strong>
            &nbsp;&nbsp;&nbsp;&nbsp; Giới tính: <strong>{{ $consultation->patientProfile->gender_name }}</strong>
        </div>
        <div class="info-row">
            - Mã số người bệnh (PID): <strong>{{ $consultation->patientProfile->patient_code }}</strong>
            &nbsp;&nbsp;&nbsp;&nbsp; Thẻ BHYT: <strong>{{ $consultation->patientProfile->insurance_code ?: 'Không' }}</strong>
        </div>
        <div class="info-row">
            - Địa chỉ: {{ $consultation->patientProfile->address ?: 'TP. Hà Nội' }}
        </div>
        <div class="info-row">
            - Tiền sử dị ứng: <strong class="{{ $consultation->patientProfile->allergies ? 'text-danger' : '' }}">{{ $consultation->patientProfile->allergies ?: 'Chưa ghi nhận dị ứng' }}</strong>
        </div>

        <!-- II. Thành phần hội chẩn -->
        <div class="section-title">II. THÀNH PHẦN THAM GIA HỘI CHẨN:</div>
        <div class="info-row">
            1. Bác sĩ yêu cầu hội chẩn: <strong>{{ $consultation->requestingUser->name }}</strong> (Chuyên khoa: {{ $consultation->requestingSpecialty?->name ?? 'Khoa Khám Bệnh' }})
        </div>
        <div class="info-row">
            2. Chuyên gia hội chẩn: <strong>{{ $consultation->consultantDoctor?->user->name ?? ($consultation->targetDoctor?->user->name ?? 'Bác sĩ chuyên khoa trực') }}</strong> (Chuyên khoa: {{ $consultation->targetSpecialty->name }})
        </div>

        <!-- III. Diễn biến và câu hỏi -->
        <div class="section-title">III. TÓM TẮT DIỄN BIẾN BỆNH & CÂU HỎI XIN Ý KIẾN:</div>
        <div class="info-row" style="text-align: justify;">
            - <strong>Lý do hội chẩn:</strong> {{ $consultation->reason }}
        </div>
        <div class="info-row" style="text-align: justify;">
            - <strong>Tóm tắt triệu chứng & cận lâm sàng:</strong> {{ $consultation->clinical_summary }}
        </div>
        <div class="info-row" style="text-align: justify;">
            - <strong>Nội dung xin ý kiến hội chẩn:</strong> {{ $consultation->proposed_questions }}
        </div>

        <!-- IV. Kết luận hội chẩn -->
        <div class="section-title">IV. KẾT LUẬN & HƯỚNG ĐIỀU TRỊ CỦA HỘI ĐỒNG:</div>
        <div class="info-row" style="text-align: justify; padding: 6px 10px; border: 1px solid #000; min-height: 80px;">
            <strong>1. Kết luận chuyên môn:</strong><br>
            {{ $consultation->consultation_result ?: '(Đang chờ hội đồng chuyên môn ký duyệt kết luận)' }}
            <br><br>
            <strong>2. Phác đồ phối hợp & Hướng xử trí tiếp theo:</strong><br>
            {{ $consultation->treatment_recommendations ?: 'Tiếp tục theo dõi diễn biến và thực hiện theo chỉ định của chuyên khoa.' }}
        </div>

        <!-- Signatures -->
        <table style="width: 100%; margin-top: 35px;">
            <tr>
                <td style="width: 50%;" class="signature-block">
                    <div class="signature-title">BÁC SĨ YÊU CẦU HỘI CHẨN</div>
                    <div style="font-style: italic; font-size: 10pt;">(Ký và ghi rõ họ tên)</div>
                    <div style="height: 65px;"></div>
                    <strong>{{ $consultation->requestingUser->name }}</strong>
                </td>
                <td style="width: 50%;" class="signature-block">
                    <div class="signature-title">BÁC SĨ CHUYÊN KHOA HỘI CHẨN</div>
                    <div style="font-style: italic; font-size: 10pt;">(Ký và ghi rõ họ tên)</div>
                    <div style="height: 65px;"></div>
                    <strong>{{ $consultation->consultantDoctor?->user->name ?? $consultation->targetSpecialty->name }}</strong>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
