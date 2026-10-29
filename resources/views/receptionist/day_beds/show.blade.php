@extends('layouts.admin')

@section('title', 'Bảng Theo Dõi Bệnh Nhân Lưu Ban Ngày')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('reception.day-beds.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Trở lại sơ đồ giường
                </a>
                <span class="badge bg-primary fs-6">{{ $admission->dayBed->bed_code }} ({{ $admission->dayBed->room_name }})</span>
            </div>
            <h4 class="fw-bold mb-0 text-slate-800">
                Hồ Sơ Lưu Bệnh: {{ $admission->patientProfile->full_name }}
            </h4>
        </div>
        <div class="d-flex gap-2">
            @if($admission->status === 'active')
                <button type="button" class="btn btn-danger d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#dischargeModal">
                    <i class="bi bi-box-arrow-right"></i> Xuất Viện / Trả Giường
                </button>
            @endif
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Patient Info Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-person-vcard text-primary me-2"></i>Thông Tin Bệnh Nhân</h6>
                </div>
                <div class="card-body">
                    <div class="text-center pb-3 border-bottom mb-3">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 56px; height: 56px;">
                            <i class="bi bi-person-fill fs-3"></i>
                        </div>
                        <h5 class="fw-bold mb-0">{{ $admission->patientProfile->full_name }}</h5>
                        <span class="badge bg-primary-subtle text-primary">{{ $admission->patientProfile->patient_code }}</span>
                    </div>

                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Tuổi / Giới tính:</span>
                            <strong>{{ $admission->patientProfile->age_text }} • {{ $admission->patientProfile->gender_name }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Số điện thoại:</span>
                            <strong>{{ $admission->patientProfile->phone }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Giờ vào lưu bệnh:</span>
                            <strong>{{ $admission->admitted_at->format('H:i - d/m/Y') }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Tổng thời gian lưu:</span>
                            <strong class="{{ $admission->is_overstay ? 'text-danger' : 'text-primary' }}">{{ $admission->duration_hours }} giờ</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Bác sĩ chỉ định:</span>
                            <strong>{{ $admission->attendingDoctor?->user->name ?? 'BS Trực cấp cứu' }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Điều dưỡng phụ trách:</span>
                            <strong>{{ $admission->attending_nurse_name ?? 'Điều dưỡng ca' }}</strong>
                        </li>
                    </ul>

                    <div class="mt-3 p-3 bg-light rounded-3">
                        <small class="text-muted d-block fw-semibold">Lý do lưu bệnh:</small>
                        <p class="mb-0 text-dark small mt-1">{{ $admission->admission_reason }}</p>
                    </div>

                    @if($admission->is_overstay)
                        <div class="alert alert-danger border-0 rounded-3 mt-3 mb-0 small">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            <strong>Cảnh báo quá giờ:</strong> Bệnh nhân đã lưu vượt mức 4 tiếng quy định của Bộ Y Tế. Vui lòng kiểm tra và hoàn tất xuất viện hoặc chuyển khoa nội trú.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- IV Infusion & Clinical Flowsheet -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-slate-800">
                        <i class="bi bi-droplet-half text-info me-2"></i>Bảng Theo Dõi Truyền Dịch (IV Infusion Flowsheet)
                    </h6>
                    @if($admission->status === 'active')
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#addInfusionForm">
                            <i class="bi bi-plus-lg me-1"></i>Thêm Dịch Truyền
                        </button>
                    @endif
                </div>

                <!-- Add Infusion Collapse -->
                <div class="collapse border-bottom bg-light p-3" id="addInfusionForm">
                    <form action="{{ route('reception.day-beds.infusion.store', $admission) }}" method="POST">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-5">
                                <label class="form-label small fw-semibold">Tên dịch truyền</label>
                                <input type="text" name="fluid_name" class="form-control form-control-sm" placeholder="VD: Ringer Lactat 500ml" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">Thể tích (ml)</label>
                                <input type="number" name="volume_ml" class="form-control form-control-sm" value="500" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">Tốc độ (giọt/p)</label>
                                <input type="number" name="drip_rate_drops_per_min" class="form-control form-control-sm" value="30" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Điều dưỡng thực hiện</label>
                                <input type="text" name="nurse_name" class="form-control form-control-sm" value="{{ auth()->user()->name }}">
                            </div>
                            <div class="col-12 text-end mt-2">
                                <button type="submit" class="btn btn-sm btn-primary">Xác Nhận Treo Dịch</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted small text-uppercase">
                                <tr>
                                    <th>Tên Dung Dịch Truyền</th>
                                    <th>Thể Tích / Tốc Độ</th>
                                    <th>Giờ Bắt Đầu</th>
                                    <th>Dự Kiến Xong</th>
                                    <th>Tiến Độ</th>
                                    <th>Trạng Thái</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($admission->ivInfusions as $infusion)
                                    <tr>
                                        <td>
                                            <strong class="text-dark d-block">{{ $infusion->fluid_name }}</strong>
                                            <small class="text-muted"><i class="bi bi-person me-1"></i>{{ $infusion->nurse_name ?? 'ĐD ca trực' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $infusion->volume_ml }} ml</span>
                                            <small class="text-primary fw-semibold d-block mt-1">{{ $infusion->drip_rate_drops_per_min }} giọt/phút</small>
                                        </td>
                                        <td>{{ $infusion->started_at->format('H:i') }}</td>
                                        <td>{{ $infusion->estimated_finish_at?->format('H:i') ?? '--' }}</td>
                                        <td style="min-width: 140px;">
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar {{ $infusion->status === 'completed' ? 'bg-success' : 'progress-bar-striped progress-bar-animated bg-info' }}" style="width: {{ $infusion->progress_percent }}%"></div>
                                            </div>
                                            <small class="text-muted">{{ $infusion->progress_percent }}%</small>
                                        </td>
                                        <td>
                                            @if($infusion->status === 'completed')
                                                <span class="badge bg-success-subtle text-success">Đã hoàn tất</span>
                                            @elseif($infusion->status === 'running')
                                                <span class="badge bg-info-subtle text-info">Đang truyền</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Tạm dừng</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($infusion->status === 'running')
                                                <form action="{{ route('reception.day-beds.infusion.complete', $infusion) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Xác nhận đã truyền hết chai">
                                                        <i class="bi bi-check2"></i> Xong
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            Chưa có chỉ định dịch truyền nào cho ca lưu bệnh này.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Discharge Modal -->
<div class="modal fade" id="dischargeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('reception.day-beds.discharge', $admission) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="bi bi-box-arrow-right me-1"></i> Xuất Giường Bệnh Nhân
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-start">
                    <p class="mb-2">Bệnh nhân: <strong>{{ $admission->patientProfile->full_name }}</strong> (Mã: {{ $admission->patientProfile->patient_code }})</p>
                    <p class="small text-muted mb-3">Thời gian lưu: {{ $admission->duration_hours }} giờ tại giường {{ $admission->dayBed->bed_code }}.</p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Ghi chú xuất viện / Tình trạng ra viện:</label>
                        <textarea name="discharge_notes" class="form-control form-control-sm" rows="3" required>Sinh hiệu ổn định, hết sốt, dặn dò uống thuốc theo đơn.</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-sm btn-danger">Xác Nhận Xuất Giường</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
