@extends('layouts.admin')

@section('title', 'Quản Lý Giao Thuốc Ngoại Trú Tận Nhà')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-slate-800">
                <i class="bi bi-box2-heart-fill text-primary me-2"></i>Quản Lý Giao Thuốc Ngoại Trú Tận Nhà & Bệnh Mãn Tính
            </h4>
            <p class="text-muted small mb-0">Điều phối đóng gói bưu phẩm dược, kiểm soát vận chuyển chuỗi lạnh (Cold-chain 2-8°C) và in phiếu vận đơn chuẩn.</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#newDeliveryModal">
                <i class="bi bi-plus-circle-fill"></i> Tạo Đơn Giao Thuốc Mới
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Stat Bar -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm p-3 border-start border-primary border-4">
                <span class="text-muted small d-block">Tổng đơn giao</span>
                <h3 class="fw-bold mb-0 text-slate-800">{{ $stats['total'] }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm p-3 border-start border-warning border-4">
                <span class="text-muted small d-block">Chờ đóng gói</span>
                <h3 class="fw-bold mb-0 text-warning">{{ $stats['pending_packaging'] }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm p-3 border-start border-info border-4">
                <span class="text-muted small d-block">Đang trên đường giao</span>
                <h3 class="fw-bold mb-0 text-info">{{ $stats['in_transit'] }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm p-3 border-start border-success border-4">
                <span class="text-muted small d-block">Giao thành công</span>
                <h3 class="fw-bold mb-0 text-success">{{ $stats['delivered'] }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm p-3 border-start border-danger border-4">
                <span class="text-muted small d-block">Bảo quản lạnh (2-8°C)</span>
                <h3 class="fw-bold mb-0 text-danger"><i class="bi bi-snow2 me-1"></i>{{ $stats['cold_chain'] }}</h3>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <form action="{{ route('reception.deliveries.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Tìm theo Mã vận đơn, Mã tracking, Người nhận, SĐT..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="pending_packaging" {{ request('status') == 'pending_packaging' ? 'selected' : '' }}>Chờ dược sĩ đóng gói</option>
                        <option value="packaged" {{ request('status') == 'packaged' ? 'selected' : '' }}>Đã đóng gói - Chờ shipper</option>
                        <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>Đang giao hàng</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Giao thành công</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="carrier" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Đơn vị vận chuyển --</option>
                        <option value="tam_anh_express" {{ request('carrier') == 'tam_anh_express' ? 'selected' : '' }}>Tâm Anh Express</option>
                        <option value="viettel_post" {{ request('carrier') == 'viettel_post' ? 'selected' : '' }}>Viettel Post</option>
                        <option value="grab_express" {{ request('carrier') == 'grab_express' ? 'selected' : '' }}>GrabExpress</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100">Lọc</button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th>Mã Vận Đơn</th>
                        <th>Người Nhận / Địa Chỉ</th>
                        <th>Đơn Vị VC / Cước</th>
                        <th>Bảo Quản Lạnh</th>
                        <th>Trạng Thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveries as $del)
                        <tr>
                            <td>
                                <strong class="text-primary font-monospace d-block">{{ $del->tracking_code }}</strong>
                                <small class="text-muted">Vận đơn: <code>{{ $del->waybill_number }}</code></small>
                            </td>
                            <td>
                                <strong class="text-dark d-block">{{ $del->recipient_name }}</strong>
                                <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $del->recipient_phone }}</small>
                                <p class="text-muted small mb-0 text-truncate" style="max-width: 250px;" title="{{ $del->delivery_address }}">{{ $del->delivery_address }}</p>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $del->carrier_name_formatted }}</span>
                                <small class="text-success fw-bold d-block mt-1">{{ number_format($del->shipping_fee) }} VNĐ</small>
                            </td>
                            <td>
                                @if($del->requires_cold_chain)
                                    <span class="badge bg-info text-dark animate-pulse"><i class="bi bi-snow2 me-1"></i>Lạnh 2°C - 8°C</span>
                                @else
                                    <span class="badge bg-light text-muted border">Nhiệt độ thường</span>
                                @endif
                            </td>
                            <td>
                                {!! $del->status_badge !!}
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    @if($del->status === 'pending_packaging')
                                        <form action="{{ route('reception.deliveries.status', $del) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="status" value="packaged">
                                            <button type="submit" class="btn btn-warning" title="Xác nhận đã đóng gói xong">
                                                <i class="bi bi-box-seam me-1"></i>Đóng gói
                                            </button>
                                        </form>
                                    @elseif($del->status === 'packaged')
                                        <form action="{{ route('reception.deliveries.status', $del) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="status" value="in_transit">
                                            <button type="submit" class="btn btn-primary" title="Bàn giao shipper đi giao">
                                                <i class="bi bi-truck me-1"></i>Giao đi
                                            </button>
                                        </form>
                                    @elseif($del->status === 'in_transit')
                                        <form action="{{ route('reception.deliveries.status', $del) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="status" value="delivered">
                                            <button type="submit" class="btn btn-success" title="Xác nhận đã giao thành công">
                                                <i class="bi bi-check2-circle me-1"></i>Đã giao
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('reception.deliveries.waybill', $del) }}" target="_blank" class="btn btn-outline-dark" title="In phiếu vận đơn dán hộp bưu phẩm">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                    <a href="{{ route('public.delivery.tracking', $del->tracking_code) }}" target="_blank" class="btn btn-outline-info" title="Xem trang theo dõi lộ trình của bệnh nhân">
                                        <i class="bi bi-geo-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-box-seam fs-1 d-block mb-2 text-secondary"></i>
                                Chưa có đơn giao thuốc tận nhà nào trong danh sách.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($deliveries->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $deliveries->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Tạo Đơn Giao Thuốc Mới -->
<div class="modal fade" id="newDeliveryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('reception.deliveries.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-box2-heart-fill me-2"></i>Đăng Ký Giao Thuốc Tận Nhà Cho Bệnh Nhân
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Chọn đơn thuốc cần giao <span class="text-danger">*</span></label>
                            <select name="medical_record_id" class="form-select" required>
                                <option value="">-- Chọn đơn thuốc của bệnh nhân --</option>
                                @foreach($recentRecords as $rec)
                                    <option value="{{ $rec->id }}">
                                        {{ $rec->record_code }} - {{ $rec->patientProfile->full_name }} ({{ $rec->prescriptions->count() }} loại thuốc - BS: {{ $rec->doctor?->user->name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Họ tên người nhận <span class="text-danger">*</span></label>
                            <input type="text" name="recipient_name" class="form-control" placeholder="Họ tên người nhận" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Số điện thoại liên hệ <span class="text-danger">*</span></label>
                            <input type="text" name="recipient_phone" class="form-control" placeholder="Số điện thoại" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Địa chỉ giao thuốc chi tiết <span class="text-danger">*</span></label>
                            <input type="text" name="delivery_address" class="form-control" placeholder="Số nhà, ngõ, tên đường, phường/xã, quận/huyện..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Đơn vị vận chuyển <span class="text-danger">*</span></label>
                            <select name="carrier_name" class="form-select" required>
                                <option value="tam_anh_express">Tâm Anh Express (Đội xe nội bộ bệnh viện)</option>
                                <option value="viettel_post">Viettel Post Chuyển Phát Dược Phẩm</option>
                                <option value="grab_express">GrabExpress Giao Siêu Tốc 2 Giờ</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Phí vận chuyển (VNĐ)</label>
                            <input type="number" name="shipping_fee" class="form-control" value="30000">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="requires_cold_chain" value="1" id="coldChainCheck">
                                <label class="form-check-label fw-semibold text-danger" for="coldChainCheck">
                                    <i class="bi bi-snow2 me-1"></i>Đơn hàng có thuốc cần bảo quản lạnh 2°C - 8°C (Insulin, Vắc-xin)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>Tạo Đơn Giao Thuốc</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
