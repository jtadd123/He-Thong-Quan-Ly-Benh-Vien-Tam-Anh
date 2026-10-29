@extends('layouts.admin')

@section('title', 'Ví Ký Quỹ & Tạm Ứng Viện Phí Thông Minh')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-primary">
                <i class="bi bi-wallet2 me-2"></i>Ví Ký Quỹ & Tạm Ứng Viện Phí Thông Minh
            </h4>
            <p class="text-muted small mb-0">Hệ thống ký quỹ khám bệnh một chạm - Không cần xếp hàng đóng tiền nhiều lần - Bệnh Viện Tâm Anh</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm border-start border-primary border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Tổng Số Ví Bệnh Nhân</div>
                            <div class="fs-4 fw-bold text-dark">{{ number_format($stats['total_wallets']) }}</div>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                            <i class="bi bi-credit-card-2-front fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm border-start border-success border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Tổng Số Dư Tạm Ứng Đang Quản Lý</div>
                            <div class="fs-4 fw-bold text-success">{{ number_format($stats['total_funds'], 0, ',', '.') }} VNĐ</div>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                            <i class="bi bi-cash-stack fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm border-start border-info border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Ví Đang Có Số Dư Khả Dụng</div>
                            <div class="fs-4 fw-bold text-info">{{ number_format($stats['positive_wallets']) }}</div>
                        </div>
                        <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                            <i class="bi bi-check2-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('reception.wallets.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control border-start-0" placeholder="Tìm kiếm theo Mã ví, Họ tên bệnh nhân, Mã BN, Số điện thoại...">
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary px-3">
                        <i class="bi bi-funnel me-1"></i> Tìm Kiếm
                    </button>
                    @if($search)
                        <a href="{{ route('reception.wallets.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3">Mã Ví Ký Quỹ</th>
                            <th>Bệnh Nhân</th>
                            <th>Số Điện Thoại</th>
                            <th>Số Dư Khả Dụng</th>
                            <th>Trạng Thái</th>
                            <th>Cập Nhật Lần Cuối</th>
                            <th class="text-end pe-3">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($wallets as $w)
                            <tr>
                                <td class="ps-3 fw-bold text-primary font-monospace">{{ $w->wallet_code }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $w->patientProfile->full_name }}</div>
                                    <small class="text-muted">Mã: {{ $w->patientProfile->patient_code }} | {{ $w->patientProfile->age_text }}</small>
                                </td>
                                <td>{{ $w->patientProfile->phone }}</td>
                                <td>
                                    <span class="fs-6 fw-bold text-success">{{ $w->formatted_balance }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-success">Đang Hoạt Động</span>
                                </td>
                                <td class="small text-muted">{{ $w->updated_at->format('H:i d/m/Y') }}</td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('reception.wallets.show', $w) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-clock-history me-1"></i> Chi Tiết & Sao Kê
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-wallet fs-1 d-block mb-2"></i>
                                    Chưa có ví ký quỹ nào được tạo. Khi nạp tiền tạm ứng cho bệnh nhân, ví sẽ tự động kích hoạt.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($wallets->hasPages())
                <div class="p-3 border-top">
                    {{ $wallets->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
