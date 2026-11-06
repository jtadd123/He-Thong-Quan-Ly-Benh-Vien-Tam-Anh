@extends('layouts.master')

@section('title', 'Bảo Mật 2 Lớp (2FA) - Bệnh Viện Tâm Anh')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Quick Security Navigation Bar -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('two-factor.setup') }}" class="btn btn-sm btn-primary rounded-pill px-3">
                        <i class="bi bi-shield-lock-fill me-1"></i> Xác thực 2 bước (2FA)
                    </a>
                    <a href="{{ route('sessions.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="bi bi-display me-1"></i> Phiên thiết bị
                    </a>
                    <a href="{{ route('profile.password') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="bi bi-key me-1"></i> Đổi mật khẩu
                    </a>
                </div>
                @if(Auth::user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                        <i class="bi bi-speedometer2 me-1"></i> Về Dashboard Admin
                    </a>
                @elseif(Auth::user()->isPatient())
                    <a href="{{ route('patient.history') }}" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                        <i class="bi bi-journal-medical me-1"></i> Về Lịch sử khám
                    </a>
                @endif
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-shield-lock-fill me-2"></i>Xác Thực 2 Bước (Two-Factor Authentication - 2FA)</h5>
                    @if($isEnabled)
                        <span class="badge bg-success px-3 py-2"><i class="bi bi-check-circle-fill me-1"></i>ĐANG BẬT BẢO VỆ</span>
                    @else
                        <span class="badge bg-warning text-dark px-3 py-2"><i class="bi bi-exclamation-triangle-fill me-1"></i>CHƯA KÍCH HOẠT</span>
                    @endif
                </div>

                <div class="card-body p-4">
                    @if(!$isEnabled)
                        <div class="alert alert-info border-start border-4 border-info">
                            <h6 class="fw-bold mb-1"><i class="bi bi-info-circle-fill me-1"></i>Hướng dẫn kích hoạt bảo mật 2 lớp:</h6>
                            <p class="mb-0 small text-muted">
                                Xác thực 2 bước tăng cường lớp bảo vệ tối đa cho tài khoản của bạn. Sau khi kích hoạt, mỗi lần đăng nhập bạn cần nhập thêm mã OTP 6 chữ số từ ứng dụng như <strong>Google Authenticator</strong> hoặc <strong>Microsoft Authenticator</strong>.
                            </p>
                        </div>

                        <div class="row my-4 align-items-center">
                            <div class="col-md-5 text-center mb-3 mb-md-0">
                                <div class="p-3 bg-white border rounded shadow-sm d-inline-block">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($qrUrl) }}" alt="TOTP QR Code" class="img-fluid" style="width: 180px; height: 180px;">
                                </div>
                                <div class="mt-2 text-muted small">Quét mã bằng Google Authenticator</div>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label fw-bold small text-muted">HOẶC NHẬP MÃ THỦ CÔNG (SECRET KEY - BASE32):</label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control font-monospace fw-bold bg-light" value="{{ $secret }}" readonly id="secretKeyInput">
                                    <button class="btn btn-outline-primary" type="button" onclick="window.copyWithFeedback('{{ $secret }}', this, 'Đã sao chép khóa bí mật Secret Key (Base32)!')">
                                        <i class="bi bi-clipboard"></i> Sao chép
                                    </button>
                                </div>

                                <!-- Live RFC 6238 30s Window Countdown -->
                                <div class="p-2 px-3 bg-light border rounded mb-3 d-flex align-items-center justify-content-between">
                                    <span class="small text-muted"><i class="bi bi-clock-history text-primary me-1"></i>Chu kỳ mã TOTP RFC 6238 (30s):</span>
                                    <span class="badge bg-primary rounded-pill font-monospace" id="totpTimerBadge">--s</span>
                                </div>

                                <form action="{{ route('two-factor.confirm') }}" method="POST">
                                    @csrf
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label fw-bold mb-0">Nhập mã xác nhận 6 chữ số từ ứng dụng:</label>
                                        @if(isset($currentOtp) && config('app.debug'))
                                            <button type="button" class="btn btn-sm btn-warning text-dark fw-semibold py-0 px-2 rounded-pill" onclick="document.getElementById('totpConfirmCodeInput').value='{{ $currentOtp }}'">
                                                <i class="bi bi-magic me-1"></i>Điền OTP Demo: <span class="font-monospace">{{ $currentOtp }}</span>
                                            </button>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-2">
                                        <input type="text" name="code" id="totpConfirmCodeInput" class="form-control form-control-lg text-center font-monospace tracking-widest fw-bold" placeholder="000000" maxlength="6" required pattern="[0-9]{6}" autocomplete="off">
                                        <button type="submit" class="btn btn-success px-4 fw-bold">
                                            <i class="bi bi-check2-circle me-1"></i>Kích hoạt 2FA
                                        </button>
                                    </div>
                                    <small class="text-muted d-block mt-1">Mã xác thực thay đổi tự động sau mỗi 30 giây theo chuẩn HMAC-SHA1.</small>
                                </form>
                            </div>
                        </div>

                        @if(!empty($recoveryCodes))
                            <div class="mt-4 pt-3 border-top">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                    <h6 class="fw-bold text-danger mb-0"><i class="bi bi-key-fill me-1"></i>Mã khôi phục dự phòng (Recovery Codes):</h6>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.copyWithFeedback('{{ implode('\n', $recoveryCodes) }}', this, 'Đã sao chép toàn bộ mã khôi phục dự phòng!')">
                                            <i class="bi bi-clipboard-check me-1"></i>Sao chép tất cả
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="downloadRecoveryTxt('{{ implode('\n', $recoveryCodes) }}')">
                                            <i class="bi bi-download me-1"></i>Tải file (.txt)
                                        </button>
                                    </div>
                                </div>
                                <p class="small text-muted mb-2">Lưu lại các mã này ở nơi an toàn. Mỗi mã chỉ dùng được 01 lần khi bạn bị mất quyền truy cập thiết bị xác thực:</p>
                                <div class="row g-2">
                                    @foreach($recoveryCodes as $rc)
                                        <div class="col-6 col-md-3">
                                            <div class="p-2 bg-light border rounded text-center font-monospace small fw-bold text-secondary">
                                                {{ $rc }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    @else
                        <div class="p-4 bg-light rounded-3 text-center mb-4">
                            <i class="bi bi-shield-check text-success display-4 mb-2"></i>
                            <h5 class="fw-bold text-success mb-1">Tài khoản của bạn đang được bảo vệ an toàn với 2FA</h5>
                            <p class="text-muted small mb-0">Thời điểm xác nhận kích hoạt: {{ $user->two_factor_confirmed_at?->format('H:i d/m/Y') }}</p>
                        </div>

                        @if(!empty($recoveryCodes))
                            <div class="mb-4">
                                <h6 class="fw-bold mb-2"><i class="bi bi-key-fill text-primary me-1"></i>Mã khôi phục khẩn cấp còn lại:</h6>
                                <div class="row g-2">
                                    @foreach($recoveryCodes as $rc)
                                        <div class="col-6 col-md-3">
                                            <div class="p-2 bg-light border rounded text-center font-monospace small fw-bold text-secondary">
                                                {{ $rc }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="border-top pt-4">
                            <h6 class="fw-bold text-danger mb-2">Hủy kích hoạt xác thực 2 bước</h6>
                            <p class="small text-muted">Nếu bạn tắt 2FA, tài khoản của bạn sẽ chỉ được bảo vệ bởi mật khẩu đơn lẻ.</p>
                            <form action="{{ route('two-factor.disable') }}" method="POST" class="row g-2 align-items-center" onsubmit="return confirm('Bạn có chắc chắn muốn hủy kích hoạt 2FA không?');">
                                @csrf
                                @method('DELETE')
                                <div class="col-md-6">
                                    <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu hiện tại để xác nhận" required>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="bi bi-x-circle me-1"></i>Hủy kích hoạt 2FA
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function updateTotpCountdown() {
        const badge = document.getElementById('totpTimerBadge');
        if (!badge) return;
        const sec = 30 - (Math.floor(Date.now() / 1000) % 30);
        badge.textContent = 'Còn ' + sec + 's';
        badge.className = sec <= 5
            ? 'badge bg-danger rounded-pill font-monospace'
            : 'badge bg-primary rounded-pill font-monospace';
    }
    updateTotpCountdown();
    setInterval(updateTotpCountdown, 1000);

    function downloadRecoveryTxt(codesText) {
        const content = "MÃ KHÔI PHỤC KHẨN CẤP (2FA RECOVERY CODES) - BỆNH VIỆN TÂM ANH\n"
            + "Tài khoản: {{ Auth::user()->email }}\n"
            + "Ngày xuất: " + new Date().toLocaleString('vi-VN') + "\n"
            + "------------------------------------------------------------\n"
            + codesText + "\n"
            + "------------------------------------------------------------\n"
            + "Lưu ý: Mỗi mã chỉ sử dụng được 01 lần duy nhất.";
        const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'tamanh-2fa-recovery-codes.txt';
        link.click();
    }
</script>
@endpush
@endsection
