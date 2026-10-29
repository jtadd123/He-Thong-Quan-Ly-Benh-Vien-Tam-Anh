@extends('layouts.admin')

@section('title', 'Sao Kê & Quản Lý Ví Ký Quỹ: ' . $patientWallet->wallet_code)

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('reception.wallets.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách ví
            </a>
            <h4 class="fw-bold mb-1 text-primary">
                <i class="bi bi-wallet-fill me-2"></i>Ví Ký Quỹ Viện Phí: {{ $patientWallet->wallet_code }}
            </h4>
            <p class="text-muted small mb-0">Chủ sở hữu: <strong>{{ $patientWallet->patientProfile->full_name }}</strong> (Mã BN: {{ $patientWallet->patientProfile->patient_code }})</p>
        </div>
        <div class="d-flex gap-2">
            <!-- Nạp Tiền Modal Button -->
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#depositModal">
                <i class="bi bi-plus-circle me-1"></i> Nạp Thêm Tạm Ứng
            </button>
            @if($patientWallet->balance > 0)
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#refundModal">
                    <i class="bi bi-arrow-return-left me-1"></i> Hoàn Ứng Viện Phí
                </button>
            @endif
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Balance Card -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm bg-primary text-white" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-white text-opacity-75 small text-uppercase fw-semibold" style="letter-spacing: 1px;">Số Dư Ký Quỹ Khả Dụng</span>
                        <i class="bi bi-credit-card-2-front fs-2 text-white text-opacity-50"></i>
                    </div>
                    <div class="display-6 fw-bold mb-3">{{ $patientWallet->formatted_balance }}</div>
                    <div class="d-flex justify-content-between align-items-center small text-white text-opacity-75 pt-2 border-top border-white border-opacity-25">
                        <span>Mã Ví: <strong class="text-white">{{ $patientWallet->wallet_code }}</strong></span>
                        <span>Trạng Thái: <span class="badge bg-white text-primary">HOẠT ĐỘNG</span></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Patient Quick Info -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-person-circle text-primary me-2"></i>Thông Tin Bệnh Nhân
                    </h6>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Họ và tên:</span>
                            <span class="fw-bold text-dark">{{ $patientWallet->patientProfile->full_name }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Số điện thoại:</span>
                            <span class="fw-semibold text-dark">{{ $patientWallet->patientProfile->phone }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Mã số thẻ BHYT:</span>
                            <span class="badge bg-light text-dark border font-monospace">{{ $patientWallet->patientProfile->insurance_code ?: 'Không có' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Nhóm máu & An toàn:</span>
                            <span class="badge bg-danger">{{ $patientWallet->patientProfile->blood_group ?: 'Chưa xét nghiệm' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction History Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-receipt-cutoff text-primary me-2"></i>Lịch Sử Giao Dịch & Sao Kê Ký Quỹ
            </h6>
            <span class="badge bg-light text-secondary border">{{ $patientWallet->transactions->count() }} giao dịch</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3">Mã Giao Dịch</th>
                            <th>Thời Gian</th>
                            <th>Loại Biến Động</th>
                            <th>Số Tiền</th>
                            <th>Số Dư Sau GD</th>
                            <th>Phương Thức</th>
                            <th>Nội Dung / Hóa Đơn</th>
                            <th class="pe-3">Cán Bộ Xử Lý</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($patientWallet->transactions as $tx)
                            <tr>
                                <td class="ps-3 fw-bold font-monospace text-primary">{{ $tx->transaction_code }}</td>
                                <td class="small">{{ $tx->created_at->format('H:i:s d/m/Y') }}</td>
                                <td>{!! $tx->type_badge !!}</td>
                                <td>
                                    @if($tx->type === 'deposit')
                                        <span class="fw-bold text-success">+{{ number_format($tx->amount, 0, ',', '.') }} VNĐ</span>
                                    @elseif($tx->type === 'deduction')
                                        <span class="fw-bold text-danger">-{{ number_format($tx->amount, 0, ',', '.') }} VNĐ</span>
                                    @else
                                        <span class="fw-bold text-primary">-{{ number_format($tx->amount, 0, ',', '.') }} VNĐ</span>
                                    @endif
                                </td>
                                <td class="fw-semibold">{{ number_format($tx->balance_after, 0, ',', '.') }} VNĐ</td>
                                <td>
                                    <span class="badge bg-light text-dark border text-uppercase">{{ $tx->payment_method }}</span>
                                </td>
                                <td class="small text-muted">{{ $tx->notes }}</td>
                                <td class="pe-3 small text-secondary">{{ $tx->processedBy?->name ?? 'Hệ thống tự động' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    Chưa có giao dịch phát sinh trong ví này.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nạp Tiền -->
<div class="modal fade" id="depositModal" tabindex="-1" aria-labelledby="depositModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('reception.patients.wallets.deposit', $patientWallet->patientProfile) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="depositModalLabel"><i class="bi bi-plus-circle me-2"></i>Nạp Tiền Tạm Ứng Ký Quỹ</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Số tiền nạp tạm ứng (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control form-control-lg fw-bold text-success" step="10000" min="50000" placeholder="VD: 1000000" required>
                        <div class="form-text">Tối thiểu: 50.000 VNĐ. Số tiền này sẽ dùng để trừ dần khi bác sĩ chỉ định xét nghiệm/thuốc.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phương thức thanh toán <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            <option value="vietqr">VietQR Napas 247 Chuyển Khoản Nhanh</option>
                            <option value="cash">Tiền Mặt Tại Quầy Tiếp Nhận</option>
                            <option value="pos">Quẹt Thẻ Ngân Hàng POS</option>
                            <option value="bank_transfer">Chuyển Khoản Trực Tiếp</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ghi chú giao dịch</label>
                        <input type="text" name="notes" class="form-control" value="Tạm ứng viện phí khám ngoại trú một chạm">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success fw-bold px-4">Xác Nhận Nạp Tiền</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Hoàn Ứng -->
<div class="modal fade" id="refundModal" tabindex="-1" aria-labelledby="refundModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('reception.wallets.refund', $patientWallet) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="refundModalLabel"><i class="bi bi-arrow-return-left me-2"></i>Hoàn Trả Tiền Tạm Ứng Thừa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 small">
                        Số dư hiện tại có thể hoàn ứng: <strong>{{ $patientWallet->formatted_balance }}</strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Số tiền hoàn ứng (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" name="refund_amount" value="{{ $patientWallet->balance }}" max="{{ $patientWallet->balance }}" min="1000" class="form-control form-control-lg fw-bold text-danger" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lý do hoàn ứng</label>
                        <input type="text" name="notes" class="form-control" value="Hoàn viện phí thừa khi kết thúc đợt khám">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger fw-bold px-4">Xác Nhận Hoàn Ứng</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
