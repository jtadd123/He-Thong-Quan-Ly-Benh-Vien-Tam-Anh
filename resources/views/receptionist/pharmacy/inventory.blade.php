@extends('layouts.admin')

@section('title', 'Quản Lý Kho Dược & Danh Mục Thuốc Ngoại Trú - Bệnh Viện Tâm Anh')

@section('content')
<x-page-header title="Kho Dược Ngoại Trú & Quản Lý Tồn Kho" subtitle="Quản lý danh mục thuốc formulary, theo dõi số lượng tồn kho theo thời gian thực, nhập kho và kiểm soát hạn sử dụng">
    <x-slot:actions>
        <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreateMedication">
            <i class="bi bi-plus-circle"></i> Thêm Thuốc Mới
        </button>
        <a href="{{ route('reception.pharmacy.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-capsule-pill"></i> Về Quầy Phát Thuốc
        </a>
    </x-slot:actions>
</x-page-header>

<!-- KPI Counters -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-4 border-primary p-3 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Danh Mục Thuốc</div>
                    <h3 class="fw-bold text-primary mb-0 mt-1 font-monospace">{{ $totalCount }} <small class="fs-6 text-muted fw-normal">mặt hàng</small></h3>
                    <small class="text-muted">Tổng số thuốc trong kho ngoại trú</small>
                </div>
                <div class="rounded-circle bg-primary-subtle p-3 text-primary">
                    <i class="bi bi-capsule fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-4 border-warning p-3 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Cảnh Báo Sắp Hết</div>
                    <h3 class="fw-bold text-warning mb-0 mt-1 font-monospace">{{ $lowStockCount }} <small class="fs-6 text-muted fw-normal">loại</small></h3>
                    <small class="text-muted">Dưới mức tồn kho tối thiểu</small>
                </div>
                <div class="rounded-circle bg-warning-subtle p-3 text-warning">
                    <i class="bi bi-exclamation-triangle fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-4 border-danger p-3 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Đã Hết Hàng</div>
                    <h3 class="fw-bold text-danger mb-0 mt-1 font-monospace">{{ $outOfStockCount }} <small class="fs-6 text-muted fw-normal">loại</small></h3>
                    <small class="text-muted">Tồn kho bằng 0 cần bổ sung gấp</small>
                </div>
                <div class="rounded-circle bg-danger-subtle p-3 text-danger">
                    <i class="bi bi-slash-circle fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-4 border-success p-3 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Giá Trị Tồn Kho</div>
                    <h4 class="fw-bold text-success mb-0 mt-1 font-monospace">{{ number_format($totalInventoryValue, 0, ',', '.') }} <small class="fs-6 text-muted fw-normal">đ</small></h4>
                    <small class="text-muted">Định giá tài sản kho dược</small>
                </div>
                <div class="rounded-circle bg-success-subtle p-3 text-success">
                    <i class="bi bi-cash-stack fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Search Toolbar -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('reception.pharmacy.inventory.index') }}" class="row g-2 align-items-center">
            <!-- Status Tabs / Buttons -->
            <div class="col-md-7">
                <div class="btn-group btn-group-sm w-100 flex-wrap" role="group">
                    <a href="{{ route('reception.pharmacy.inventory.index', ['status' => 'all', 'q' => $search]) }}" class="btn {{ $status === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        Tất cả ({{ $totalCount }})
                    </a>
                    <a href="{{ route('reception.pharmacy.inventory.index', ['status' => 'fefo', 'q' => $search]) }}" class="btn {{ $status === 'fefo' ? 'btn-dark text-white fw-bold' : 'btn-outline-secondary' }}" title="Sắp xếp ưu tiên thuốc hạn dùng gần nhất xuất trước">
                        <i class="bi bi-arrow-down-up me-1"></i> Chuẩn FEFO
                    </a>
                    <a href="{{ route('reception.pharmacy.inventory.index', ['status' => 'expiring_soon', 'q' => $search]) }}" class="btn {{ $status === 'expiring_soon' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary' }}">
                        <i class="bi bi-clock-history me-1"></i> Cận hạn ({{ $expiringSoonCount }})
                    </a>
                    @if(($expiredCount ?? 0) > 0)
                        <a href="{{ route('reception.pharmacy.inventory.index', ['status' => 'expired', 'q' => $search]) }}" class="btn {{ $status === 'expired' ? 'btn-danger text-white fw-bold' : 'btn-outline-danger' }}">
                            <i class="bi bi-x-octagon-fill me-1"></i> Hết hạn ({{ $expiredCount }})
                        </a>
                    @endif
                    <a href="{{ route('reception.pharmacy.inventory.index', ['status' => 'low_stock', 'q' => $search]) }}" class="btn {{ $status === 'low_stock' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary' }}">
                        Sắp hết ({{ $lowStockCount }})
                    </a>
                    <a href="{{ route('reception.pharmacy.inventory.index', ['status' => 'out_of_stock', 'q' => $search]) }}" class="btn {{ $status === 'out_of_stock' ? 'btn-danger text-white fw-bold' : 'btn-outline-secondary' }}">
                        Hết hàng ({{ $outOfStockCount }})
                    </a>
                </div>
                <input type="hidden" name="status" value="{{ $status }}">
            </div>

            <!-- Search Bar -->
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Tìm kiếm theo mã thuốc, tên biệt dược, hoạt chất hoặc số lô...">
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="bi bi-search"></i> Tìm kiếm
                    </button>
                    @if(!empty($search) || $status !== 'all')
                        <a href="{{ route('reception.pharmacy.inventory.index') }}" class="btn btn-outline-secondary" title="Đặt lại bộ lọc">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Medication Inventory Table -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary small text-uppercase">
                    <tr>
                        <th class="ps-3" style="width: 120px;">Mã Thuốc</th>
                        <th>Tên Biệt Dược & Hoạt Chất</th>
                        <th style="width: 130px;">Dạng Bào Chế</th>
                        <th class="text-center" style="width: 140px;">Tồn Kho</th>
                        <th class="text-end" style="width: 130px;">Đơn Giá</th>
                        <th style="width: 160px;">Lô & Hạn Dùng</th>
                        <th class="text-end pe-3" style="width: 160px;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($medications as $med)
                        <tr>
                            <!-- Mã thuốc -->
                            <td class="ps-3">
                                <span class="badge bg-light text-dark font-monospace border px-2 py-1">
                                    {{ $med->medication_code }}
                                </span>
                            </td>

                            <!-- Tên thuốc & hoạt chất -->
                            <td>
                                <div class="fw-bold text-dark fs-6">{{ $med->name }}</div>
                                @if($med->active_ingredient)
                                    <div class="text-muted small">
                                        <i class="bi bi-info-circle me-1"></i>Hoạt chất: {{ $med->active_ingredient }}
                                    </div>
                                @endif
                                @if($med->instructions_default)
                                    <div class="text-muted small fst-italic">
                                        {{ $med->instructions_default }}
                                    </div>
                                @endif
                            </td>

                            <!-- Dạng bào chế -->
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary border">
                                    {{ $med->dosage_form }}
                                </span>
                            </td>

                            <!-- Số lượng tồn kho & Badge cảnh báo -->
                            <td class="text-center">
                                <div class="font-monospace fw-bold fs-6">
                                    {{ number_format($med->stock_quantity) }} <small class="text-muted fw-normal">{{ $med->unit }}</small>
                                </div>
                                @if($med->isOutOfStock())
                                    <span class="badge bg-danger-subtle text-danger border border-danger small">Hết hàng</span>
                                @elseif($med->isLowStock())
                                    <span class="badge bg-warning-subtle text-warning border border-warning small">Sắp hết (≤ {{ $med->min_stock_alert }})</span>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success small">Đủ cơ số</span>
                                @endif
                            </td>

                            <!-- Đơn giá -->
                            <td class="text-end font-monospace">
                                <span class="fw-bold text-primary">{{ $med->formatted_price }}</span>
                                <div class="text-muted small">/ {{ $med->unit }}</div>
                            </td>

                            <!-- Số lô & Hạn dùng (FEFO Tracker) -->
                            <td>
                                <div class="small mb-1">
                                    <span class="text-muted">Lô SX:</span> <span class="font-monospace fw-bold text-dark">{{ $med->batch_number ?? '---' }}</span>
                                </div>
                                <div>
                                    {!! $med->fefo_badge !!}
                                </div>
                            </td>

                            <!-- Thao tác nghiệp vụ -->
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <!-- Nút Nhập kho -->
                                    <button type="button" class="btn btn-outline-success" title="Nhập thêm thuốc vào kho"
                                            data-bs-toggle="modal" data-bs-target="#modalRestock{{ $med->id }}">
                                        <i class="bi bi-box-arrow-in-down"></i> Nhập
                                    </button>

                                    <!-- Nút Sửa -->
                                    <button type="button" class="btn btn-outline-primary" title="Sửa thông tin thuốc"
                                            data-bs-toggle="modal" data-bs-target="#modalEdit{{ $med->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    <!-- Nút Xóa -->
                                    <button type="button" class="btn btn-outline-danger" title="Xóa khỏi danh mục"
                                            data-bs-toggle="modal" data-bs-target="#modalDelete{{ $med->id }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Nhập Kho Thuốc Nhanh (Restock) -->
                        <div class="modal fade" id="modalRestock{{ $med->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <form action="{{ route('reception.pharmacy.inventory.restock', $med) }}" method="POST">
                                        @csrf
                                        <div class="modal-header bg-success text-white py-3">
                                            <h5 class="modal-title fs-6 fw-bold">
                                                <i class="bi bi-box-arrow-in-down me-2"></i>Nhập Thêm Thuốc Vào Kho
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="alert alert-light border d-flex align-items-center mb-3">
                                                <i class="bi bi-capsule fs-3 text-success me-3"></i>
                                                <div>
                                                    <div class="fw-bold">{{ $med->name }}</div>
                                                    <div class="small text-muted">Mã: {{ $med->medication_code }} | Tồn hiện tại: <strong class="text-dark">{{ $med->stock_quantity }} {{ $med->unit }}</strong></div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold small text-uppercase">Số Lượng Nhập Thêm <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="number" name="quantity" class="form-control form-control-lg font-monospace fw-bold text-success" min="1" max="100000" placeholder="VD: 500" required>
                                                    <span class="input-group-text">{{ $med->unit }}</span>
                                                </div>
                                                <div class="form-text">Số lượng này sẽ được cộng dồn trực tiếp vào cơ số tồn kho hiện hành.</div>
                                            </div>

                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <label class="form-label small fw-bold">Số Lô Sản Xuất</label>
                                                    <input type="text" name="batch_number" class="form-control font-monospace" value="{{ $med->batch_number }}" placeholder="VD: L261028">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small fw-bold">Hạn Sử Dụng Mới</label>
                                                    <input type="date" name="expiry_date" class="form-control" value="{{ $med->expiry_date ? $med->expiry_date->format('Y-m-d') : '' }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light py-2">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                                            <button type="submit" class="btn btn-success btn-sm px-3 fw-bold">
                                                <i class="bi bi-check2 me-1"></i> Xác Nhận Nhập Kho
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Sửa Thông Tin Thuốc -->
                        <div class="modal fade" id="modalEdit{{ $med->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <form action="{{ route('reception.pharmacy.inventory.update', $med) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header bg-primary text-white py-3">
                                            <h5 class="modal-title fs-6 fw-bold">
                                                <i class="bi bi-pencil-square me-2"></i>Cập Nhật Thông Tin Thuốc: {{ $med->name }}
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="row g-3">
                                                <div class="col-md-7">
                                                    <label class="form-label fw-bold small">Tên Biệt Dược & Hàm Lượng <span class="text-danger">*</span></label>
                                                    <input type="text" name="name" class="form-control" value="{{ old('name', $med->name) }}" required>
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label fw-bold small">Hoạt Chất Chính</label>
                                                    <input type="text" name="active_ingredient" class="form-control" value="{{ old('active_ingredient', $med->active_ingredient) }}">
                                                </div>

                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold small">Dạng Bào Chế <span class="text-danger">*</span></label>
                                                    <input type="text" name="dosage_form" class="form-control" value="{{ old('dosage_form', $med->dosage_form) }}" placeholder="VD: Viên nén bao phim" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold small">Đơn Vị Tính <span class="text-danger">*</span></label>
                                                    <input type="text" name="unit" class="form-control" value="{{ old('unit', $med->unit) }}" placeholder="VD: Viên, Gói, Chai" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold small">Ngưỡng Báo Sắp Hết <span class="text-danger">*</span></label>
                                                    <input type="number" name="min_stock_alert" class="form-control" value="{{ old('min_stock_alert', $med->min_stock_alert) }}" min="0" required>
                                                </div>

                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold small">Đơn Giá (VNĐ) <span class="text-danger">*</span></label>
                                                    <input type="number" name="unit_price" class="form-control font-monospace" value="{{ old('unit_price', $med->unit_price) }}" min="0" step="100" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold small">Số Lô Sản Xuất</label>
                                                    <input type="text" name="batch_number" class="form-control font-monospace" value="{{ old('batch_number', $med->batch_number) }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold small">Hạn Sử Dụng</label>
                                                    <input type="date" name="expiry_date" class="form-control" value="{{ old('expiry_date', $med->expiry_date ? $med->expiry_date->format('Y-m-d') : '') }}">
                                                </div>

                                                <div class="col-12">
                                                    <hr class="my-2">
                                                    <div class="small fw-bold text-muted mb-2 text-uppercase">Liều Dùng Mặc Định (Gợi ý cho Bác sĩ kê đơn)</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small">Liều lượng</label>
                                                    <input type="text" name="dosage_default" class="form-control form-control-sm" value="{{ old('dosage_default', $med->dosage_default) }}" placeholder="VD: 1 viên">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small">Tần suất</label>
                                                    <input type="text" name="frequency_default" class="form-control form-control-sm" value="{{ old('frequency_default', $med->frequency_default) }}" placeholder="VD: 2 lần/ngày">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small">Số ngày</label>
                                                    <input type="number" name="duration_default" class="form-control form-control-sm" value="{{ old('duration_default', $med->duration_default) }}" min="1">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label small">Lời dặn / Hướng dẫn uống</label>
                                                    <input type="text" name="instructions_default" class="form-control form-control-sm" value="{{ old('instructions_default', $med->instructions_default) }}" placeholder="VD: Uống sau ăn no, uống nhiều nước">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light py-2">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                                            <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold">
                                                <i class="bi bi-save me-1"></i> Lưu Cập Nhật
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Xóa Thuốc -->
                        <div class="modal fade" id="modalDelete{{ $med->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <form action="{{ route('reception.pharmacy.inventory.destroy', $med) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <div class="modal-header bg-danger text-white py-3">
                                            <h5 class="modal-title fs-6 fw-bold">
                                                <i class="bi bi-exclamation-triangle me-2"></i>Xác Nhận Xóa Thuốc
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4 text-center">
                                            <i class="bi bi-trash fs-1 text-danger mb-3 d-block"></i>
                                            <p class="mb-1">Bạn có chắc chắn muốn xóa thuốc <strong class="text-danger">{{ $med->name }}</strong> khỏi danh mục kho dược?</p>
                                            <small class="text-muted">Mã: {{ $med->medication_code }} | Tồn kho hiện tại: {{ $med->stock_quantity }} {{ $med->unit }}</small>
                                        </div>
                                        <div class="modal-footer bg-light py-2 justify-content-center">
                                            <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Hủy</button>
                                            <button type="submit" class="btn btn-danger btn-sm px-3 fw-bold">
                                                <i class="bi bi-trash me-1"></i> Xóa Vĩnh Viễn
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-capsule fs-1 text-secondary opacity-50 mb-3 d-block"></i>
                                    <h5 class="fw-bold">Không tìm thấy thuốc nào trong danh mục</h5>
                                    <p class="small text-muted mb-3">Thử thay đổi từ khóa tìm kiếm hoặc lọc theo trạng thái khác.</p>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreateMedication">
                                        <i class="bi bi-plus-circle me-1"></i> Thêm Thuốc Mới
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($medications->hasPages())
        <div class="card-footer bg-white border-top-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                    Hiển thị <strong>{{ $medications->firstItem() }}</strong> đến <strong>{{ $medications->lastItem() }}</strong> trong tổng số <strong>{{ $medications->total() }}</strong> thuốc
                </div>
                {{ $medications->links() }}
            </div>
        </div>
    @endif
</div>

<!-- Modal Thêm Thuốc Mới Vào Danh Mục (Create) -->
<div class="modal fade" id="modalCreateMedication" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('reception.pharmacy.inventory.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fs-6 fw-bold">
                        <i class="bi bi-plus-circle me-2"></i>Thêm Thuốc Mới Vào Danh Mục Formulary
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label fw-bold small">Tên Biệt Dược & Hàm Lượng <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="VD: Augmentin 1g, Panadol Extra 500mg" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold small">Hoạt Chất Chính</label>
                            <input type="text" name="active_ingredient" class="form-control" placeholder="VD: Amoxicillin + Clavulanic acid">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Dạng Bào Chế <span class="text-danger">*</span></label>
                            <select name="dosage_form" class="form-select" required>
                                <option value="Viên nén bao phim">Viên nén bao phim</option>
                                <option value="Viên nang">Viên nang</option>
                                <option value="Viên sủi">Viên sủi</option>
                                <option value="Gói bột pha hỗn dịch">Gói bột pha hỗn dịch</option>
                                <option value="Chai siro">Chai siro</option>
                                <option value="Dung dịch nhỏ mắt/mũi">Dung dịch nhỏ mắt/mũi</option>
                                <option value="Tuýp gel bôi ngoài">Tuýp gel bôi ngoài</option>
                                <option value="Ống tiêm">Ống tiêm</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Đơn Vị Tính <span class="text-danger">*</span></label>
                            <select name="unit" class="form-select" required>
                                <option value="Viên">Viên</option>
                                <option value="Gói">Gói</option>
                                <option value="Chai">Chai</option>
                                <option value="Lọ">Lọ</option>
                                <option value="Tuýp">Tuýp</option>
                                <option value="Ống">Ống</option>
                                <option value="Hộp">Hộp</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Đơn Giá Niêm Yết (VNĐ) <span class="text-danger">*</span></label>
                            <input type="number" name="unit_price" class="form-control font-monospace" placeholder="VD: 15000" min="0" step="100" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Số Lượng Tồn Ban Đầu <span class="text-danger">*</span></label>
                            <input type="number" name="stock_quantity" class="form-control font-monospace" placeholder="VD: 500" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Ngưỡng Báo Sắp Hết <span class="text-danger">*</span></label>
                            <input type="number" name="min_stock_alert" class="form-control font-monospace" value="50" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Số Lô Sản Xuất</label>
                            <input type="text" name="batch_number" class="form-control font-monospace" placeholder="VD: L261028">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Hạn Sử Dụng</label>
                            <input type="date" name="expiry_date" class="form-control">
                        </div>

                        <div class="col-12">
                            <hr class="my-2">
                            <div class="small fw-bold text-muted mb-2 text-uppercase">Cấu Hình Liều Dùng Mặc Định (Hỗ trợ Bác sĩ kê đơn 1-Click)</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Liều dùng mặc định</label>
                            <input type="text" name="dosage_default" class="form-control form-control-sm" placeholder="VD: 1 viên">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Tần suất mặc định</label>
                            <input type="text" name="frequency_default" class="form-control form-control-sm" placeholder="VD: 2 lần/ngày (sáng, tối)">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Số ngày mặc định</label>
                            <input type="number" name="duration_default" class="form-control form-control-sm" value="5" min="1">
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Lời dặn sử dụng thuốc</label>
                            <input type="text" name="instructions_default" class="form-control form-control-sm" placeholder="VD: Uống sau bữa ăn no 15 phút, uống nhiều nước">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold">
                        <i class="bi bi-plus-circle me-1"></i> Lưu Vào Danh Mục
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
