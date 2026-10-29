@extends('layouts.admin')

@section('title', 'Trung Tâm Tiêm Chủng & Sổ Vắc-Xin Ngoại Trú')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-slate-800">
                <i class="bi bi-shield-check text-primary me-2"></i>Trung Tâm Tiêm Chủng Ngoại Trú & Sổ Vắc-Xin Thông Minh
            </h4>
            <p class="text-muted small mb-0">Quản lý phác đồ tiêm ngừa, khám sàng lọc an toàn và phòng theo dõi 30 phút sau tiêm theo chuẩn Bộ Y Tế.</p>
        </div>
        <div>
            <a href="{{ route('reception.vaccinations.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-plus-circle-fill"></i> Đăng Ký Mũi Tiêm Mới
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

    <!-- Stat Bar -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-primary border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small d-block">Mũi tiêm hôm nay</span>
                        <h3 class="fw-bold mb-0 text-slate-800">{{ $stats['today_total'] }}</h3>
                    </div>
                    <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle">
                        <i class="bi bi-eyedropper fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-warning border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small d-block">Đang theo dõi 30p</span>
                        <h3 class="fw-bold mb-0 text-warning">{{ $stats['observing'] }}</h3>
                    </div>
                    <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-circle">
                        <i class="bi bi-stopwatch-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-success border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small d-block">Đã ra về an toàn</span>
                        <h3 class="fw-bold mb-0 text-success">{{ $stats['cleared_today'] }}</h3>
                    </div>
                    <div class="p-3 bg-success bg-opacity-10 text-success rounded-circle">
                        <i class="bi bi-check2-circle fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-danger border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small d-block">Phản ứng bất thường</span>
                        <h3 class="fw-bold mb-0 text-danger">{{ $stats['adverse_count'] }}</h3>
                    </div>
                    <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-circle">
                        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active 30-Minute Observation Room -->
    @if($observingRecords->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4 border-start border-warning border-4">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-slate-800">
                    <i class="bi bi-stopwatch text-warning me-2 fs-5"></i>Phòng Theo Dõi 30 Phút Sau Tiêm (Live Post-Vaccination Room)
                </h6>
                <span class="badge bg-warning text-dark">{{ $observingRecords->count() }} bệnh nhân đang lưu theo dõi</span>
            </div>
            <div class="card-body p-3">
                <div class="row g-3">
                    @foreach($observingRecords as $obs)
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100 border-2 {{ $obs->is_observation_complete ? 'border-success bg-success bg-opacity-10' : 'border-warning bg-warning bg-opacity-10' }}">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">{{ $obs->patientProfile->full_name }}</h6>
                                            <small class="text-muted">Mã: <code>{{ $obs->patientProfile->patient_code }}</code> • {{ $obs->patientProfile->age_text }}</small>
                                        </div>
                                        <span class="badge bg-primary">{{ $obs->vaccine->name }}</span>
                                    </div>
                                    <div class="small text-muted mb-2">
                                        <i class="bi bi-clock me-1"></i>Đã tiêm lúc: <strong>{{ $obs->administered_at->format('H:i') }}</strong>
                                        (Đã theo dõi: <strong>{{ $obs->observation_elapsed_minutes }} phút</strong>)
                                    </div>

                                    @if($obs->is_observation_complete)
                                        <div class="alert alert-success p-2 small border-0 mb-2">
                                            <i class="bi bi-check-circle-fill me-1"></i>Đã đủ 30 phút theo dõi an toàn!
                                        </div>
                                        <form action="{{ route('reception.vaccinations.clear', $obs) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success w-100 fw-bold">
                                                <i class="bi bi-check-all me-1"></i>Xác Nhận Xuất Viện An Toàn
                                            </button>
                                        </form>
                                    @else
                                        <div class="progress mb-2" style="height: 8px;">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" style="width: {{ min(100, round(($obs->observation_elapsed_minutes / 30) * 100)) }}%"></div>
                                        </div>
                                        <div class="d-flex justify-content-between small text-dark mb-2">
                                            <span>Tiến độ: {{ min(100, round(($obs->observation_elapsed_minutes / 30) * 100)) }}%</span>
                                            <strong class="text-danger">Còn {{ $obs->observation_remaining_minutes }} phút nữa</strong>
                                        </div>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('reception.vaccinations.show', $obs) }}" class="btn btn-sm btn-outline-primary flex-grow-1">
                                                Chi tiết
                                            </a>
                                            <form action="{{ route('reception.vaccinations.clear', $obs) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Xác nhận đủ điều kiện ra về">
                                                    <i class="bi bi-check2"></i> Cho về
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Vaccination Records Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <form action="{{ route('reception.vaccinations.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Tìm theo Mã phiếu, Tên bệnh nhân, Mã BN, Số lô..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Tất cả trạng thái theo dõi --</option>
                        <option value="observing" {{ request('status') == 'observing' ? 'selected' : '' }}>Đang theo dõi 30 phút</option>
                        <option value="cleared" {{ request('status') == 'cleared' ? 'selected' : '' }}>Đã an toàn xuất viện</option>
                        <option value="adverse_reaction" {{ request('status') == 'adverse_reaction' ? 'selected' : '' }}>Phản ứng bất thường</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100">Lọc</button>
                    @if(request()->anyFilled(['search', 'status']))
                        <a href="{{ route('reception.vaccinations.index') }}" class="btn btn-sm btn-outline-secondary">Xóa</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th>Mã Phiếu</th>
                        <th>Bệnh Nhân</th>
                        <th>Loại Vắc-Xin / Liều</th>
                        <th>Số Lô & Hạn Dùng</th>
                        <th>Hẹn Mũi Tiếp Theo</th>
                        <th>Trạng Thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $rec)
                        <tr>
                            <td>
                                <strong class="text-primary font-monospace">{{ $rec->record_code }}</strong>
                                <small class="text-muted d-block">{{ $rec->administered_at->format('H:i d/m/Y') }}</small>
                            </td>
                            <td>
                                <strong class="text-dark d-block">{{ $rec->patientProfile->full_name }}</strong>
                                <small class="text-muted">Mã: <code>{{ $rec->patientProfile->patient_code }}</code> • {{ $rec->patientProfile->age_text }}</small>
                            </td>
                            <td>
                                <strong class="text-slate-800 d-block">{{ $rec->vaccine->name }}</strong>
                                <span class="badge bg-light text-dark border">Mũi số {{ $rec->dose_number }}/{{ $rec->vaccine->total_doses }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark font-monospace">{{ $rec->batch_number }}</span>
                                <small class="text-muted d-block mt-1">HSD: {{ $rec->batch_expiry_date?->format('d/m/Y') ?? 'N/A' }}</small>
                            </td>
                            <td>
                                @if($rec->next_dose_due_date)
                                    <strong class="text-primary">{{ $rec->next_dose_due_date->format('d/m/Y') }}</strong>
                                @else
                                    <span class="text-muted small">Hoàn thành phác đồ</span>
                                @endif
                            </td>
                            <td>
                                {!! $rec->status_badge !!}
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('reception.vaccinations.show', $rec) }}" class="btn btn-outline-primary" title="Xem chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('reception.vaccinations.certificate', $rec) }}" target="_blank" class="btn btn-outline-success" title="In Giấy Xác Nhận Tiêm Chủng Quốc Tế">
                                        <i class="bi bi-printer"></i> Giấy xác nhận
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-eyedropper fs-1 d-block mb-2 text-secondary"></i>
                                Chưa có dữ liệu tiêm chủng nào phù hợp.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($records->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $records->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
