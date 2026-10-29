<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu Số Thứ Tự Tiếp Nhận - {{ $appointment->appointment_code }} - BV Tâm Anh</title>

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background-color: #0f172a;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #0f172a;
            margin: 0;
            padding: 20px;
        }

        /* 80mm Thermal Paper Simulator */
        .ticket-wrapper {
            max-width: 380px;
            margin: 0 auto;
            background: #ffffff;
            padding: 24px 20px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.4);
            text-align: center;
        }

        .ticket-queue-number {
            font-size: 3.8rem;
            font-weight: 900;
            line-height: 1;
            color: #005baa;
            letter-spacing: 2px;
            margin: 12px 0;
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
        }

        .ticket-qr img {
            width: 130px;
            height: 130px;
            border: 1px solid #cbd5e1;
            padding: 4px;
            border-radius: 8px;
        }

        .ticket-divider {
            border-top: 1px dashed #94a3b8;
            margin: 14px 0;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .ticket-wrapper {
                max-width: 80mm !important;
                width: 80mm !important;
                margin: 0 !important;
                padding: 5mm !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
            .ticket-queue-number {
                font-size: 3.5rem !important;
            }
        }
    </style>
</head>
<body>

    <!-- Kiosk Control Bar -->
    <div class="no-print text-center mb-4">
        <div class="d-inline-flex align-items-center gap-3 bg-white p-3 rounded-pill shadow-sm">
            <a href="{{ route('kiosk.index') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">
                <i class="bi bi-house me-1"></i> Màn Hình Chính
            </a>
            <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 fw-bold shadow">
                <i class="bi bi-printer-fill me-1"></i> In Lại Phiếu
            </button>
            <div class="text-danger fw-bold small ms-2">
                Tự động quay về sau: <span id="countdown">8</span>s
            </div>
        </div>
    </div>

    <!-- 80mm Ticket Paper -->
    <div class="ticket-wrapper">
        <div class="fw-bold fs-6 text-uppercase" style="letter-spacing: 0.5px; color: #005baa;">
            BỆNH VIỆN ĐA KHOA TÂM ANH
        </div>
        <div class="small text-muted" style="font-size: 0.75rem;">108 Hoàng Như Tiếp, Long Biên, Hà Nội</div>
        <div class="small text-muted" style="font-size: 0.72rem;">Hotline: 1800 6858 &bull; tamanhhospital.vn</div>

        <div class="ticket-divider"></div>

        <div class="small text-muted text-uppercase fw-bold">PHIẾU SỐ THỨ TỰ TIẾP NHẬN</div>
        
        <!-- Big Queue Number -->
        <div class="ticket-queue-number">
            A-{{ str_pad($appointment->queue_number, 3, '0', STR_PAD_LEFT) }}
        </div>

        <div class="fw-bold fs-5 text-dark text-uppercase">
            {{ $appointment->patientProfile->full_name }}
        </div>
        <div class="small text-muted font-monospace">
            Mã BN: {{ $appointment->patientProfile->patient_code }} &bull; SĐT: {{ $appointment->patientProfile->phone }}
        </div>

        <div class="ticket-divider"></div>

        <div class="text-start small mb-2">
            <div>Chuyên khoa: <strong class="text-primary">{{ $appointment->specialty ? $appointment->specialty->name : 'Khám chuyên khoa' }}</strong></div>
            <div>Bác sĩ: <strong>{{ $appointment->doctor ? $appointment->doctor->full_name_with_title : 'Lễ tân điều phối' }}</strong></div>
            <div>Địa điểm: <strong>{{ $appointment->doctor ? 'Phòng ' . $appointment->doctor->room_number : 'Quầy tiếp đón số 01' }}</strong></div>
            <div>Thời gian in: <span class="font-monospace">{{ date('H:i:s d/m/Y') }}</span></div>
            <div>Mã phiếu: <span class="font-monospace fw-bold">{{ $appointment->appointment_code }}</span></div>
        </div>

        <div class="ticket-divider"></div>

        <!-- Tracking QR Code -->
        <div class="ticket-qr mb-2">
            <img src="{{ $qrCodeUrl }}" alt="QR Tra Cứu Thứ Tự">
            <div class="small text-muted mt-1" style="font-size: 0.72rem;">Quét để theo dõi thứ tự khám trên điện thoại</div>
        </div>

        <div class="alert alert-light border p-2 small mb-0 text-muted" style="font-size: 0.72rem; line-height: 1.3;">
            <i class="bi bi-info-circle me-1 text-primary"></i>
            Quý khách vui lòng ngồi chờ tại sảnh, chú ý lắng nghe loa gọi số và theo dõi màn hình TV hiển thị hàng đợi.
        </div>
    </div>

    <script>
        // Auto print on page load
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 600);

            // Auto return to Kiosk Home after 8s
            let count = 8;
            const timerEl = document.getElementById('countdown');
            const interval = setInterval(() => {
                count--;
                if (timerEl) timerEl.textContent = count;
                if (count <= 0) {
                    clearInterval(interval);
                    window.location.href = "{{ route('kiosk.index') }}";
                }
            }, 1000);
        });
    </script>
</body>
</html>
