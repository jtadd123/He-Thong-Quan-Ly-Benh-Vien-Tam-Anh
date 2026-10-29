@extends('layouts.master')

@section('title', 'Cổng Tra Cứu Viện Phí Điện Tử & Thanh Toán VietQR - Bệnh Viện Tâm Anh')

@section('content')
<div class="bg-primary py-5 text-white text-center">
    <div class="container">
        <h2 class="fw-bold mb-2">
            <i class="bi bi-wallet2 me-2"></i> CỔNG TRA CỨU VIỆN PHÍ ĐIỆN TỬ & THANH TOÁN VIETQR
        </h2>
        <p class="lead mb-0 opacity-75">
            Minh bạch chi phí khám chữa bệnh ngoại trú & Hỗ trợ chuyển khoản nhanh 24/7 qua Napas 247
        </p>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Search Form Card -->
            <div class="card border-0 shadow-sm p-4 mb-4">
                <form method="GET" action="{{ route('public.billing.lookup') }}" class="row g-2 align-items-center">
                    <div class="col-md-9">
                        <label class="form-label small fw-semibold text-muted">Nhập Mã Hóa Đơn hoặc Mã Lịch Khám (VD: HD-20261028-0001 hoặc AP-20261028-0001):</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                            <input type="text" name="code" class="form-control form-control-lg font-monospace text-uppercase" placeholder="Nhập mã biên lai hoặc mã ca khám..." value="{{ $code }}" required>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold">
                            Tra Cứu Ngay
                        </button>
                    </div>
                </form>
            </div>

            @if(!empty($code))
                @if($invoice)
                    @php $statusBadge = $invoice->payment_status_badge; @endphp
                    <!-- Invoice Result -->
                    <div class="card border-0 shadow-sm overflow-hidden mb-4">
                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-light text-primary border font-monospace fs-6 px-3 py-1">{{ $invoice->invoice_code }}</span>
                                <div class="text-muted small mt-1">Ngày lập: {{ $invoice->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                            <span class="badge {{ $statusBadge['badge_class'] }} px-3 py-2 fs-6">
                                <i class="bi {{ $statusBadge['icon'] }} me-1"></i>{{ $statusBadge['label'] }}
                            </span>
                        </div>

                        <div class="card-body p-4">
                            <!-- Patient Info -->
                            <div class="row g-3 mb-4 pb-3 border-bottom">
                                <div class="col-sm-6">
                                    <span class="text-muted small">Người bệnh:</span>
                                    <h5 class="fw-bold text-dark mb-0">{{ $invoice->patientProfile->full_name }}</h5>
                                    <div class="text-muted small">Mã BN: <strong class="text-primary font-monospace">{{ $invoice->patientProfile->patient_code }}</strong></div>
                                </div>
                                <div class="col-sm-6 text-sm-end">
                                    <span class="text-muted small">Bác sĩ khám:</span>
                                    <div class="fw-semibold text-dark">{{ $invoice->appointment && $invoice->appointment->doctor ? $invoice->appointment->doctor->full_name_with_title : 'Khoa Khám Bệnh' }}</div>
                                    <div class="text-muted small">{{ $invoice->appointment && $invoice->appointment->specialty ? $invoice->appointment->specialty->name : 'Ngoại trú' }}</div>
                                </div>
                            </div>

                            <!-- Cost Breakdown -->
                            <h6 class="fw-bold text-dark text-uppercase small mb-3">Chi tiết các khoản viện phí:</h6>
                            <ul class="list-group list-group-flush mb-4">
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                    <span>1. Tiền khám lâm sàng chuyên khoa</span>
                                    <span class="font-monospace fw-semibold">{{ number_format($invoice->consultation_fee, 0, ',', '.') }} đ</span>
                                </li>
                                @if($invoice->medication_fee > 0)
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                        <span>2. Tiền thuốc & dược phẩm theo toa</span>
                                        <span class="font-monospace fw-semibold">{{ number_format($invoice->medication_fee, 0, ',', '.') }} đ</span>
                                    </li>
                                @endif
                                @if($invoice->procedure_fee > 0)
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                        <span>3. Chi phí thủ thuật y khoa</span>
                                        <span class="font-monospace fw-semibold">{{ number_format($invoice->procedure_fee, 0, ',', '.') }} đ</span>
                                    </li>
                                @endif
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 bg-light fw-bold">
                                    <span>Tổng chi phí:</span>
                                    <span class="font-monospace">{{ number_format($invoice->total_amount, 0, ',', '.') }} đ</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 text-success fw-bold">
                                    <span>Quỹ Bảo hiểm y tế (BHYT) chi trả:</span>
                                    <span class="font-monospace">-{{ number_format($invoice->insurance_covered_amount, 0, ',', '.') }} đ</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-top border-2 border-danger">
                                    <span class="fs-6 fw-bold text-danger text-uppercase">Bệnh nhân cùng chi trả:</span>
                                    <span class="fs-4 fw-bold font-monospace text-danger">{{ number_format($invoice->patient_co_pay_amount, 0, ',', '.') }} VNĐ</span>
                                </li>
                            </ul>

                            @if(!$invoice->isPaid() && $paymentDetails)
                                <!-- Dynamic VietQR Card for Instant Mobile Payment -->
                                <div class="card border-primary bg-primary-subtle p-4 text-center">
                                    <h5 class="fw-bold text-primary mb-2">
                                        <i class="bi bi-qr-code me-1"></i> Quét Mã VietQR Để Thanh Toán Ngay
                                    </h5>
                                    <p class="small text-muted mb-3">
                                        Mở app ngân hàng quét mã QR bên dưới, hệ thống tự động điền số tiền <strong>{{ $paymentDetails['formatted_amount'] }}</strong> và nội dung chuyển khoản.
                                    </p>
                                    <div class="p-2 bg-white rounded shadow-sm d-inline-block mx-auto mb-3">
                                        <img src="{{ $paymentDetails['qr_image_url'] }}" alt="VietQR" class="img-fluid" style="max-width: 250px;">
                                    </div>
                                    <div class="small font-monospace text-muted">
                                        VietinBank &bull; STK: <strong>110002868888</strong> &bull; BV DA KHOA TAM ANH
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-success d-flex align-items-center gap-3 mb-0">
                                    <i class="bi bi-check-circle-fill fs-2"></i>
                                    <div>
                                        <h6 class="fw-bold mb-0">HÓA ĐƠN ĐÃ ĐƯỢC THANH TOÁN HOÀN TẤT</h6>
                                        <small>Thanh toán lúc {{ $invoice->paid_at->format('H:i d/m/Y') }}. Cảm ơn Quý khách đã sử dụng dịch vụ tại Bệnh Viện Tâm Anh!</small>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning text-center p-4">
                        <i class="bi bi-exclamation-circle fs-2 d-block mb-2"></i>
                        <h6 class="fw-bold">Không tìm thấy thông tin viện phí</h6>
                        <p class="small mb-0 text-muted">Không tìm thấy hóa đơn nào với mã "<strong>{{ $code }}</strong>". Vui lòng kiểm tra lại mã in trên phiếu hẹn hoặc liên hệ quầy tiếp nhận.</p>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
