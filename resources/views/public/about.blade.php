@extends('layouts.master')

@section('title', 'Giới Thiệu - Bệnh Viện Đa Khoa Tâm Anh')

@section('content')
<!-- Hero Section -->
<div class="card border-0 shadow-sm text-white overflow-hidden mb-5" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 60%, #0d9488 100%); border-radius: 1.25rem;">
    <div class="card-body p-4 p-md-5">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-white text-primary rounded-pill px-3 py-1 mb-3 fw-bold">VỀ CHÚNG TÔI &bull; BV ĐA KHOA TÂM ANH</span>
                <h1 class="display-5 fw-bold mb-3">TẬN TÂM VÌ SỨC KHỎE NGƯỜI BỆNH</h1>
                <p class="lead mb-4 text-white-50">
                    Bệnh viện Đa khoa Tâm Anh tự hào là một trong những trung tâm y tế chất lượng cao hàng đầu tại Việt Nam, hội tụ đội ngũ chuyên gia, giáo sư, bác sĩ giàu y đức cùng hệ thống trang thiết bị chẩn đoán và điều trị hiện đại bậc nhất thế giới.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('public.doctors.index') }}" class="btn btn-warning btn-lg text-dark fw-bold rounded-pill px-4 shadow">
                        <i class="bi bi-person-badge-fill me-1"></i> Xem Đội Ngũ Bác Sĩ
                    </a>
                    <a href="{{ route('booking.create') }}" class="btn btn-outline-light btn-lg rounded-pill px-4">
                        <i class="bi bi-calendar2-check me-1"></i> Đặt Lịch Khám Trực Tuyến
                    </a>
                </div>
            </div>
            <div class="col-lg-4 text-center mt-4 mt-lg-0 d-none d-lg-block">
                <div class="p-4 bg-white bg-opacity-10 rounded-4 border border-white border-opacity-25 shadow-sm">
                    <i class="bi bi-hospital fs-1 text-warning mb-3 d-block"></i>
                    <h4 class="fw-bold text-white mb-2">TÂM ANH HOSPITAL</h4>
                    <p class="small text-white-50 mb-0">Tiêu chuẩn quốc tế - Kỹ thuật cao - Chi phí hợp lý</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Counter -->
<div class="row g-4 mb-5 text-center">
    <div class="col-md-3 col-6">
        <div class="card h-100 border-0 shadow-sm p-4 rounded-4">
            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 58px; height: 58px;">
                <i class="bi bi-people-fill fs-3"></i>
            </div>
            <h3 class="fw-bold text-dark mb-1">{{ $doctorsCount }}+</h3>
            <span class="text-muted small">Bác sĩ & Chuyên gia công tác</span>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card h-100 border-0 shadow-sm p-4 rounded-4">
            <div class="rounded-circle bg-success bg-opacity-10 text-success d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 58px; height: 58px;">
                <i class="bi bi-diagram-3-fill fs-3"></i>
            </div>
            <h3 class="fw-bold text-dark mb-1">{{ $specialtiesCount }}+</h3>
            <span class="text-muted small">Chuyên khoa khám chữa bệnh</span>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card h-100 border-0 shadow-sm p-4 rounded-4">
            <div class="rounded-circle bg-info bg-opacity-10 text-info d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 58px; height: 58px;">
                <i class="bi bi-building-check fs-3"></i>
            </div>
            <h3 class="fw-bold text-dark mb-1">100%</h3>
            <span class="text-muted small">Phòng khám chuẩn vô trùng JCI</span>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card h-100 border-0 shadow-sm p-4 rounded-4">
            <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 58px; height: 58px;">
                <i class="bi bi-award-fill fs-3"></i>
            </div>
            <h3 class="fw-bold text-dark mb-1">24/7</h3>
            <span class="text-muted small">Hệ thống cấp cứu & Đặt lịch</span>
        </div>
    </div>
</div>

<!-- Mission, Vision & Core Values -->
<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center">
            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                <i class="bi bi-bullseye fs-3"></i>
            </div>
            <h4 class="fw-bold text-dark mb-2">Sứ Mệnh</h4>
            <p class="text-muted small mb-0">
                Mang đến dịch vụ khám chữa bệnh ngoại trú và nội trú tiêu chuẩn quốc tế cho mọi người dân Việt Nam với phương châm người bệnh là trung tâm, tôn trọng và tận tụy chăm sóc.
            </p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center">
            <div class="rounded-circle bg-success bg-opacity-10 text-success d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                <i class="bi bi-eye-fill fs-3"></i>
            </div>
            <h4 class="fw-bold text-dark mb-2">Tầm Nhìn</h4>
            <p class="text-muted small mb-0">
                Trở thành hệ thống bệnh viện đa khoa tư nhân kiểu mẫu hàng đầu khu vực Đông Nam Á, tiên phong áp dụng công nghệ số hóa y tế và các kỹ thuật can thiệp điều trị tiên tiến.
            </p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center">
            <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                <i class="bi bi-gem fs-3"></i>
            </div>
            <h4 class="fw-bold text-dark mb-2">Giá Trị Cốt Lõi</h4>
            <p class="text-muted small mb-0">
                <strong>TÂM</strong> (Y đức tận tâm) - <strong>ANH</strong> (Trí tuệ tinh anh) - <strong>TÍN</strong> (Cam kết chất lượng) - <strong>MINH</strong> (Minh bạch chi phí khám chữa bệnh).
            </p>
        </div>
    </div>
