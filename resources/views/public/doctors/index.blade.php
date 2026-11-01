@extends('layouts.master')

@section('title', 'Đội Ngũ Bác Sĩ Chuyên Khoa - Bệnh Viện Đa Khoa Tâm Anh')

@section('content')
<!-- Header Banner -->
<div class="bg-primary text-white p-4 p-md-5 rounded-4 mb-4 shadow-sm" style="background: linear-gradient(135deg, #0284c7 0%, #0d9488 100%);">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <span class="badge bg-white text-primary px-3 py-2 rounded-pill fw-bold mb-2">Đội ngũ Chuyên gia Hàng đầu</span>
            <h1 class="fw-bold mb-2 display-6">Danh Bạ Bác Sĩ Bệnh Viện Tâm Anh</h1>
            <p class="mb-0 text-white-50 lead fs-6">
                Quy tụ các Giáo sư, Tiến sĩ, Bác sĩ Chuyên khoa giàu kinh nghiệm, tận tâm vì sức khỏe người bệnh và ứng dụng các kỹ thuật y khoa hiện đại.
            </p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <a href="{{ route('booking.create') }}" class="btn btn-light btn-lg px-4 rounded-pill fw-bold text-primary shadow-sm">
                <i class="bi bi-calendar-check me-1"></i> Đặt lịch hẹn ngay
            </a>
        </div>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3 p-md-4">
        <form method="GET" action="{{ route('public.doctors.index') }}" class="row g-3">
            <div class="col-md-7 col-lg-8">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0" placeholder="Tìm theo tên bác sĩ, học vị (PGS, TS, ThS), phòng khám...">
                </div>
            </div>
            <div class="col-md-5 col-lg-4 d-flex gap-2">
                <select name="specialty_id" class="form-select">
                    <option value="">-- Tất cả chuyên khoa --</option>
                    @foreach($specialties as $sp)
                        <option value="{{ $sp->id }}" {{ request('specialty_id') == $sp->id ? 'selected' : '' }}>
                            {{ $sp->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-1">
                    <i class="bi bi-search"></i> Tìm
                </button>
                @if(request('search') || request('specialty_id') || request()->boolean('on_duty_today'))
                    <a href="{{ route('public.doctors.index') }}" class="btn btn-outline-secondary" title="Đặt lại">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
            <div class="col-12 mt-2 pt-2 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" role="switch" name="on_duty_today" value="1" id="on_duty_today" {{ request()->boolean('on_duty_today') ? 'checked' : '' }} onchange="this.form.submit()">
                    <label class="form-check-label fw-semibold text-dark small" for="on_duty_today">
                        <i class="bi bi-clock-history text-success me-1"></i> Chỉ hiển thị bác sĩ có lịch trực hôm nay
                    </label>
                </div>
                <span class="text-muted small">
                    <i class="bi bi-info-circle me-1"></i> Tìm thấy <strong>{{ $doctors->total() }}</strong> bác sĩ phù hợp
                </span>
            </div>
        </form>
    </div>
</div>

<!-- Doctor Grid -->
<div class="row g-4 mb-4">
    @forelse($doctors as $doctor)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="rounded-circle bg-light text-primary d-flex align-items-center justify-content-center fw-bold fs-3 flex-shrink-0 overflow-hidden" style="width: 64px; height: 64px; border: 2px solid #e2e8f0;">
                            @if($doctor->avatar_url)
                                <img src="{{ $doctor->avatar_url }}" alt="{{ $doctor->full_name_with_title }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <i class="bi bi-person-badge"></i>
                            @endif
                        </div>
                        <div>
                            @if($doctor->specialty)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1 small mb-1">
                                    <i class="{{ $doctor->specialty->icon ?: 'bi bi-tag' }} me-1"></i> {{ $doctor->specialty->name }}
                                </span>
                            @endif
                            <h5 class="fw-bold text-dark mb-0 lh-sm">{{ $doctor->full_name_with_title }}</h5>
                            <small class="text-muted"><i class="bi bi-geo-alt me-1"></i> {{ $doctor->room_number }}</small>
                            <div class="mt-1">
                                @if($doctor->is_on_duty_today)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                        <i class="bi bi-calendar2-check-fill me-1"></i> Trực hôm nay: {{ $doctor->today_shifts_text }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                        <i class="bi bi-calendar3 me-1"></i> Nhận đặt theo hẹn
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <p class="text-secondary small mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                        {{ $doctor->bio ?: 'Bác sĩ chuyên khoa giàu kinh nghiệm tại Bệnh viện Đa khoa Tâm Anh, tận tâm chăm sóc sức khỏe toàn diện cho bệnh nhân.' }}
                    </p>

                    <div class="border-top pt-3 mt-auto">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted small">Phí khám ban đầu:</span>
                            <span class="fw-bold text-primary fs-6">{{ number_format($doctor->consultation_fee, 0, ',', '.') }} đ</span>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="{{ route('booking.create') }}?doctor_id={{ $doctor->id }}&specialty_id={{ $doctor->specialty_id }}" class="btn btn-primary rounded-pill fw-semibold">
                                <i class="bi bi-calendar-plus me-1"></i> Đặt lịch khám
                            </a>
                            <a href="{{ route('public.doctors.show', $doctor) }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                                Xem lý lịch chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm p-5 text-center text-muted">
                <i class="bi bi-search fs-1 d-block mb-3"></i>
                <h5 class="text-dark">Không tìm thấy bác sĩ nào phù hợp</h5>
                <p>Vui lòng thử tìm với từ khóa khác hoặc chọn chuyên khoa khác.</p>
                <div class="mt-2">
                    <a href="{{ route('public.doctors.index') }}" class="btn btn-outline-primary rounded-pill">
                        Xem tất cả bác sĩ
                    </a>
                </div>
            </div>
        </div>
    @endforelse
</div>

@if($doctors->hasPages())
    <div class="d-flex justify-content-center mb-4">
        {{ $doctors->links() }}
    </div>
@endif
@endsection
