@extends('layouts.admin')

@section('title', 'Bảo Lãnh Viện Phí Tư Nhân TPA & Khách VIP')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-primary">
                <i class="bi bi-shield-lock-fill me-2"></i>Bảo Lãnh Viện Phí Tư Nhân TPA & Khách VIP
            </h4>
            <p class="text-muted small mb-0">Hệ thống bảo lãnh viện phí trực tiếp (Direct Billing GOP) liên kết các hãng bảo hiểm cao cấp - Bệnh Viện Tâm Anh</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reception.private-insurance.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-plus-circle"></i> Tiếp Nhận Thư Bảo Lãnh GOP Mới
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-primary border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Tổng Hồ Sơ Bảo Lãnh</div>
                            <div class="fs-4 fw-bold text-dark">{{ $stats['total_gop'] }}</div>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                            <i class="bi bi-folder2-open fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-success border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Đã Chấp Thuận GOP</div>
                            <div class="fs-4 fw-bold text-success">{{ $stats['approved_count'] }}</div>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                            <i class="bi bi-shield-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-info border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Tổng Tiền Đã Bảo Lãnh</div>
                            <div class="fs-5 fw-bold text-info">{{ number_format($stats['total_approved_amount'], 0, ',', '.') }} đ</div>
                        </div>
                        <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                            <i class="bi bi-cash-stack fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-warning border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Chờ Phê Duyệt GOP</div>
                            <div class="fs-4 fw-bold text-warning">{{ $stats['pending_count'] }}</div>
                        </div>
                        <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                            <i class="bi bi-hourglass-split fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('reception.private-insurance.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control border-start-0" placeholder="Tìm theo Số GOP, Số thẻ bảo hiểm, Tên BN, Hãng bảo hiểm...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Đã bảo lãnh</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Chờ phê duyệt</option>
                        <option value="settled" {{ $status === 'settled' ? 'selected' : '' }}>Đã quyết toán</option>
                        <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Từ chối</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-3">
                        <i class="bi bi-funnel me-1"></i> Lọc
                    </button>
                    @if($search || $status)
                        <a href="{{ route('reception.private-insurance.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Đặt lại
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
                            <th class="ps-3 py-3">Số Thư GOP</th>
                            <th>Bệnh Nhân</th>
                            <th>Hạng VIP</th>
                            <th>Đơn Vị Bảo Hiểm / TPA</th>
                            <th>Số Thẻ Bảo Hiểm</th>
                            <th>Số Tiền Bảo Lãnh</th>
                            <th>Bệnh Nhân Tự Trả</th>
                            <th>Trạng Thái</th>
                            <th class="text-end pe-3">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($guarantees as $g)
                            <tr>
                                <td class="ps-3 fw-bold text-primary font-monospace">{{ $g->gop_number }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $g->patientProfile->full_name }}</div>
                                    <small class="text-muted">{{ $g->patientProfile->patient_code }} | {{ $g->patientProfile->phone }}</small>
                                </td>
                                <td>{!! $g->vip_badge !!}</td>
                                <td class="fw-semibold text-secondary">{{ $g->tpa_company }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace">{{ $g->card_number }}</span>
                                </td>
                                <td class="fw-bold text-success">{{ number_format($g->approved_amount, 0, ',', '.') }} VNĐ</td>
                                <td class="text-danger fw-semibold">{{ number_format($g->patient_copay_amount, 0, ',', '.') }} VNĐ</td>
                                <td>{!! $g->status_badge !!}</td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('reception.private-insurance.show', $g) }}" class="btn btn-outline-primary" title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('reception.private-insurance.print', $g) }}" target="_blank" class="btn btn-outline-secondary" title="In thư GOP">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-shield-x fs-1 d-block mb-2"></i>
                                    Chưa có thư bảo lãnh viện phí nào được tiếp nhận.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($guarantees->hasPages())
                <div class="p-3 border-top">
                    {{ $guarantees->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