</div>

<!-- Medical Technology Section -->
<div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-5 bg-white">
    <div class="row align-items-center">
        <div class="col-lg-6">
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 mb-2 fw-semibold">TRANG THIẾT BỊ ĐẦU NGÀNH</span>
            <h3 class="fw-bold text-dark mb-3">CÔNG NGHỆ CHẨN ĐOÁN HÌNH ẢNH & ĐIỀU TRỊ HIỆN ĐẠI</h3>
            <p class="text-secondary small mb-3">
                Chúng tôi đầu tư đồng bộ các dòng máy thế hệ mới nhất từ các tập đoàn y tế hàng đầu thế giới như Siemens Healthineers (Đức), GE Healthcare (Mỹ), Philips (Hà Lan):
            </p>
            <ul class="list-unstyled d-flex flex-column gap-2 small text-secondary mb-4">
                <li><i class="bi bi-check-circle-fill text-success me-2"></i> Hệ thống chụp cộng hưởng từ <strong>MRI 3 Tesla Magnetom Vida</strong> công nghệ BioMatrix tự động điều chỉnh theo nhịp thở.</li>
                <li><i class="bi bi-check-circle-fill text-success me-2"></i> Máy chụp cắt lớp vi tính <strong>CT Somatom Drive 768 lát cắt</strong> tầm soát sớm tim mạch và đột quỵ với liều tia thấp nhất.</li>
                <li><i class="bi bi-check-circle-fill text-success me-2"></i> Hệ thống siêu âm tim, sản phụ khoa Doppler 4D màu <strong>Voluson E10</strong> cho hình ảnh giải phẫu thai nhi siêu nét.</li>
                <li><i class="bi bi-check-circle-fill text-success me-2"></i> Phòng mổ tích hợp vô trùng áp lực dương chuẩn quốc tế với hệ thống phẫu thuật nội soi 4K 3D.</li>
            </ul>
            <a href="{{ route('public.doctors.index') }}" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-arrow-right-circle me-1"></i> Khám phá Đội ngũ Bác sĩ chuyên khoa
            </a>
        </div>
        <div class="col-lg-6 mt-4 mt-lg-0 text-center">
            <div class="p-4 bg-light rounded-4 border">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 bg-white rounded-3 shadow-sm border text-center">
                            <i class="bi bi-heart-pulse-fill text-danger fs-1 mb-2 d-block"></i>
                            <div class="fw-bold text-dark small">Can thiệp Tim mạch</div>
                            <small class="text-muted">DSA Philips Azurion</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-white rounded-3 shadow-sm border text-center">
                            <i class="bi bi-activity text-primary fs-1 mb-2 d-block"></i>
                            <div class="fw-bold text-dark small">MRI 3.0 Tesla</div>
                            <small class="text-muted">Siemens Magnetom</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-white rounded-3 shadow-sm border text-center">
                            <i class="bi bi-capsule-pill text-success fs-1 mb-2 d-block"></i>
                            <div class="fw-bold text-dark small">Nội soi NBI 4K</div>
                            <small class="text-muted">Olympus EVIS X1</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-white rounded-3 shadow-sm border text-center">
                            <i class="bi bi-shield-shaded text-warning fs-1 mb-2 d-block"></i>
                            <div class="fw-bold text-dark small">Xét nghiệm tự động</div>
                            <small class="text-muted">Roche Cobas Pro</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Specialties Grid -->
<div class="mb-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <span class="text-primary text-uppercase fw-bold small">Chuyên môn mũi nhọn</span>
            <h3 class="fw-bold text-dark mb-0">CÁC CHUYÊN KHOA TRỌNG ĐIỂM</h3>
        </div>
        <a href="{{ route('public.doctors.index') }}" class="btn btn-outline-primary btn-sm rounded-pill">
            Xem tất cả bác sĩ <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="row g-3">
        @foreach($specialties as $sp)
            <div class="col-md-4 col-sm-6">
                <div class="p-3 bg-white border rounded-3 shadow-sm d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fs-4 flex-shrink-0" style="width: 48px; height: 48px;">
                        <i class="{{ $sp->icon ?: 'bi bi-heart-pulse' }}"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark">{{ $sp->name }}</div>
                        <small class="text-muted">{{ $sp->doctors_count }} bác sĩ trực thuộc</small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- CTA -->
<div class="card border-0 shadow text-center p-4 p-md-5 bg-light rounded-4">
    <h3 class="fw-bold text-dark mb-2">Đăng Ký Khám Sức Khỏe Tại Bệnh Viện Tâm Anh</h3>
    <p class="text-muted mb-4">Hệ thống đặt lịch trực tuyến 24/7 giúp bạn chọn đúng bác sĩ, đúng chuyên khoa, tiết kiệm tối đa thời gian chờ đợi.</p>
    <div class="d-flex justify-content-center flex-wrap gap-3">
        <a href="{{ route('booking.create') }}" class="btn btn-primary btn-lg rounded-pill px-4 shadow">
            <i class="bi bi-calendar-check me-2"></i> Đăng Ký Khám Ngay
        </a>
        <a href="{{ route('public.doctors.index') }}" class="btn btn-outline-secondary btn-lg rounded-pill px-4">
            <i class="bi bi-people me-2"></i> Tra Cứu Danh Bạ Bác Sĩ
        </a>
    </div>
</div>
@endsection
