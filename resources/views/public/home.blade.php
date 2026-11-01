@extends('layouts.master')

@section('title', 'Bệnh Viện Đa Khoa Tâm Anh - Hệ Thống Đặt Lịch Khám & Chăm Sóc Sức Khỏe')

@section('content')
<!-- Hero Section -->
<div class="card border-0 shadow-sm text-white overflow-hidden mb-5" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 60%, #0d9488 100%); border-radius: 1rem;">
    <div class="card-body p-4 p-md-5">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <span class="badge bg-white text-primary rounded-pill px-3 py-1 mb-3 fw-bold">CHUYÊN GIA ĐẦU NGÀNH &bull; CÔNG NGHỆ CAO</span>
                <h1 class="display-5 fw-bold mb-3">TẬN TÂM CHĂM SÓC, AN TÂM HỒI PHỤC</h1>
                <p class="lead mb-4 text-white-50">
                    Bệnh viện Đa khoa Tâm Anh mang đến dịch vụ khám chữa bệnh ngoại trú tiêu chuẩn quốc tế. Đặt lịch trực tuyến nhanh chóng, chọn bác sĩ và ca khám mong muốn mà không cần chờ đợi.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('booking.create') }}" class="btn btn-warning btn-lg text-dark fw-bold rounded-pill px-4 shadow">
                        <i class="bi bi-calendar2-check-fill me-1"></i> ĐẶT LỊCH KHÁM NGAY
                    </a>
                    <a href="{{ route('booking.lookup') }}" class="btn btn-outline-light btn-lg rounded-pill px-4">
                        <i class="bi bi-search me-1"></i> Tra cứu phiếu hẹn
                    </a>
                </div>
            </div>
            <div class="col-lg-5 text-center mt-4 mt-lg-0 d-none d-lg-block">
                <div class="p-4 bg-white bg-opacity-10 rounded-4 border border-white border-opacity-25 shadow-sm text-start">
                    <h5 class="fw-bold mb-3 text-white"><i class="bi bi-shield-check text-warning me-2"></i>Quy trình Khám ngoại trú</h5>
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <span class="badge bg-warning text-dark rounded-circle p-2 px-3 fw-bold">1</span>
                        <div class="small">Chọn Chuyên khoa, Bác sĩ & Khung giờ phù hợp trên website.</div>
                    </div>
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <span class="badge bg-warning text-dark rounded-circle p-2 px-3 fw-bold">2</span>
                        <div class="small">Nhận Mã tra cứu lịch hẹn qua màn hình và email tự động.</div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <span class="badge bg-warning text-dark rounded-circle p-2 px-3 fw-bold">3</span>
                        <div class="small">Đến viện, quét mã check-in tại quầy Lễ tân và vào thẳng phòng khám.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Key Highlights -->
<div class="row g-4 mb-5 text-center">
    <div class="col-md-3 col-6">
        <div class="card h-100 border-0 shadow-sm p-3">
            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 50px; height: 50px;">
                <i class="bi bi-people-fill fs-4"></i>
            </div>
            <h4 class="fw-bold text-dark mb-0">150+</h4>
            <span class="text-muted small">Bác sĩ & Chuyên gia</span>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card h-100 border-0 shadow-sm p-3">
            <div class="rounded-circle bg-success bg-opacity-10 text-success d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 50px; height: 50px;">
                <i class="bi bi-diagram-3-fill fs-4"></i>
            </div>
            <h4 class="fw-bold text-dark mb-0">20+</h4>
            <span class="text-muted small">Chuyên khoa mũi nhọn</span>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card h-100 border-0 shadow-sm p-3">
            <div class="rounded-circle bg-info bg-opacity-10 text-info d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 50px; height: 50px;">
                <i class="bi bi-clock-history fs-4"></i>
            </div>
            <h4 class="fw-bold text-dark mb-0">0 Phút</h4>
            <span class="text-muted small">Xếp hàng chờ lấy số</span>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card h-100 border-0 shadow-sm p-3">
            <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 50px; height: 50px;">
                <i class="bi bi-award-fill fs-4"></i>
            </div>
            <h4 class="fw-bold text-dark mb-0">100%</h4>
            <span class="text-muted small">Hồ sơ y tế bảo mật</span>
        </div>
    </div>
</div>

