@extends('layouts.admin')

@section('title', 'Trung Tâm Chỉ Định & Kết Quả Cận Lâm Sàng - BVĐK Tâm Anh')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-slate-800">
                <i class="bi bi-moisture text-primary me-2"></i>Trung Tâm Cận Lâm Sàng & Xét Nghiệm Y Khoa
            </h4>
            <p class="text-muted small mb-0">Quản lý phiếu chỉ định cận lâm sàng, nhập kết quả xét nghiệm/CĐHA và cảnh báo giá trị nguy hiểm (Critical Panic Values).</p>
        </div>
        <div>
            <a href="{{ route('reception.lab-orders.create') }}" class="btn btn-primary fw-semibold shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Tạo Phiếu Chỉ Định Mới
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-primary border-4">
                <span class="text-muted small d-block">Tổng Chỉ Định</span>
                <h3 class="fw-bold mb-0 text-slate-800">{{ number_format($stats['total']) }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-warning border-4">
                <span class="text-muted small d-block">Chờ Lấy Mẫu / Tiếp Nhận</span>
                <h3 class="fw-bold mb-0 text-warning">{{ number_format($stats['pending']) }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-info border-4">
                <span class="text-muted small d-block">Đang Phân Tích Kết Quả</span>
                <h3 class="fw-bold mb-0 text-info">{{ number_format($stats['processing']) }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-danger border-4">
                <span class="text-muted small d-block">Cảnh Báo Giá Trị Nguy Hiểm (Panic)</span>
                <div class="d-flex align-items-center gap-2">
                    <h3 class="fw-bold mb-0 text-danger">{{ number_format($stats['panic_alerts']) }}</h3>
                    @if($stats['panic_alerts'] > 0)
                        <span class="badge bg-danger rounded-pill animate-pulse">Khẩn cấp</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('reception.lab-orders.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Tìm theo mã chỉ định, tên bệnh nhân, SĐT..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select bg-light" onchange="this.form.submit()">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ tiếp nhận</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Đang thực hiện</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Đã hoàn tất</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold">Lọc dữ liệu</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('reception.lab-orders.index') }}" class="btn btn-outline-secondary w-100">Đặt lại</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Lab Orders Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mã Phiếu / Thời Gian</th>
                        <th>Người Bệnh</th>
                        <th>Bác Sĩ Chỉ Định</th>
                        <th>Chẩn Đoán Sơ Bộ</th>
                        <th>Số Dịch Vụ</th>
                        <th>Tổng Viện Phí</th>
                        <th>Trạng Thái</th>
                        <th class="text-end">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>
                                <strong class="text-primary font-monospace">{{ $order->order_code }}</strong>
                                <small class="text-muted d-block">{{ $order->ordered_at->format('H:i d/m/Y') }}</small>
                            </td>
                            <td>
                                <strong class="text-dark">{{ $order->patientProfile->full_name }}</strong>
                                <small class="text-muted d-block font-monospace">{{ $order->patientProfile->patient_code }} • {{ $order->patientProfile->phone }}</small>
                            </td>
                            <td>
                                <span>{{ $order->doctor ? $order->doctor->user->name : 'Ngoại trú tiếp nhận' }}</span>
                            </td>
                            <td style="max-width: 200px;">
                                <p class="text-truncate mb-0 small" title="{{ $order->clinical_diagnosis }}">{{ $order->clinical_diagnosis }}</p>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $order->items->count() }} dịch vụ</span>
                                @if($order->items->where('is_panic_alert', true)->count() > 0)
                                    <span class="badge bg-danger ms-1">🚨 Nguy hiểm</span>
                                @endif
                            </td>
                            <td>
                                <strong class="text-success">{{ number_format($order->total_amount) }} đ</strong>
                            </td>
                            <td>
                                @if($order->status === 'completed')
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Đã có kết quả</span>
                                @elseif($order->status === 'processing')
                                    <span class="badge bg-info text-dark"><i class="bi bi-hourglass-split me-1"></i>Đang phân tích</span>
                                @else
                                    <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Chờ lấy mẫu</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('reception.lab-orders.show', $order) }}" class="btn btn-outline-primary" title="Xem chi tiết & Nhập kết quả">
                                        <i class="bi bi-pencil-square"></i> Nhập KQ
                                    </a>
                                    @if($order->status === 'completed')
                                        <a href="{{ route('reception.lab-orders.report', $order) }}" target="_blank" class="btn btn-outline-secondary" title="In phiếu kết quả A4">
                                            <i class="bi bi-printer"></i> In KQ
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                Chưa có phiếu chỉ định cận lâm sàng nào phù hợp.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
