@extends('layouts.master')

@section('title', 'Đăng nhập Hệ thống - Bệnh Viện Tâm Anh')

@section('content')
<div class="row justify-content-center my-4">
    <div class="col-md-8 col-lg-5">
        <x-alert />
        <div class="card shadow border-0 overflow-hidden">
            <div class="card-header text-white text-center py-4" style="background: linear-gradient(135deg, #0284c7 0%, #0d9488 100%);">
                <div class="rounded-circle bg-white text-primary d-inline-flex align-items-center justify-content-center mb-2 shadow-sm" style="width: 54px; height: 54px;">
                    <i class="bi bi-shield-lock-fill fs-3 text-primary"></i>
                </div>
                <h4 class="fw-bold mb-1">ĐĂNG NHẬP HỆ THỐNG</h4>
                <p class="mb-0 small text-light opacity-90">Cổng thông tin & Điều phối Bệnh viện Tâm Anh</p>
            </div>

            <div class="card-body p-4 p-md-5">
                <form method="POST" action="{{ route('login.post') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Địa chỉ Email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                            <input type="email" class="form-control border-start-0 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="name@tamanh.vn">
                        </div>
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label for="password" class="form-label fw-semibold">Mật khẩu <span class="text-danger">*</span></label>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-muted"></i></span>
                            <input type="password" class="form-control border-start-0 border-end-0 @error('password') is-invalid @enderror" id="password" name="password" required placeholder="Nhập mật khẩu của bạn">
                            <button class="btn btn-light border border-start-0 text-muted" type="button" id="togglePasswordBtn" title="Ẩn / Hiện mật khẩu">
                                <i class="bi bi-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4 d-flex justify-content-between align-items-center">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label small text-muted" for="remember">
                                Ghi nhớ đăng nhập
                            </label>
                        </div>
                        <a href="{{ route('booking.lookup') }}" class="small text-decoration-none text-primary">
                            Tra cứu lịch hẹn?
                        </a>
                    </div>

                    <button type="submit" id="loginSubmitBtn" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm">
                        <i class="bi bi-box-arrow-in-right me-1"></i> ĐĂNG NHẬP
                    </button>
                </form>

            </div>
            <div class="card-footer bg-light text-center py-3 border-0 small text-muted">
                Khách vãng lai muốn đặt lịch khám? <a href="{{ route('booking.create') }}" class="text-primary fw-semibold text-decoration-none">Đặt lịch trực tuyến tại đây</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const pwdInput = document.getElementById('password');
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const toggleIcon = document.getElementById('togglePasswordIcon');
    if (toggleBtn && pwdInput) {
        toggleBtn.addEventListener('click', function () {
            const isPwd = pwdInput.type === 'password';
            pwdInput.type = isPwd ? 'text' : 'password';
            toggleIcon.className = isPwd ? 'bi bi-eye-slash text-primary' : 'bi bi-eye';
        });
    }
</script>
@endpush
