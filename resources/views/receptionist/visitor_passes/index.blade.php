@extends('layouts.admin')

@section('title', 'Quản Lý Khách Thăm Bệnh & Kiểm Soát Nhiễm Khuẩn')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-primary">
                <i class="bi bi-person-badge-fill me-2"></i>Thẻ Thăm Bệnh Điện Tử & Kiểm Soát Nhiễm Khuẩn
            </h4>
            <p class="text-muted small mb-0">Quản lý người vào thăm, giới hạn số lượng và tầm soát nhiễm khuẩn khu điều trị theo chuẩn JCI - Bệnh Viện Tâm Anh</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newPassModal">
                <i class="bi bi-plus-circle"></i> Cấp Thẻ Thăm Bệnh Mới
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm border-start border-success border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Người Đang Thăm Bệnh</div>
                            <div class="fs-4 fw-bold text-success">{{ $stats['active_visitors'] }} người</div>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm border-start border-primary border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Đã Check-out Hôm Nay</div>
                            <div class="fs-4 fw-bold text-primary">{{ $stats['checked_out_today'] }} lượt</div>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                            <i class="bi bi-box-arrow-right fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm border-start border-secondary border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Tổng Thẻ Đã Phát Hành</div>
                            <div class="fs-4 fw-bold text-secondary">{{ $stats['total_passes'] }}</div>
                        </div>
                        <div class="bg-secondary bg-opacity-10 text-secondary p-3 rounded-circle">
                            <i class="bi bi-card-checklist fs-4"></i>
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
                            <th class="ps-3 py-3">Mã Thẻ Thăm</th>
                            <th>Người Vào Thăm</th>
                            <th>Bệnh Nhân Được Thăm</th>
                            <th>Khoa Phòng Thăm</th>
                            <th>Thời Hạn Vào Thăm</th>
                            <th>Sàng Lọc Nhiễm Khuẩn</th>
                            <th>Trạng Thái</th>
                            <th class="text-end pe-3">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($passes as $p)
                            <tr>
                                <td class="ps-3 fw-bold text-primary font-monospace">{{ $p->pass_code }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $p->visitor_name }}</div>
                                    <small class="text-muted">Quan hệ: <strong>{{ $p->relationship }}</strong> | SĐT: {{ $p->visitor_phone }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $p->patientProfile->full_name }}</div>
                                    <small class="text-muted">{{ $p->patientProfile->patient_code }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $p->target_department }}</span>
                                </td>
                                <td class="small">
                                    <div>{{ $p->valid_from->format('H:i') }} &rarr; {{ $p->valid_to->format('H:i d/m') }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-success"><i class="bi bi-shield-check me-1"></i>Đạt Chuẩn An Toàn</span>
                                </td>
                                <td>{!! $p->status_badge !!}</td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('reception.visitor-passes.print', $p) }}" target="_blank" class="btn btn-outline-secondary" title="In thẻ đeo QR">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        @if($p->status === 'active' && !$p->is_expired)
                                            <form action="{{ route('reception.visitor-passes.checkout', $p) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-outline-danger" title="Check-out khách ra về">
                                                    <i class="bi bi-box-arrow-right"></i> Check-out
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-person-x fs-1 d-block mb-2"></i>
                                    Chưa có khách thăm bệnh nào trong thời điểm hiện tại.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($passes->hasPages())
                <div class="p-3 border-top">{{ $passes->links() }}</div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Cấp Thẻ Thăm Bệnh Mới -->
<div class="modal fade" id="newPassModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('reception.visitor-passes.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-person-badge-fill me-2"></i>Cấp Thẻ Thăm Bệnh Điện Tử</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Người bệnh muốn thăm <span class="text-danger">*</span></label>
                        <select name="patient_profile_id" class="form-select" required>
                            <option value="">-- Chọn người bệnh đang điều trị --</option>
                            @foreach($patients as $pt)
                                <option value="{{ $pt->id }}">{{ $pt->full_name }} (Mã: {{ $pt->patient_code }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Lưu ý: Hệ thống tự động giới hạn tối đa 2 người thăm cùng một lúc.</small>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Họ tên người thăm <span class="text-danger">*</span></label>
                            <input type="text" name="visitor_name" class="form-control" placeholder="VD: Nguyễn Văn C" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" name="visitor_phone" class="form-control" placeholder="VD: 0912345678" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Số CCCD / CMND</label>
                            <input type="text" name="visitor_id_card" class="form-control" placeholder="VD: 00109200xxxx">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Quan hệ với người bệnh <span class="text-danger">*</span></label>
                            <input type="text" name="relationship" class="form-control" placeholder="VD: Bố / Mẹ / Bạn bè..." required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Khoa phòng vào thăm <span class="text-danger">*</span></label>
                            <select name="target_department" class="form-select" required>
                                <option value="Khoa Hồi Sức Tích Cực (ICU)">Khoa Hồi Sức Tích Cực (ICU)</option>
                                <option value="Khoa Sơ Sinh & Hồi Sức Cấp Cứu (NICU)">Khoa Sơ Sinh (NICU)</option>
                                <option value="Khoa Ngoại Tổng Hợp - Hồi Tỉnh">Khoa Ngoại - Hồi Tỉnh</option>
                                <option value="Khoa Nội Tim Mạch Can Thiệp">Khoa Nội Tim Mạch Can Thiệp</option>
                                <option value="Khu Điều Trị Nội Trú VIP Tầng 6">Khu Nội Trú VIP Tầng 6</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Thời lượng vào thăm <span class="text-danger">*</span></label>
                            <select name="valid_hours" class="form-select" required>
                                <option value="1">1 Giờ (Khung giờ thường)</option>
                                <option value="2" selected>2 Giờ</option>
                                <option value="4">4 Giờ (Chăm sóc đặc biệt)</option>
                            </select>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded border mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="infection_screening_passed" value="1" id="screenCheck" checked required>
                            <label class="form-check-label fw-semibold text-success" for="screenCheck">
                                <i class="bi bi-shield-check me-1"></i> Đã đo thân nhiệt (< 37.5°C) & không có triệu chứng hô hấp/ho/sốt
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary fw-bold">Cấp Thẻ Thăm Bệnh</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
