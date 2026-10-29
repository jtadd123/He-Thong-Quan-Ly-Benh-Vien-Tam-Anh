<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hành Trình Khám Chữa Bệnh - BV Đa Khoa Tâm Anh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .header-gradient {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: white;
            border-bottom-left-radius: 2rem;
            border-bottom-right-radius: 2rem;
            box-shadow: 0 10px 25px -5px rgba(2, 132, 199, 0.3);
        }
        .timeline-step {
            position: relative;
            padding-left: 2.75rem;
            padding-bottom: 2rem;
        }
        .timeline-step:last-child {
            padding-bottom: 0;
        }
        .timeline-step::before {
            content: '';
            position: absolute;
            left: 1.15rem;
            top: 2.2rem;
            bottom: -0.2rem;
            width: 3px;
            background-color: #e2e8f0;
        }
        .timeline-step:last-child::before {
            display: none;
        }
        .step-icon {
            position: absolute;
            left: 0;
            top: 0;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            font-weight: 700;
            background: white;
            border: 3px solid #cbd5e1;
            color: #64748b;
            z-index: 2;
        }
        .timeline-step.completed::before {
            background-color: #10b981;
        }
        .timeline-step.completed .step-icon {
            background-color: #10b981;
            border-color: #059669;
            color: white;
        }
        .timeline-step.active .step-icon {
            background-color: #0284c7;
            border-color: #38bdf8;
            color: white;
            box-shadow: 0 0 0 5px rgba(2, 132, 199, 0.25);
            animation: pulse-ring 2s infinite;
        }
        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(2, 132, 199, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(2, 132, 199, 0); }
            100% { box-shadow: 0 0 0 0 rgba(2, 132, 199, 0); }
        }
        .ticket-card {
            border: none;
            border-radius: 1.25rem;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.06);
            background: white;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="header-gradient pt-4 pb-5 px-3">
        <div class="container" style="max-width: 640px;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-white text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-hospital fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">BV ĐA KHOA TÂM ANH</h6>
                        <small class="text-white-50">Hệ Thống Theo Dõi Khám Thời Gian Thực</small>
                    </div>
                </div>
                <button onclick="window.location.reload()" class="btn btn-sm btn-light rounded-pill px-3 shadow-sm">
                    <i class="bi bi-arrow-clockwise me-1"></i> Làm mới
                </button>
            </div>

            <div class="text-center mt-3">
                <span class="badge bg-white text-primary px-3 py-2 rounded-pill fw-semibold mb-2">
                    <i class="bi bi-ticket-perforated-fill me-1"></i> SỐ THỨ TỰ TIẾP NHẬN
                </span>
                <h1 class="display-3 fw-black text-white tracking-tight mb-0">
                    {{ str_pad($appointment->queue_number ?? 1, 3, '0', STR_PAD_LEFT) }}
                </h1>
                <p class="text-white-50 small mt-1">Mã cuộc hẹn: <strong>{{ $appointment->appointment_code }}</strong></p>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="container" style="max-width: 640px; margin-top: -2.5rem;">
        <!-- Patient Info Card -->
        <div class="card ticket-card p-4 mb-3">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <h5 class="fw-bold mb-1 text-slate-800">{{ $patient->full_name }}</h5>
                    <p class="text-muted small mb-2">Mã BN: <span class="fw-semibold text-primary">{{ $patient->patient_code }}</span> • {{ $patient->age_text }} • {{ $patient->gender_name }}</p>
                </div>
                <div>
                    {!! $appointment->esi_badge !!}
                </div>
            </div>

            <hr class="my-2 border-slate-200">

            <div class="row g-2 mt-1">
                <div class="col-6">
                    <div class="p-2 bg-light rounded-3">
                        <small class="text-muted d-block">Chuyên khoa</small>
                        <strong class="text-primary fs-6">{{ $specialty?->name ?? 'Khoa Khám Bệnh' }}</strong>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 bg-light rounded-3">
                        <small class="text-muted d-block">Bác sĩ khám</small>
                        <strong class="text-slate-700 fs-6">{{ $doctor?->user->name ?? 'Bác sĩ trực khám' }}</strong>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 bg-light rounded-3">
                        <small class="text-muted d-block">Phòng khám</small>
                        <strong class="text-dark fs-6"><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $doctor?->room_number ?? 'Phòng số 01' }}</strong>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 bg-light rounded-3">
                        <small class="text-muted d-block">Thời gian đăng ký</small>
                        <strong class="text-dark fs-6">{{ $appointment->created_at->format('H:i - d/m/Y') }}</strong>
                    </div>
                </div>
            </div>

            <!-- Wait Status Banner -->
            @if($current_step === 2)
                <div class="alert alert-info border-0 rounded-3 mt-3 mb-0 d-flex align-items-center gap-3">
                    <i class="bi bi-people-fill fs-2 text-info"></i>
                    <div>
                        <h6 class="fw-bold mb-0">Còn {{ $people_ahead }} người nữa trước bạn</h6>
                        <small class="text-muted">Ước tính thời gian chờ khoảng: <strong>{{ $estimated_wait_minutes }} phút</strong>. Bạn có thể thong thả nghỉ ngơi tại sảnh chờ.</small>
                    </div>
                </div>
            @elseif($current_step === 3)
                <div class="alert alert-warning border-0 rounded-3 mt-3 mb-0 d-flex align-items-center gap-3">
                    <i class="bi bi-megaphone-fill fs-2 text-warning"></i>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">ĐANG GỌI KHÁM!</h6>
                        <small class="text-dark">Xin mời bạn di chuyển ngay vào phòng khám <strong>{{ $doctor?->room_number ?? 'Phòng số 01' }}</strong> để bác sĩ thăm khám.</small>
                    </div>
                </div>
            @elseif($current_step === 4)
                <div class="alert alert-primary border-0 rounded-3 mt-3 mb-0 d-flex align-items-center gap-3">
                    <i class="bi bi-wallet2 fs-2 text-primary"></i>
                    <div>
                        <h6 class="fw-bold mb-0">Bác sĩ đã hoàn tất khám & kê đơn!</h6>
                        <small class="text-muted">Vui lòng tiến hành thanh toán viện phí và nhận thuốc tại Quầy Dược Ngoại Trú (Tầng 1).</small>
                    </div>
                </div>
            @elseif($current_step === 5)
                <div class="alert alert-success border-0 rounded-3 mt-3 mb-0 d-flex align-items-center gap-3">
                    <i class="bi bi-check-circle-fill fs-2 text-success"></i>
                    <div>
                        <h6 class="fw-bold mb-0">Đã hoàn tất toàn bộ quy trình khám!</h6>
                        <small class="text-muted">Chúc Quý khách mau chóng bình phục sức khỏe. Cảm ơn đã tin tưởng BV Đa Khoa Tâm Anh.</small>
                    </div>
                </div>
            @endif
        </div>

        <!-- 5-Step Journey Flow -->
        <div class="card ticket-card p-4 mb-3">
            <h6 class="fw-bold text-uppercase text-secondary small mb-4 tracking-wider">
                <i class="bi bi-diagram-3-fill me-1 text-primary"></i> Tiến Trình Khám Chi Tiết (5 Chặng)
            </h6>

            <div class="timeline-container">
                <!-- Chặng 1: Tiếp nhận -->
                <div class="timeline-step {{ $step_status[1]['state'] }}">
                    <div class="step-icon"><i class="bi bi-check2"></i></div>
                    <div class="step-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="fw-bold mb-0">1. Tiếp nhận & Phân luồng Triage</h6>
                            <span class="badge bg-light text-muted small">{{ $step_status[1]['time'] }}</span>
                        </div>
                        <p class="text-muted small mb-0 mt-1">Đã cấp số thứ tự, kiểm tra thẻ BHYT và ghi nhận sinh hiệu ban đầu.</p>
                    </div>
                </div>

                <!-- Chặng 2: Hàng đợi phòng khám -->
                <div class="timeline-step {{ $step_status[2]['state'] }}">
                    <div class="step-icon">2</div>
                    <div class="step-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="fw-bold mb-0">2. Hàng đợi phòng khám</h6>
                            @if($step_status[2]['state'] === 'active')
                                <span class="badge bg-primary px-2 py-1">Đang chờ</span>
                            @endif
                        </div>
                        <p class="text-muted small mb-0 mt-1">Phòng khám chuyên khoa: <strong>{{ $specialty?->name }}</strong> ({{ $doctor?->room_number ?? 'Phòng 01' }}).</p>
                    </div>
                </div>

                <!-- Chặng 3: Bác sĩ khám -->
                <div class="timeline-step {{ $step_status[3]['state'] }}">
                    <div class="step-icon">3</div>
                    <div class="step-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="fw-bold mb-0">3. Bác sĩ thăm khám lâm sàng</h6>
                            @if($step_status[3]['time'])
                                <span class="badge bg-light text-muted small">{{ $step_status[3]['time'] }}</span>
                            @endif
                        </div>
                        <p class="text-muted small mb-0 mt-1">Khám chuyên sâu, chẩn đoán triệu chứng và chỉ định cận lâm sàng cần thiết.</p>
                    </div>
                </div>

                <!-- Chặng 4: Viện phí & Đơn thuốc -->
                <div class="timeline-step {{ $step_status[4]['state'] }}">
                    <div class="step-icon">4</div>
                    <div class="step-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="fw-bold mb-0">4. Đơn thuốc & Viện phí điện tử</h6>
                            @if($step_status[4]['state'] === 'active')
                                <span class="badge bg-primary px-2 py-1">Đang xử lý</span>
                            @endif
                        </div>
                        <p class="text-muted small mb-0 mt-1">
                            @if($invoices->isNotEmpty())
                                Đã có hóa đơn viện phí: <strong>{{ number_format($invoices->first()->patient_co_pay_amount) }} VNĐ</strong>.
                                <a href="{{ route('public.billing.lookup', ['code' => $appointment->appointment_code]) }}" class="btn btn-sm btn-outline-primary ms-1 py-0 px-2">Xem & Trả VietQR</a>
                            @else
                                Kê đơn thuốc điện tử và tính khấu trừ BHYT tự động.
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Chặng 5: Hoàn tất & Lấy thuốc -->
                <div class="timeline-step {{ $step_status[5]['state'] }}">
                    <div class="step-icon"><i class="bi bi-capsule"></i></div>
                    <div class="step-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="fw-bold mb-0">5. Quầy Dược Ngoại Trú & Lấy thuốc</h6>
                            @if($step_status[5]['time'])
                                <span class="badge bg-light text-success small">{{ $step_status[5]['time'] }}</span>
                            @endif
                        </div>
                        <p class="text-muted small mb-0 mt-1">Nhận thuốc theo mã vạch, in tem nhãn liều dùng và hoàn tất buổi khám.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- History of SMS / ZNS sent -->
        @if($notifications->isNotEmpty())
            <div class="card ticket-card p-4 mb-4">
                <h6 class="fw-bold text-uppercase text-secondary small mb-3">
                    <i class="bi bi-chat-dots-fill me-1 text-primary"></i> Tin Nhắn Cảnh Báo Đã Gửi (SMS / Zalo ZNS)
                </h6>
                <div class="list-group list-group-flush">
                    @foreach($notifications as $notif)
                        <div class="list-group-item px-0 py-2 border-slate-100">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold text-dark small">{{ $notif->title }}</span>
                                <small class="text-muted">{{ $notif->created_at->format('H:i') }}</small>
                            </div>
                            <p class="text-muted small mb-0">{{ $notif->message_content }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="text-center text-muted small py-4">
            <p class="mb-1">Hệ thống Y tế Bệnh Viện Đa Khoa Tâm Anh</p>
            <p class="mb-0">Hotline cấp cứu 24/7: <strong>1800 6858</strong> • 108 Phố Hoàng Như Tiếp, Bồ Đề, Long Biên, Hà Nội</p>
        </div>
    </main>

</body>
</html>
