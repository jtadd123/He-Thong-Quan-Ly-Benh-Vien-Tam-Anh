@extends('layouts.admin')

@section('title', 'Cổng Đề Nghị Sao Lục Bệnh Án Điện Tử')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-primary">
                <i class="bi bi-file-earmark-lock2-fill me-2"></i>Cổng Đề Nghị Sao Lục Bệnh Án Điện Tử
            </h4>
            <p class="text-muted small mb-0">Tiếp nhận và phê duyệt trích sao hồ sơ bệnh án theo Luật Khám bệnh, chữa bệnh 2023 - Bệnh Viện Tâm Anh</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reception.record-requests.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-plus-circle"></i> Tiếp Nhận Đơn Xin Sao Lục Mới
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
                            <div class="text-muted small fw-semibold">Tổng Đơn Đề Nghị</div>
                            <div class="fs-4 fw-bold text-dark">{{ $stats['total'] }}</div>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                            <i class="bi bi-folder-fill fs-4"></i>
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
                            <div class="text-muted small fw-semibold">Đang Chờ Thẩm Tra</div>
                            <div class="fs-4 fw-bold text-warning">{{ $stats['pending'] }}</div>
                        </div>
                        <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                            <i class="bi bi-hourglass-split fs-4"></i>
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
                            <div class="text-muted small fw-semibold">Lãnh Đạo Đã Duyệt</div>
                            <div class="fs-4 fw-bold text-info">{{ $stats['approved'] }}</div>
                        </div>
                        <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                            <i class="bi bi-check2-circle fs-4"></i>
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
                            <div class="text-muted small fw-semibold">Đã Xuất Bản Sao Watermark</div>
                            <div class="fs-4 fw-bold text-success">{{ $stats['completed'] }}</div>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                            <i class="bi bi-printer-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('reception.record-requests.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control border-start-0" placeholder="Tìm theo Mã đơn, Người xin trích sao, CCCD, Tên người bệnh...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="submitted" {{ $status === 'submitted' ? 'selected' : '' }}>Chờ tiếp nhận</option>
                        <option value="reviewing" {{ $status === 'reviewing' ? 'selected' : '' }}>Đang thẩm tra</option>
                        <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Đã phê duyệt</option>
                        <option value="printed" {{ $status === 'printed' ? 'selected' : '' }}>Đã xuất bản sao</option>
                        <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Từ chối</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-3">
                        <i class="bi bi-funnel me-1"></i> Lọc
                    </button>
                    @if($search || $status)
                        <a href="{{ route('reception.record-requests.index') }}" class="btn btn-outline-secondary">
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
                            <th class="ps-3 py-3">Mã Đơn Đề Nghị</th>
                            <th>Bệnh Nhân Cần Sao Lục</th>
                            <th>Người Xin Trích Sao</th>
                            <th>Tư Cách Pháp Lý</th>
                            <th>Mục Đích Sử Dụng</th>
                            <th>Số Bản</th>
                            <th>Trạng Thái</th>
                            <th class="text-end pe-3">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $r)
                            <tr>
                                <td class="ps-3 fw-bold text-primary font-monospace">{{ $r->request_code }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $r->patientProfile->full_name }}</div>
                                    <small class="text-muted">{{ $r->patientProfile->patient_code }} | {{ $r->patientProfile->phone }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $r->requester_name }}</div>
                                    <small class="text-muted">CCCD: {{ $r->requester_identity_card }} | SĐT: {{ $r->requester_phone }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ match($r->requester_type) {
                                            'patient_self' => 'Người bệnh chính chủ',
                                            'guardian' => 'Thân nhân / Giám hộ',
                                            'law_enforcement' => 'Cơ quan tố tụng',
                                            default => 'Giám định viên bảo hiểm'
                                        } }}
                                    </span>
                                </td>
                                <td>
                                    <div class="small text-truncate" style="max-width: 220px;" title="{{ $r->purpose }}">{{ $r->purpose }}</div>
                                </td>
                                <td class="fw-bold">{{ $r->copies_count }} bản</td>
                                <td>{!! $r->status_badge !!}</td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('reception.record-requests.show', $r) }}" class="btn btn-outline-primary" title="Xem chi tiết & Thẩm định">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($r->status === 'approved' || $r->status === 'printed')
                                            <a href="{{ route('reception.record-requests.print', $r) }}" target="_blank" class="btn btn-outline-success" title="In bản sao watermark">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-folder-x fs-1 d-block mb-2"></i>
                                    Chưa có đơn đề nghị sao lục hồ sơ bệnh án nào.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($requests->hasPages())
                <div class="p-3 border-top">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
