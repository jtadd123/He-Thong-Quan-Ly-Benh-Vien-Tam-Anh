@extends('layouts.admin')

@section('title', 'Quản Lý Xe Lăn & Thiết Bị Di Chuyển Hỗ Trợ - BVĐK Tâm Anh')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-slate-800">
                <i class="bi bi-person-wheelchair text-primary me-2"></i>Quản Lý Xe Lăn & Thiết Bị Di Chuyển Hỗ Trợ
            </h4>
            <p class="text-muted small mb-0">Theo dõi trạng thái, quản lý mượn trả xe lăn/cáng đẩy tại sảnh và kiểm soát quy trình khử khuẩn nhiễm khuẩn bệnh viện.</p>
        </div>
        <div>
            <button class="btn btn-primary fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalBorrowFast">
                <i class="bi bi-qr-code-scan me-1"></i> Cho Mượn Xe Lăn Nhanh
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-x-octagon-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-primary border-4">
                <span class="text-muted small d-block">Tổng Thiết Bị</span>
                <h3 class="fw-bold mb-0 text-slate-800">{{ $stats['total'] }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-success border-4">
                <span class="text-muted small d-block">Sẵn Sàng Tại Sảnh (Đã Khử Khuẩn)</span>
                <h3 class="fw-bold mb-0 text-success">{{ $stats['available'] }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-warning border-4">
                <span class="text-muted small d-block">Bệnh Nhân Đang Mượn</span>
                <h3 class="fw-bold mb-0 text-warning">{{ $stats['in_use'] }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-info border-4">
                <span class="text-muted small d-block">Đang Trong Buồng Khử Khuẩn</span>
                <h3 class="fw-bold mb-0 text-info">{{ $stats['disinfecting'] }}</h3>
            </div>
        </div>
    </div>

    <!-- Interactive Equipment Grid -->
    <div class="card border-0 shadow-sm p-4 mb-4">
        <h6 class="fw-bold text-slate-800 border-bottom pb-3 mb-3">
            <i class="bi bi-grid-3x3-gap-fill text-primary me-2"></i>Sơ Đồ Hiện Trạng Xe Lăn & Cáng Đẩy
        </h6>

        <div class="row g-3">
            @foreach($equipments as $eq)
                <div class="col-md-6 col-xl-4">
                    <div class="border rounded-3 p-3 h-100 position-relative {{ $eq->status === 'available' ? 'border-success-subtle bg-light' : ($eq->status === 'in_use' ? 'border-warning-subtle' : 'border-info-subtle') }}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge {{ $eq->status === 'available' ? 'bg-success' : ($eq->status === 'in_use' ? 'bg-warning text-dark' : 'bg-info text-dark') }} mb-1">
                                    @if($eq->status === 'available')
                                        <i class="bi bi-check-circle-fill me-1"></i>SẴN SÀNG
                                    @elseif($eq->status === 'in_use')
                                        <i class="bi bi-person-fill me-1"></i>ĐANG MƯỢN
                                    @elseif($eq->status === 'disinfecting')
                                        <i class="bi bi-moisture me-1"></i>ĐANG KHỬ KHUẨN
                                    @else
                                        <i class="bi bi-wrench me-1"></i>BẢO TRÌ
                                    @endif
                                </span>
                                <h6 class="fw-bold text-dark mb-0">{{ $eq->name }}</h6>
                                <span class="badge bg-secondary font-monospace" style="font-size: 0.75rem;">{{ $eq->device_code }}</span>
                            </div>
                            <span class="fs-2 text-muted">
                                @if($eq->device_type === 'wheelchair' || $eq->device_type === 'electric_wheelchair')
                                    <i class="bi bi-person-wheelchair text-primary"></i>
                                @elseif($eq->device_type === 'stretcher')
                                    <i class="bi bi-hospital text-danger"></i>
                                @else
                                    <i class="bi bi-shield-plus text-success"></i>
                                @endif
                            </span>
                        </div>

                        <div class="small text-muted mb-2">
                            <div><i class="bi bi-geo-alt me-1 text-danger"></i>Vị trí: <strong>{{ $eq->current_location }}</strong></div>
                            @if($eq->last_disinfected_at)
                                <div><i class="bi bi-shield-check me-1 text-success"></i>Khử khuẩn gần nhất: {{ $eq->last_disinfected_at->format('H:i d/m') }}</div>
                            @endif
                        </div>

                        @if($eq->status === 'in_use' && $activeLoan = $eq->activeLoan())
                            <div class="p-2 bg-warning-subtle rounded-2 small mb-3">
                                <strong>Người mượn:</strong> {{ $activeLoan->borrower_name }} ({{ $activeLoan->borrower_phone }})<br>
                                <strong>Di chuyển tới:</strong> {{ $activeLoan->destination_department }}
                            </div>
                            <form action="{{ route('reception.mobility.return', $activeLoan) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-warning w-100 fw-bold">
                                    <i class="bi bi-arrow-return-left me-1"></i> Tiếp Nhận Trả Xe
                                </button>
                            </form>
                        @elseif($eq->status === 'disinfecting')
                            <div class="p-2 bg-info-subtle rounded-2 small mb-3 text-info-emphasis">
                                Xe đang được sát khuẩn bề mặt chống lây nhiễm chéo.
                            </div>
                            <form action="{{ route('reception.mobility.disinfect', $eq) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-info text-white w-100 fw-bold shadow-sm">
                                    <i class="bi bi-check2-circle me-1"></i> Nghiệm Thu Đã Khử Khuẩn Xong
                                </button>
                            </form>
                        @elseif($eq->status === 'available')
                            <button class="btn btn-sm btn-outline-success w-100 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalBorrowItem{{ $eq->id }}">
                                <i class="bi bi-hand-index-thumb me-1"></i> Cấp Xe Nhanh
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Modal Cấp riêng xe này -->
                <div class="modal fade" id="modalBorrowItem{{ $eq->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('reception.mobility.borrow') }}" method="POST">
                                @csrf
                                <input type="hidden" name="mobility_equipment_id" value="{{ $eq->id }}">
                                <div class="modal-header">
                                    <h6 class="modal-title fw-bold">Cho Mượn: {{ $eq->name }} ({{ $eq->device_code }})</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Người mượn / Thân nhân <span class="text-danger">*</span></label>
                                        <input type="text" name="borrower_name" class="form-control" required placeholder="Họ và tên người mượn">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Số điện thoại liên hệ <span class="text-danger">*</span></label>
                                        <input type="text" name="borrower_phone" class="form-control" required placeholder="Số điện thoại di động">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Số CCCD / Giấy tờ giữ lại</label>
                                        <input type="text" name="id_card_held" class="form-control" placeholder="Số Căn cước công dân gắn chip hoặc Thẻ BHYT">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Khoa / Phòng dự kiến di chuyển tới <span class="text-danger">*</span></label>
                                        <input type="text" name="destination_department" class="form-control" required value="Khoa Khám Bệnh Chuyên Khoa">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                    <button type="submit" class="btn btn-primary fw-bold">Xác Nhận Bàn Giao Xe</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Loan History Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="fw-bold mb-0 text-slate-800">
                <i class="bi bi-journal-text me-1 text-primary"></i> Nhật Ký Mượn Trả Phương Tiện Gần Đây
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mã Phiếu</th>
                        <th>Thiết Bị</th>
                        <th>Người Mượn</th>
                        <th>Khoa Đến</th>
                        <th>Thời Gian Mượn</th>
                        <th>Thời Gian Trả</th>
                        <th>Trạng Thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLoans as $loan)
                        <tr>
                            <td><strong class="font-monospace text-primary">{{ $loan->loan_code }}</strong></td>
                            <td>
                                <strong>{{ $loan->equipment->name }}</strong>
                                <small class="text-muted d-block font-monospace">{{ $loan->equipment->device_code }}</small>
                            </td>
                            <td>
                                <strong class="text-dark">{{ $loan->borrower_name }}</strong>
                                <small class="text-muted d-block">{{ $loan->borrower_phone }} • {{ $loan->id_card_held ?: 'Không giữ CCCD' }}</small>
                            </td>
                            <td>{{ $loan->destination_department }}</td>
                            <td><small>{{ $loan->borrowed_at->format('H:i d/m/Y') }}</small></td>
                            <td>
                                @if($loan->returned_at)
                                    <small class="text-success">{{ $loan->returned_at->format('H:i d/m/Y') }}</small>
                                @else
                                    <span class="badge bg-warning text-dark">Chưa trả</span>
                                @endif
                            </td>
                            <td>
                                @if($loan->status === 'returned')
                                    <span class="badge bg-success">Đã trả</span>
                                @else
                                    <span class="badge bg-danger animate-pulse">Đang mượn</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Chưa có nhật ký mượn trả nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Mượn Nhanh Toàn Cục -->
<div class="modal fade" id="modalBorrowFast" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('reception.mobility.borrow') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">Cho Mượn Xe Lăn / Phương Tiện Nhanh</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Chọn thiết bị sẵn sàng <span class="text-danger">*</span></label>
                        <select name="mobility_equipment_id" class="form-select" required>
                            <option value="">-- Chọn xe lăn / cáng đẩy sẵn có --</option>
                            @foreach($equipments->where('status', 'available') as $eq)
                                <option value="{{ $eq->id }}">
                                    {{ $eq->device_code }} - {{ $eq->name }} ({{ $eq->current_location }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Họ tên người mượn / Bệnh nhân <span class="text-danger">*</span></label>
                        <input type="text" name="borrower_name" class="form-control" required placeholder="Họ và tên người mượn">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Số điện thoại liên hệ <span class="text-danger">*</span></label>
                        <input type="text" name="borrower_phone" class="form-control" required placeholder="09xxxxxxxx">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Khoa phòng dự kiến di chuyển tới <span class="text-danger">*</span></label>
                        <input type="text" name="destination_department" class="form-control" required value="Khoa Khám Bệnh Sảnh Tầng 1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary fw-bold">Xác Nhận Xuất Xe</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
