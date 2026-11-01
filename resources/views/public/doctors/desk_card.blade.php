<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng Tên Để Bàn - {{ $doctor->full_name_with_title }} | Bệnh Viện Tâm Anh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #1e293b;
        }
        .desk-card-wrapper {
            max-width: 720px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 2px solid #e2e8f0;
        }
        .card-header-brand {
            background: linear-gradient(135deg, #0284c7 0%, #0d9488 100%);
            color: #ffffff;
            padding: 24px 32px;
        }
        .qr-frame {
            background: #ffffff;
            padding: 12px;
            border-radius: 16px;
            border: 2px dashed #0284c7;
            display: inline-block;
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
            .desk-card-wrapper {
                box-shadow: none;
                border: 2px solid #0284c7;
                margin: 0;
                max-width: 100%;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 no-print max-w-720 mx-auto" style="max-width: 720px;">
            <a href="{{ route('public.doctors.show', $doctor) }}" class="btn btn-outline-secondary rounded-pill">
                <i class="bi bi-arrow-left me-1"></i> Quay lại hồ sơ bác sĩ
            </a>
            <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                <i class="bi bi-printer me-1"></i> In Thẻ Để Bàn (Desk Tent Card)
            </button>
        </div>

        <div class="desk-card-wrapper">
            <!-- Header with Hospital Logo -->
            <div class="card-header-brand d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-hospital fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-white text-uppercase" style="letter-spacing: 0.5px;">BỆNH VIỆN ĐA KHOA TÂM ANH</h5>
                        <small class="text-white-50">TẬN TÂM - CHUYÊN NGHIỆP - HIỆN ĐẠI</small>
                    </div>
                </div>
                <div class="text-end">
                    <span class="badge bg-white text-primary fw-bold px-3 py-2 rounded-pill">
                        <i class="bi bi-door-open-fill me-1"></i> {{ $doctor->room_number }}
                    </span>
                </div>
            </div>

            <!-- Body -->
            <div class="p-4 p-md-5">
                <div class="row align-items-center g-4">
                    <div class="col-sm-7 text-center text-sm-start">
                        <div class="mb-3">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1 rounded-pill fw-semibold">
                                {{ $doctor->specialty->name ?? 'Đa khoa' }}
                            </span>
                        </div>
                        <h2 class="fw-bold text-dark mb-2">{{ $doctor->full_name_with_title }}</h2>
                        <p class="text-muted small mb-4">
                            {{ $doctor->qualification }} • Bác sĩ Chuyên khoa Bệnh viện Đa khoa Tâm Anh
                        </p>

                        <div class="p-3 bg-light rounded-4 mb-3 border">
                            <div class="small fw-semibold text-dark mb-1">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i> Địa điểm thăm khám:
                            </div>
                            <div class="fw-bold text-primary">{{ $doctor->room_number }}</div>
                            <div class="text-muted small mt-1">108 Hoàng Như Tiếp, P. Bồ Đề, Q. Long Biên, TP. Hà Nội</div>
                        </div>

                        <div class="small text-muted">
                            <i class="bi bi-telephone-fill text-success me-1"></i> Tổng đài CSKH: <strong>1800 6858</strong>
                        </div>
                    </div>

                    <div class="col-sm-5 text-center">
                        <div class="qr-frame mb-2 shadow-xs">
                            <img src="{{ $qrCodeUrl }}" alt="QR Code Bác sĩ {{ $doctor->full_name_with_title }}" class="img-fluid" style="width: 190px; height: 190px;">
                        </div>
                        <div class="small fw-bold text-dark mt-2">
                            <i class="bi bi-qr-code-scan text-primary me-1"></i> Quét mã QR bằng Camera
                        </div>
                        <p class="text-muted small mb-0" style="font-size: 0.78rem;">
                            Xem lịch trực tuần • Đặt lịch khám • Lưu danh thiếp số (.vcf)
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer Fold Guideline -->
            <div class="bg-light p-3 text-center border-top small text-muted">
                <i class="bi bi-scissors me-1"></i> Đường gấp thẻ để bàn mica phòng khám (Kích thước chuẩn: A5 gập ngang / 15x21cm)
            </div>
        </div>
    </div>
</body>
</html>
