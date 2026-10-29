@extends('layouts.admin')

@section('title', 'Hội Chẩn Đa Chuyên Khoa Nội Viện')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-slate-800">
                <i class="bi bi-people-fill text-primary me-2"></i>Hội Chẩn Đa Chuyên Khoa Nội Viện (In-Hospital Tele-Consultation)
            </h4>
            <p class="text-muted small mb-0">Điều phối và hội chẩn liên chuyên khoa, phân loại mức độ khẩn cấp (Tối khẩn 15 phút, Khẩn cấp 1 giờ, Thường quy) theo chuẩn Bộ Y Tế.</p>
        </div>
        <div>
            <a href="{{ route('consultations.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-plus-circle-fill"></i> Tạo Yêu Cầu Hội Chẩn
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

    <!-- Stat Summary Bar -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-primary border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small d-block">Tổng số ca hội chẩn</span>
                        <h3 class="fw-bold mb-0 text-slate-800">{{ $stats['total'] }}</h3>
                    </div>
                    <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle">
                        <i class="bi bi-diagram-3-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-danger border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small d-block">Tối khẩn (Chờ trong 15p)</span>
                        <h3 class="fw-bold mb-0 text-danger">{{ $stats['stat'] }}</h3>
                    </div>
                    <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-circle animate-pulse">
                        <i class="bi bi-exclamation-octagon-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-warning border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small d-block">Đang chờ phản hồi</span>
                        <h3 class="fw-bold mb-0 text-warning">{{ $stats['pending'] }}</h3>
                    </div>
                    <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-circle">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-success border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small d-block">Đã có kết luận</span>
                        <h3 class="fw-bold mb-0 text-success">{{ $stats['completed'] }}</h3>
                    </div>
                    <div class="p-3 bg-success bg-opacity-10 text-success rounded-circle">
                        <i class="bi bi-check-all fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <form action="{{ route('consultations.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Tìm theo Mã HC, Tên bệnh nhân hoặc Mã BN..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="urgency" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Mức độ khẩn cấp --</option>
                        <option value="stat" {{ request('urgency') == 'stat' ? 'selected' : '' }}>🔴 Tối khẩn (15 phút)</option>
                        <option value="urgent" {{ request('urgency') == 'urgent' ? 'selected' : '' }}>🟠 Khẩn cấp (1 giờ)</option>
                        <option value="routine" {{ request('urgency') == 'routine' ? 'selected' : '' }}>🟡 Thường quy</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="specialty" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Chuyên khoa hội chẩn --</option>
                        @foreach($specialties as $spec)
                            <option value="{{ $spec->id }}" {{ request('specialty') == $spec->id ? 'selected' : '' }}>{{ $spec->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100">Lọc</button>
                    @if(request()->anyFilled(['search', 'urgency', 'specialty']))
                        <a href="{{ route('consultations.index') }}" class="btn btn-sm btn-outline-secondary">Xóa</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th>Mã Phiếu / Mức Độ</th>
                        <th>Bệnh Nhân</th>
                        <th>Khoa Yêu Cầu -> Khoa Mời</th>
                        <th>Lý Do Hội Chẩn</th>
                        <th>Trạng Thái</th>
                        <th>Thời Gian</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($consultations as $item)
                        <tr class="{{ $item->urgency_level === 'stat' && $item->status === 'requested' ? 'table-danger bg-opacity-25' : '' }}">
                            <td>
                                <strong class="text-primary font-monospace d-block">{{ $item->consultation_code }}</strong>
                                {!! $item->urgency_badge !!}
                            </td>
                            <td>
                                <strong class="text-dark d-block">{{ $item->patientProfile->full_name }}</strong>
                                <small class="text-muted">Mã: <code>{{ $item->patientProfile->patient_code }}</code> • {{ $item->patientProfile->age_text }}</small>
                            </td>
                            <td>
                                <div class="small">
                                    <span class="text-muted">Từ:</span> <strong>{{ $item->requestingSpecialty?->name ?? 'Khoa Khám Bệnh' }}</strong>
                                </div>
                                <div class="small text-primary mt-1">
                                    <i class="bi bi-arrow-right-short"></i><span class="text-muted">Đến:</span> <strong>{{ $item->targetSpecialty->name }}</strong>
                                </div>
                            </td>
                            <td style="max-width: 280px;">
                                <strong class="text-slate-800 small d-block mb-1">{{ Str::limit($item->reason, 60) }}</strong>
                                <p class="text-muted small mb-0 text-truncate" title="{{ $item->proposed_questions }}">{{ $item->proposed_questions }}</p>
                            </td>
                            <td>
                                {!! $item->status_badge !!}
                            </td>
                            <td>
                                <small class="text-dark fw-semibold d-block">{{ $item->created_at->format('H:i') }}</small>
                                <small class="text-muted">{{ $item->created_at->format('d/m/Y') }}</small>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('consultations.show', $item) }}" class="btn btn-outline-primary" title="Mở phòng hội chẩn trực tuyến">
                                        <i class="bi bi-box-arrow-in-right me-1"></i>Hội chẩn
                                    </a>
                                    <a href="{{ route('consultations.print', $item) }}" target="_blank" class="btn btn-outline-dark" title="In Biên bản hội chẩn chuẩn Bộ Y Tế">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                                Không có yêu cầu hội chẩn nào phù hợp với bộ lọc.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($consultations->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $consultations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
