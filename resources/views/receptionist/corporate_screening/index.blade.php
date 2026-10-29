@extends('layouts.admin')

@section('title', 'Khám Sức Khỏe Đoàn & Doanh Nghiệp (Thông tư 32/2023/TT-BYT)')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-primary">
                <i class="bi bi-buildings-fill me-2"></i>Khám Sức Khỏe Đoàn & Doanh Nghiệp
            </h4>
            <p class="text-muted small mb-0">Quản lý hợp đồng khám định kỳ, điều phối luồng khám và phân loại sức khỏe Thông tư 32/2023/TT-BYT</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newContractModal">
                <i class="bi bi-plus-circle"></i> Khởi Tạo Hợp Đồng Khám Mới
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-primary border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Tổng Số Hợp Đồng</div>
                            <div class="fs-4 fw-bold text-dark">{{ $stats['total_contracts'] }}</div>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                            <i class="bi bi-file-earmark-text-fill fs-4"></i>
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
                            <div class="text-muted small fw-semibold">Hợp Đồng Đang Triển Khai</div>
                            <div class="fs-4 fw-bold text-success">{{ $stats['active_contracts'] }}</div>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                            <i class="bi bi-play-circle-fill fs-4"></i>
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
                            <div class="text-muted small fw-semibold">Tổng Cán Bộ Nhân Viên</div>
                            <div class="fs-4 fw-bold text-info">{{ number_format($stats['total_employees']) }} người</div>
                        </div>
                        <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                            <i class="bi bi-people-fill fs-4"></i>
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
                            <div class="text-muted small fw-semibold">Đã Khám & Phân Loại</div>
                            <div class="fs-4 fw-bold text-warning">{{ number_format($stats['completed_screenings']) }}</div>
                        </div>
                        <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                            <i class="bi bi-check2-all fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3">Mã Hợp Đồng</th>
                            <th>Doanh Nghiệp / Cơ Quan</th>
                            <th>Gói Khám Sức Khỏe</th>
                            <th>Thời Gian Khám</th>
                            <th>Quy Mô Đoàn</th>
                            <th>Tiến Độ Khám</th>
                            <th>Trạng Thái</th>
                            <th class="text-end pe-3">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contracts as $c)
                            <tr>
                                <td class="ps-3 fw-bold text-primary font-monospace">{{ $c->contract_code }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $c->company_name }}</div>
                                    <small class="text-muted">Đại diện: {{ $c->contact_person }} | {{ $c->contact_phone }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $c->package_name }}</span>
                                    <div class="small text-muted">{{ number_format($c->cost_per_person, 0, ',', '.') }} đ/người</div>
                                </td>
                                <td class="small">
                                    {{ $c->checkup_start_date->format('d/m/Y') }} &rarr; {{ $c->checkup_end_date->format('d/m/Y') }}
                                </td>
                                <td class="fw-bold">{{ number_format($c->total_employees) }} người</td>
                                <td>
                                    <div class="small fw-semibold mb-1">{{ $c->completed_count }} / {{ $c->employees_count }} người</div>
                                    <div class="progress" style="height: 6px;">
                                        @php
                                            $pct = $c->employees_count > 0 ? round(($c->completed_count / $c->employees_count) * 100) : 0;
                                        @endphp
                                        <div class="progress-bar bg-success" style="width: {{ $pct }}%"></div>
                                    </div>
                                </td>
                                <td>{!! $c->status_badge !!}</td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('reception.corporate-screening.show', $c) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-person-lines-fill me-1"></i> Danh Sách Đoàn
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-buildings fs-1 d-block mb-2"></i>
                                    Chưa có hợp đồng khám sức khỏe đoàn nào được khởi tạo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($contracts->hasPages())
                <div class="p-3 border-top">{{ $contracts->links() }}</div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Khởi Tạo Hợp Đồng Mới -->
<div class="modal fade" id="newContractModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('reception.corporate-screening.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-buildings-fill me-2"></i>Khởi Tạo Hợp Đồng Khám Doanh Nghiệp</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tên doanh nghiệp / Công ty <span class="text-danger">*</span></label>
                        <input type="text" name="company_name" class="form-control" placeholder="VD: Tập đoàn Viettel / Ngân hàng MB..." required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mã số thuế</label>
                            <input type="text" name="company_tax_code" class="form-control" placeholder="VD: 0100109106">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Quy mô nhân sự (Người) <span class="text-danger">*</span></label>
                            <input type="number" name="total_employees" value="150" min="1" class="form-control" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Người liên hệ <span class="text-danger">*</span></label>
                            <input type="text" name="contact_person" class="form-control" placeholder="VD: Chị Mai (Trưởng phòng HCNS)" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">SĐT liên hệ <span class="text-danger">*</span></label>
                            <input type="text" name="contact_phone" class="form-control" placeholder="VD: 0988776655" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ngày bắt đầu khám <span class="text-danger">*</span></label>
                            <input type="date" name="checkup_start_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ngày kết thúc <span class="text-danger">*</span></label>
                            <input type="date" name="checkup_end_date" value="{{ date('Y-m-d', strtotime('+14 days')) }}" class="form-control" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Gói khám lựa chọn <span class="text-danger">*</span></label>
                            <select name="package_name" class="form-select" required>
                                <option value="Gói Khám Tiêu Chuẩn Thông Tư 32/2023/TT-BYT">Tiêu Chuẩn TT 32/2023/TT-BYT</option>
                                <option value="Gói Khám Chuyên Sâu Tầm Soát Ung Thư">Chuyên Sâu Tầm Soát Ung Thư</option>
                                <option value="Gói Khám Ban Giám Đốc VIP">Ban Giám Đốc VIP</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Chi phí / Người (VNĐ) <span class="text-danger">*</span></label>
                            <input type="number" name="cost_per_person" value="1800000" min="0" step="50000" class="form-control fw-bold text-success" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary fw-bold">Khởi Tạo Hợp Đồng</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
