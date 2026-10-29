<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tra Cứu Giao Thuốc Tận Nhà - BV Đa Khoa Tâm Anh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
        }
        .header-bg {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: white;
            border-bottom-left-radius: 1.5rem;
            border-bottom-right-radius: 1.5rem;
            padding: 2.5rem 1rem 3rem 1rem;
        }
        .tracking-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            background: #fff;
        }
        .step-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1rem;
            margin: 0 auto 8px auto;
        }
    </style>
</head>
<body>

    <header class="header-bg text-center">
        <div class="container" style="max-width: 600px;">
            <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                <i class="bi bi-box2-heart-fill fs-3 text-warning"></i>
                <h5 class="fw-bold mb-0">HÀNH TRÌNH GIAO THUỐC TẬN NHÀ</h5>
            </div>
            <p class="text-white-50 small mb-2">Khoa Dược - Bệnh Viện Đa Khoa Tâm Anh</p>
            <h3 class="fw-black font-monospace text-white">{{ $delivery->tracking_code }}</h3>
            <span class="badge bg-white text-primary px-3 py-1 rounded-pill">Vận đơn: {{ $delivery->waybill_number }}</span>
        </div>
    </header>

    <main class="container" style="max-width: 600px; margin-top: -2rem;">
        <!-- Status Tracker Progress -->
        <div class="card tracking-card p-4 mb-3">
            <h6 class="fw-bold text-uppercase text-secondary small mb-4 tracking-wider">
                <i class="bi bi-truck text-primary me-1"></i> Tiến Trình Bưu Phẩm Dược
            </h6>

            @php
                $step = match($delivery->status) {
                    'pending_packaging' => 1,
                    'packaged' => 2,
                    'in_transit' => 3,
                    'delivered' => 4,
                    default => 1,
                };
            @endphp

            <div class="row text-center g-2">
                <div class="col-3">
                    <div class="step-circle {{ $step >= 1 ? 'bg-success text-white' : 'bg-light text-muted' }}">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <small class="d-block fw-semibold" style="font-size: 0.72rem;">Đã Tiếp Nhận</small>
                </div>
                <div class="col-3">
                    <div class="step-circle {{ $step >= 2 ? 'bg-success text-white' : ($step == 1 ? 'bg-primary text-white animate-pulse' : 'bg-light text-muted') }}">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <small class="d-block fw-semibold" style="font-size: 0.72rem;">Đang Đóng Gói</small>
                </div>
                <div class="col-3">
                    <div class="step-circle {{ $step >= 3 ? 'bg-success text-white' : ($step == 2 ? 'bg-primary text-white animate-pulse' : 'bg-light text-muted') }}">
                        <i class="bi bi-truck"></i>
                    </div>
                    <small class="d-block fw-semibold" style="font-size: 0.72rem;">Đang Giao Hàng</small>
                </div>
                <div class="col-3">
                    <div class="step-circle {{ $step >= 4 ? 'bg-success text-white' : 'bg-light text-muted' }}">
                        <i class="bi bi-house-check"></i>
                    </div>
                    <small class="d-block fw-semibold" style="font-size: 0.72rem;">Đã Nhận Thuốc</small>
                </div>
            </div>

            @if($delivery->requires_cold_chain)
                <div class="alert alert-info border-0 rounded-3 mt-4 mb-0 small d-flex align-items-center gap-2">
                    <i class="bi bi-snow2 fs-4 text-info"></i>
                    <div>
                        <strong>Gói thuốc có bảo quản lạnh (2°C - 8°C):</strong>
                        Được bọc trong thùng xốp đá gel y tế chuyên dụng, đảm bảo hoạt tính sinh học của thuốc.
                    </div>
                </div>
            @endif
        </div>

        <!-- Recipient & Details -->
        <div class="card tracking-card p-4 mb-3">
            <h6 class="fw-bold text-slate-800 mb-3"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Thông Tin Nhận Thuốc</h6>
            <ul class="list-group list-group-flush small">
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">Người nhận:</span>
                    <strong>{{ $delivery->recipient_name }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">Số điện thoại:</span>
                    <strong>{{ $delivery->recipient_phone }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">Địa chỉ giao:</span>
                    <strong class="text-end" style="max-width: 60%;">{{ $delivery->delivery_address }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">Đơn vị vận chuyển:</span>
                    <strong>{{ $delivery->carrier_name_formatted }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">Cước vận chuyển:</span>
                    <strong class="text-success">{{ number_format($delivery->shipping_fee) }} VNĐ</strong>
                </li>
            </ul>
        </div>

        <!-- Medications Inside -->
        <div class="card tracking-card p-4 mb-4">
            <h6 class="fw-bold text-slate-800 mb-3"><i class="bi bi-capsule text-primary me-2"></i>Thuốc Trong Bưu Kiện</h6>
            <div class="list-group list-group-flush small">
                @foreach($delivery->medicalRecord->prescriptions as $item)
                    <div class="list-group-item px-0 py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="text-dark">{{ $item->medication_name }}</strong>
                            <span class="badge bg-light text-dark border">{{ $item->dosage }}</span>
                        </div>
                        <small class="text-muted d-block mt-1">{{ $item->instructions }}</small>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="text-center text-muted small py-3">
            <p class="mb-1">Hệ Thống Y Tế Bệnh Viện Đa Khoa Tâm Anh</p>
            <p class="mb-0">Hỗ trợ giao thuốc: <strong>1800 6858</strong> • Nhà thuốc đạt chuẩn GPP</p>
        </div>
    </main>

</body>
</html>
