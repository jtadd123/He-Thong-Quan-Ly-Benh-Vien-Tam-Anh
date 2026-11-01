<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top shadow-sm py-2">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
            <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 42px; height: 42px; background: linear-gradient(135deg, #0284c7 0%, #0d9488 100%);">
                <i class="bi bi-hospital fs-5"></i>
            </div>
            <div>
                <span class="fw-bold d-block lh-1 text-primary" style="font-size: 1.15rem; color: #0284c7 !important;">BỆNH VIỆN TÂM ANH</span>
                <small class="text-muted text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.8px;">Tận tâm - Chuyên nghiệp - Hiện đại</small>
            </div>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('/') ? 'active fw-semibold text-primary' : '' }}" href="{{ url('/') }}">
                        <i class="bi bi-house-door me-1"></i> Trang chủ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('about*') ? 'active fw-semibold text-primary' : '' }}" href="{{ route('about') }}">
                        <i class="bi bi-info-circle me-1"></i> Giới thiệu
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('doctors') || (request()->is('doctors/*') && !request()->is('doctors/symptom-checker*')) ? 'active fw-semibold text-primary' : '' }}" href="{{ route('public.doctors.index') }}">
                        <i class="bi bi-people me-1"></i> Đội ngũ Bác sĩ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('doctors/symptom-checker*') ? 'active fw-semibold text-primary' : '' }}" href="{{ route('public.doctors.symptom_checker') }}">
                        <i class="bi bi-robot text-primary me-1"></i> Trợ lý Triệu chứng <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill" style="font-size: 0.65rem;">AI</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('booking') ? 'active fw-semibold text-primary' : '' }}" href="{{ route('booking.create') }}">
                        <i class="bi bi-calendar-plus me-1"></i> Đặt lịch khám
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('booking/lookup*') ? 'active fw-semibold text-primary' : '' }}" href="{{ route('booking.lookup') }}">
                        <i class="bi bi-search me-1"></i> Tra cứu lịch hẹn
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                @auth
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle d-flex align-items-center gap-2 py-1 px-3 rounded-pill" type="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-5"></i>
                            <span class="fw-medium text-truncate" style="max-width: 140px;">{{ Auth::user()->name }}</span>
                            <span class="badge {{ Auth::user()->role_badge_class }} rounded-pill" style="font-size: 0.7rem;">
                                {{ Auth::user()->role_name }}
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="userMenuDropdown">
                            <li class="px-3 py-2 border-bottom">
                                <div class="fw-bold text-dark">{{ Auth::user()->name }}</div>
                                <div class="small text-muted">{{ Auth::user()->email }}</div>
                            </li>
                            @if(Auth::user()->isAdmin())
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('admin.dashboard') }}">
                                        <i class="bi bi-speedometer2 text-danger me-2"></i> Bảng điều khiển Quản trị
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('admin.users.index') }}">
                                        <i class="bi bi-people text-primary me-2"></i> Quản lý Người dùng
                                    </a>
                                </li>
                            @elseif(Auth::user()->isReceptionist())
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('reception.appointments.index') }}">
                                        <i class="bi bi-clipboard2-check text-info me-2"></i> Bàn tiếp nhận & Check-in
                                    </a>
                                </li>
                            @elseif(Auth::user()->isDoctor())
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('doctor.queue') }}">
                                        <i class="bi bi-heart-pulse text-primary me-2"></i> Hàng đợi khám bệnh
                                    </a>
                                </li>
                            @elseif(Auth::user()->isPatient())
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('patient.history') }}">
                                        <i class="bi bi-journal-medical text-success me-2"></i> Lịch sử khám của tôi
                                    </a>
                                </li>
                            @endif
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('profile.password') }}">
                                    <i class="bi bi-shield-lock text-warning me-2"></i> Đổi mật khẩu
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger py-2">
                                        <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('booking.create') }}" class="btn btn-primary d-none d-md-inline-flex align-items-center gap-1 rounded-pill px-3 shadow-sm" style="background-color: #0284c7; border-color: #0284c7;">
                        <i class="bi bi-calendar2-check"></i> Đặt lịch ngay
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1 rounded-pill px-3">
                        <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>