<!-- Specialties Section -->
<div class="mb-5" id="specialties">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <span class="text-primary text-uppercase fw-bold small">Danh mục điều trị</span>
            <h3 class="fw-bold text-dark mb-0">CHUYÊN KHOA NỔI BẬT</h3>
        </div>
        <a href="{{ route('booking.create') }}" class="btn btn-outline-primary btn-sm rounded-pill">
            Đặt hẹn chuyên khoa <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="row g-4">
        @forelse($specialties as $specialty)
            <div class="col-md-4 col-sm-6">
                <div class="card h-100 border-0 shadow-sm transition hover-shadow">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="rounded-3 bg-light text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                            <i class="{{ $specialty->icon ?? 'bi bi-hospital' }} fs-4"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">{{ $specialty->name }}</h5>
                        <p class="text-muted small flex-grow-1">
                            {{ Str::limit($specialty->description ?? 'Chuyên khoa cung cấp dịch vụ khám và điều trị ngoại trú kỹ thuật cao.', 100) }}
                        </p>
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                            <span class="small text-muted">{{ $specialty->doctors_count }} bác sĩ trực</span>
                            <a href="{{ route('booking.create', ['specialty_id' => $specialty->id]) }}" class="btn btn-sm btn-primary rounded-pill px-3">
                                Đặt khám
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center text-muted py-4">Đang cập nhật danh sách chuyên khoa...</div>
        @endforelse
    </div>
</div>

<!-- Doctors Section -->
<div class="mb-5" id="doctors">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <span class="text-primary text-uppercase fw-bold small">Đội ngũ y tế</span>
            <h3 class="fw-bold text-dark mb-0">BÁC SĨ TIÊU BIỂU</h3>
        </div>
        <a href="{{ route('public.doctors.index') }}" class="btn btn-outline-primary btn-sm rounded-pill">
            Xem tất cả bác sĩ <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="row g-4">
        @forelse($doctors as $doctor)
            <div class="col-lg-3 col-md-6">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="rounded-circle bg-light text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm" style="width: 72px; height: 72px;">
                            <i class="bi bi-person-badge fs-1 text-primary"></i>
                        </div>
                        <span class="badge bg-light text-muted border mx-auto mb-2">{{ $doctor->qualification }}</span>
                        <h5 class="fw-bold text-dark mb-1">{{ $doctor->user->name ?? 'Bác sĩ' }}</h5>
                        <span class="text-primary small fw-semibold mb-2">{{ $doctor->specialty->name ?? 'Đa khoa' }}</span>
                        <p class="text-muted small mb-3">Phòng: <strong>{{ $doctor->room_number }}</strong> &bull; Giá khám: {{ number_format($doctor->consultation_fee, 0, ',', '.') }} đ</p>
                        <div class="mt-auto d-grid gap-2">
                            <a href="{{ route('booking.create', ['specialty_id' => $doctor->specialty_id, 'doctor_id' => $doctor->id]) }}" class="btn btn-primary btn-sm rounded-pill">
                                <i class="bi bi-calendar-plus me-1"></i> Đặt lịch bác sĩ
                            </a>
                            <a href="{{ route('public.doctors.show', $doctor) }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                                Xem hồ sơ chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center text-muted py-4">Đang cập nhật danh bạ bác sĩ...</div>
        @endforelse
    </div>
</div>

<!-- CTA Banner -->
<div class="card border-0 shadow text-center p-4 p-md-5 bg-light mb-4" style="border-radius: 1rem;">
    <h3 class="fw-bold text-dark mb-2">Bạn Cần Hỗ Trợ Đặt Lịch Hoặc Tư Vấn?</h3>
    <p class="text-muted mb-4">Tổng đài viên Bệnh viện Đa khoa Tâm Anh luôn sẵn sàng hỗ trợ bạn 24/7 hoàn toàn miễn phí.</p>
    <div class="d-flex justify-content-center gap-3">
        <a href="tel:18006858" class="btn btn-outline-danger btn-lg rounded-pill px-4">
            <i class="bi bi-telephone-outbound-fill me-2"></i> Hotline: 1800 6858
        </a>
        <a href="{{ route('booking.create') }}" class="btn btn-primary btn-lg rounded-pill px-4">
            <i class="bi bi-calendar-check me-2"></i> Đăng Ký Khám Ngay
        </a>
    </div>
</div>
@endsection
