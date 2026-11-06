@extends('layouts.master')

@section('title', 'Xác Thực 2 Bước (2FA) - Bệnh Viện Tâm Anh')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <div class="rounded-circle bg-white text-primary d-inline-flex align-items-center justify-content-center mb-2 shadow" style="width: 54px; height: 54px;">
                        <i class="bi bi-shield-lock-fill fs-3"></i>
                    </div>
                    <h4 class="fw-bold mb-1">XÁC THỰC BẢO MẬT 2 LỚP</h4>
                    <p class="mb-0 small text-white-50">Nhập mã OTP từ ứng dụng hoặc mã khôi phục</p>
                </div>

                <div class="card-body p-4">
                    @if(session('info'))
                        <div class="alert alert-info py-2 small mb-3">
                            <i class="bi bi-info-circle me-1"></i>{{ session('info') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger py-2 small mb-3">
                            @foreach($errors->all() as $error)
                                <div><i class="bi bi-exclamation-circle me-1"></i>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form action="{{ route('two-factor.verify') }}" method="POST" id="totpForm">
                        @csrf
                        <div id="totpGroup">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold text-dark mb-0">Mã xác thực TOTP (6 chữ số):</label>
                                <span class="badge bg-primary-subtle text-primary font-monospace" id="challengeTotpCountdown">--s</span>
                            </div>
                            <input type="text" name="code" id="challengeCodeInput" class="form-control form-control-lg text-center font-monospace tracking-widest fw-bold mb-2" placeholder="------" maxlength="6" autofocus pattern="[0-9]{6}" autocomplete="one-time-code" style="letter-spacing: 0.4em; font-size: 1.4rem;">
                            <div class="progress mb-2" style="height: 4px;">
                                <div id="challengeTotpBar" class="progress-bar bg-primary" style="width: 100%; transition: width 1s linear;"></div>
                            </div>
                            @if(isset($currentOtp) && config('app.debug'))
                                <div class="text-center mb-2">
                                    <button type="button" class="btn btn-sm btn-warning text-dark fw-semibold py-0 px-3 rounded-pill" onclick="document.getElementById('challengeCodeInput').value='{{ $currentOtp }}'">
                                        <i class="bi bi-magic me-1"></i>Điền nhanh OTP Demo: <span class="font-monospace">{{ $currentOtp }}</span>
                                    </button>
                                </div>
                            @endif
                            <p class="small text-muted mb-3 text-center">Mở ứng dụng Google/Microsoft Authenticator để lấy mã 6 chữ số.</p>
                        </div>

                        <div id="recoveryGroup" style="display: none;">
                            <label class="form-label fw-bold text-dark">Mã khôi phục khẩn cấp:</label>
                            <input type="text" name="recovery_code" class="form-control font-monospace mb-3" placeholder="xxxx-xxxx">
                            <p class="small text-muted mb-3 text-center">Sử dụng một trong các mã khôi phục dự phòng đã lưu.</p>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold mb-3">
                            <i class="bi bi-check2-circle me-1"></i>Xác nhận & Đăng nhập
                        </button>

                        <div class="text-center">
                            <button type="button" class="btn btn-link btn-sm text-decoration-none" id="toggleRecoveryBtn" onclick="toggleRecovery();">
                                Sử dụng mã khôi phục dự phòng
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card-footer bg-light text-center py-3">
                    <a href="{{ route('login') }}" class="small text-muted text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i>Quay lại trang Đăng nhập
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let isRecovery = false;
    function toggleRecovery() {
        isRecovery = !isRecovery;
        const totpGroup = document.getElementById('totpGroup');
        const recoveryGroup = document.getElementById('recoveryGroup');
        const btn = document.getElementById('toggleRecoveryBtn');

        if (isRecovery) {
            totpGroup.style.display = 'none';
            recoveryGroup.style.display = 'block';
            btn.innerText = 'Sử dụng mã ứng dụng xác thực (TOTP)';
        } else {
            totpGroup.style.display = 'block';
            recoveryGroup.style.display = 'none';
            btn.innerText = 'Sử dụng mã khôi phục dự phòng';
        }
    }

    function updateChallengeTimer() {
        const badge = document.getElementById('challengeTotpCountdown');
        const bar = document.getElementById('challengeTotpBar');
        if (!badge || !bar) return;
        const sec = 30 - (Math.floor(Date.now() / 1000) % 30);
        badge.textContent = 'Đổi mã sau ' + sec + 's';
        bar.style.width = ((sec / 30) * 100) + '%';
        bar.className = sec <= 5 ? 'progress-bar bg-danger' : 'progress-bar bg-primary';
    }
    updateChallengeTimer();
    setInterval(updateChallengeTimer, 1000);
</script>
@endsection
