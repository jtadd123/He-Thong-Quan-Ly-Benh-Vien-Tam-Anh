<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ Sơ Y Khoa Bác Sĩ - {{ $doctor->full_name_with_title }} | Bệnh Viện Tâm Anh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Times New Roman', Times, serif;
            color: #0f172a;
            font-size: 14pt;
            line-height: 1.5;
        }
        .a4-document {
            max-width: 850px;
            margin: 30px auto;
            background: #ffffff;
            padding: 40px 50px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #cbd5e1;
            position: relative;
        }
        .watermark {
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 4.5rem;
            color: rgba(2, 132, 199, 0.04);
            font-weight: 900;
            text-transform: uppercase;
            pointer-events: none;
            white-space: nowrap;
            letter-spacing: 4px;
            font-family: sans-serif;
        }
        .doc-header-border {
            border-bottom: 2px solid #0284c7;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .section-title {
            font-family: 'Segoe UI', sans-serif;
            font-size: 12pt;
            font-weight: 700;
            text-transform: uppercase;
            color: #0284c7;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
            margin-top: 20px;
            margin-bottom: 12px;
        }
        .info-label {
            font-family: 'Segoe UI', sans-serif;
            font-size: 10.5pt;
            font-weight: 600;
            color: #475569;
        }
        .info-value {
            font-size: 11pt;
            font-weight: 700;
            color: #0f172a;
        }
        @media print {
            body {
                background: none;
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .a4-document {
                box-shadow: none;
                border: none;
                margin: 0;
                padding: 20px;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Top Action Bar (Screen Only) -->
    <div class="container py-3 no-print max-w-850 mx-auto" style="max-width: 850px;">
        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('public.doctors.show', $doctor) }}" class="btn btn-outline-secondary rounded-pill">
                <i class="bi bi-arrow-left me-1"></i> Quay lại hồ sơ web
            </a>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                    <i class="bi bi-printer-fill me-1"></i> In Hồ Sơ / Lưu PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Official Document Container -->
    <div class="a4-document">
        <div class="watermark">BỆNH VIỆN TÂM ANH</div>

        <!-- Official Header -->
        <div class="doc-header-border">
            <div class="row align-items-center">
                <div class="col-7">
                    <div style="font-family: 'Segoe UI', sans-serif; font-size: 10pt; font-weight: 700; text-transform: uppercase; color: #0284c7;">
                        BỆNH VIỆN ĐA KHOA TÂM ANH
                    </div>
                    <div style="font-size: 9.5pt; color: #64748b;">
                        HỘI ĐỒNG Y KHOA & QUẢN LÝ CHẤT LƯỢNG KHÁM CHỮA BỆNH
                    </div>
                    <div style="font-size: 9pt; color: #94a3b8;">
                        Số hồ sơ: <strong>DOC-{{ str_pad($doctor->id, 5, '0', STR_PAD_LEFT) }}/TA-HR</strong>
                    </div>
                </div>
                <div class="col-5 text-end">
                    <div style="font-family: 'Segoe UI', sans-serif; font-size: 9pt; font-weight: 700;">
                        CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM
                    </div>
                    <div style="font-size: 9pt; font-style: italic;">
                        Độc lập - Tự do - Hạnh phúc
                    </div>
                    <div style="font-size: 8.5pt; color: #64748b; margin-top: 3px;">
                        Hà Nội, ngày {{ now()->format('d') }} tháng {{ now()->format('m') }} năm {{ now()->format('Y') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Document Main Title -->
        <div class="text-center mb-4">
            <h3 style="font-family: 'Segoe UI', sans-serif; font-weight: 800; color: #0f172a; text-transform: uppercase; margin-bottom: 4px;">
                BẢN GIỚI THIỆU HỒ SƠ NĂNG LỰC CHUYÊN MÔN BÁC SĨ
            </h3>
            <div style="font-size: 10pt; font-style: italic; color: #475569;">
                (Trích lục chính thức từ Hệ thống Quản trị Bệnh viện Đa khoa Tâm Anh)
            </div>
        </div>

        <!-- Doctor Summary Box -->
        <div class="p-3 bg-light rounded-3 mb-3 border">
            <div class="row align-items-center">
                <div class="col-auto text-center">
                    <div class="rounded-3 overflow-hidden border" style="width: 105px; height: 130px; background: #e2e8f0;">
                        @if($doctor->avatar_url)
                            <img src="{{ $doctor->avatar_url }}" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <div class="d-flex align-items-center justify-content-center h-100 fs-1 text-secondary">
                                <i class="bi bi-person"></i>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col">
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <span class="info-label d-block">Họ và tên bác sĩ:</span>
                            <span class="info-value fs-5">{{ $doctor->full_name_with_title }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="info-label d-block">Chuyên khoa:</span>
                            <span class="info-value text-primary">{{ $doctor->specialty->name ?? 'Đa khoa' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="info-label d-block">Học hàm / Học vị:</span>
                            <span class="info-value">{{ $doctor->qualification ?: 'Bác sĩ chuyên khoa' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="info-label d-block">Phòng khám tiếp nhận:</span>
                            <span class="info-value">{{ $doctor->room_number }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="info-label d-block">Giá khám niêm yết:</span>
                            <span class="info-value">{{ number_format($doctor->consultation_fee, 0, ',', '.') }} VNĐ / lượt</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="info-label d-block">Trạng thái tiếp nhận:</span>
                            <span class="info-value text-success">{{ $doctor->is_available ? 'Đang sẵn sàng nhận bệnh' : 'Tạm dừng tiếp nhận' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biography / Experience -->
        <div class="section-title">I. Lý lịch chuyên môn & Quá trình công tác lâm sàng</div>
        <div style="font-size: 11pt; text-align: justify; text-indent: 25px; line-height: 1.6;">
            {!! nl2br(e($doctor->bio ?: 'Bác sĩ được đào tạo chuyên sâu và có nhiều năm kinh nghiệm công tác trong lĩnh vực y khoa chuyên ngành tại các cơ sở y tế đầu ngành. Bác sĩ luôn tuân thủ nghiêm ngặt các quy trình chuyên môn chuẩn mực, ứng dụng các kỹ thuật chẩn đoán và phác đồ điều trị tiên tiến nhất nhằm đem lại kết quả điều trị tối ưu và sự an tâm tuyệt đối cho người bệnh.')) !!}
        </div>

        <!-- Weekly Clinical Schedule Table -->
        <div class="section-title">II. Phân công lịch trực khám bệnh trong tuần</div>
        <table class="table table-bordered table-sm" style="font-size: 10pt; font-family: 'Segoe UI', sans-serif;">
            <thead class="table-light text-center">
                <tr>
                    <th style="width: 25%;">Thứ trong tuần</th>
                    <th style="width: 25%;">Ca Sáng (07:30 - 11:30)</th>
                    <th style="width: 25%;">Ca Chiều (13:30 - 17:00)</th>
                    <th style="width: 25%;">Giới hạn bệnh nhân</th>
                </tr>
            </thead>
            <tbody>
                @foreach($days as $dayKey => $dayLabel)
                    @php
                        $morning = $doctor->schedules->first(fn($s) => $s->day_of_week === $dayKey && $s->shift === 'morning' && $s->is_active);
                        $afternoon = $doctor->schedules->first(fn($s) => $s->day_of_week === $dayKey && $s->shift === 'afternoon' && $s->is_active);
                    @endphp
                    <tr>
                        <td class="fw-semibold ps-2">{{ $dayLabel }}</td>
                        <td class="text-center">
                            @if($morning)
                                <span class="badge bg-primary text-white">Khám sáng</span>
                            @else
                                <span class="text-muted">&mdash;</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($afternoon)
                                <span class="badge bg-warning text-dark">Khám chiều</span>
                            @else
                                <span class="text-muted">&mdash;</span>
                            @endif
                        </td>
                        <td class="text-center">
                            {{ ($morning->max_patients ?? $afternoon->max_patients ?? 15) }} bệnh nhân/ca
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Signatures and QR Code -->
        <div class="row align-items-center mt-4 pt-2">
            <div class="col-4 text-center">
                <div class="p-1 border d-inline-block bg-white rounded">
                    <img src="{{ $qrCodeUrl }}" alt="QR Code" style="width: 105px; height: 105px;">
                </div>
                <div style="font-size: 8pt; font-family: sans-serif; color: #64748b; margin-top: 3px;">
                    Quét xác thực hồ sơ trực tuyến
                </div>
            </div>
            <div class="col-4 text-center">
                <div style="font-family: 'Segoe UI', sans-serif; font-size: 9.5pt; font-weight: 700;">
                    BÁC SĨ CHUYÊN KHOA
                </div>
                <div style="font-size: 8.5pt; font-style: italic; color: #64748b;">(Ký và ghi rõ họ tên)</div>
                <div style="height: 60px;"></div>
                <div style="font-weight: 700; font-size: 10pt;">{{ $doctor->full_name_with_title }}</div>
            </div>
            <div class="col-4 text-center">
                <div style="font-family: 'Segoe UI', sans-serif; font-size: 9.5pt; font-weight: 700;">
                    GIÁM ĐỐC CHUYÊN MÔN
                </div>
                <div style="font-size: 8.5pt; font-style: italic; color: #64748b;">(Ký, đóng dấu bệnh viện)</div>
                <div style="height: 60px;"></div>
                <div style="font-weight: 700; font-size: 10pt; color: #0284c7;">GS.TS.BS NGUYỄN THANH LIÊM</div>
            </div>
        </div>

        <div style="font-size: 8pt; font-family: sans-serif; color: #94a3b8; border-top: 1px dashed #cbd5e1; margin-top: 25px; padding-top: 8px;" class="text-center">
            Bệnh viện Đa khoa Tâm Anh • Địa chỉ: 108 Hoàng Như Tiếp, Bồ Đề, Long Biên, Hà Nội • Hotline: 1800 6858 • Website: tamanhhospital.vn
        </div>
    </div>
</body>
</html>
