@extends('layouts.admin')

@section('title', 'Cấu Hình Hệ Thống Bệnh Viện - Bệnh Viện Tâm Anh')

@section('content')
<x-page-header title="Cấu Hình Hệ Thống Bệnh Viện" subtitle="Quản lý tham số vận hành bệnh viện, cổng đặt lịch trực tuyến, hàng đợi và bảo mật" />

<form action="{{ route('admin.settings.update') }}" method="POST">
    @csrf

    <div class="row g-4">
        <!-- General Info -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-hospital me-2"></i>Thông Tin Bệnh Viện</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Tên chính thức bệnh viện:</label>
                        <input type="text" name="settings[hospital_name]" class="form-control" value="{{ \App\Models\SystemSetting::get('hospital_name') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Hotline CSKH & Cấp cứu:</label>
                        <input type="text" name="settings[hospital_hotline]" class="form-control" value="{{ \App\Models\SystemSetting::get('hospital_hotline') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Địa chỉ trụ sở chính:</label>
                        <input type="text" name="settings[hospital_address]" class="form-control" value="{{ \App\Models\SystemSetting::get('hospital_address') }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking & Queue Control -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="fw-bold mb-0 text-success"><i class="bi bi-calendar2-check me-2"></i>Cấu Hình Cổng Đặt Lịch & Hàng Đợi</h5>
                </div>
                <div class="card-body p-4">
                    <div class="form-check form-switch mb-3 p-3 bg-light rounded border">
                        <input type="hidden" name="settings[booking_maintenance_mode]" value="0">
                        <input class="form-check-input ms-0 me-3" type="checkbox" name="settings[booking_maintenance_mode]" value="1" id="maintSwitch" {{ \App\Models\SystemSetting::isTrue('booking_maintenance_mode') ? 'checked' : '' }} onchange="updateMaintPreview()">
                        <label class="form-check-label fw-bold text-danger" for="maintSwitch">
                            Bật chế độ bảo trì cổng đặt lịch (Maintenance Mode - HTTP 503)
                        </label>
                        <div class="small text-muted mt-1">Khi bật, bệnh nhân vãng lai sẽ thấy thông báo bảo trì, quản trị viên vẫn có thể truy cập để kiểm thử.</div>
                    </div>

                    <!-- Live Status Preview Box -->
                    <div id="maintLivePreview" class="p-3 rounded-3 border mb-3 d-flex align-items-center justify-content-between {{ \App\Models\SystemSetting::isTrue('booking_maintenance_mode') ? 'bg-warning-subtle border-warning text-dark' : 'bg-success-subtle border-success text-success-emphasis' }}">
                        <div class="d-flex align-items-center gap-2">
                            <i id="maintPreviewIcon" class="bi {{ \App\Models\SystemSetting::isTrue('booking_maintenance_mode') ? 'bi-tools text-warning' : 'bi-check-circle-fill text-success' }} fs-4"></i>
                            <div>
                                <div class="fw-bold small" id="maintPreviewTitle">
                                    {{ \App\Models\SystemSetting::isTrue('booking_maintenance_mode') ? 'TRẠNG THÁI DỰ KIẾN: ĐANG BẢO TRÌ (HTTP 503)' : 'TRẠNG THÁI DỰ KIẾN: ĐANG MỞ CỬA ĐÓN KHÁCH (200 OK)' }}
                                </div>
                                <div class="small opacity-75" id="maintPreviewSub">
                                    {{ \App\Models\SystemSetting::isTrue('booking_maintenance_mode') ? 'Khách vãng lai truy cập /booking sẽ nhận màn hình bảo trì.' : 'Bệnh nhân có thể đặt lịch khám trực tuyến bình thường 24/7.' }}
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('booking.create') }}" target="_blank" class="btn btn-sm btn-outline-dark text-nowrap">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Mở trang /booking
                        </a>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Thông báo hiển thị khi bảo trì:</label>
                        <textarea name="settings[booking_maintenance_message]" class="form-control" rows="2">{{ \App\Models\SystemSetting::get('booking_maintenance_message') }}</textarea>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="settings[enable_smart_waitlist]" value="0">
                        <input class="form-check-input" type="checkbox" name="settings[enable_smart_waitlist]" value="1" id="waitlistSwitch" {{ \App\Models\SystemSetting::isTrue('enable_smart_waitlist', true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="waitlistSwitch">
                            Kích hoạt Hàng Đợi Chờ Thông Minh (Smart Waitlist Engine)
                        </label>
                        <div class="small text-muted">Tự động cho phép bệnh nhân đăng ký hàng đợi khi ca khám đầy và tự động đôn người khi có ca hủy.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Số ngày tối đa cho phép đặt lịch trước:</label>
                        <input type="number" name="settings[max_booking_days_ahead]" class="form-control" value="{{ \App\Models\SystemSetting::get('max_booking_days_ahead', 30) }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Security & Audit Control -->
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-shield-check me-2"></i>Quy Chuẩn An Ninh & Giới Hạn Tần Suất</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Giới hạn số lần đặt lịch tối đa mỗi IP (trong 5 phút):</label>
                            <input type="number" name="settings[rate_limit_booking]" class="form-control" value="{{ \App\Models\SystemSetting::get('rate_limit_booking', 15) }}">
                            <small class="text-muted">Chống spam bot và tấn công cạn kiệt tài nguyên (DDoS/Spam attack).</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Thời gian lưu trữ nhật ký kiểm toán bảo mật (ngày):</label>
                            <input type="number" name="settings[audit_log_retention_days]" class="form-control" value="{{ \App\Models\SystemSetting::get('audit_log_retention_days', 90) }}">
                            <small class="text-muted">Dữ liệu kiểm toán bảo mật sẽ được lưu trữ tuân thủ tiêu chuẩn an toàn y tế.</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light py-3 text-end">
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="bi bi-save me-1"></i>Lưu cấu hình hệ thống
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    function updateMaintPreview() {
        const sw = document.getElementById('maintSwitch');
        const box = document.getElementById('maintLivePreview');
        const icon = document.getElementById('maintPreviewIcon');
        const title = document.getElementById('maintPreviewTitle');
        const sub = document.getElementById('maintPreviewSub');
        if (!sw || !box) return;

        if (sw.checked) {
            box.className = 'p-3 rounded-3 border mb-3 d-flex align-items-center justify-content-between bg-warning-subtle border-warning text-dark';
            icon.className = 'bi bi-tools text-warning fs-4';
            title.textContent = 'TRẠNG THÁI DỰ KIẾN: ĐANG BẢO TRÌ (HTTP 503)';
            sub.textContent = 'Khách vãng lai truy cập /booking sẽ nhận màn hình bảo trì.';
        } else {
            box.className = 'p-3 rounded-3 border mb-3 d-flex align-items-center justify-content-between bg-success-subtle border-success text-success-emphasis';
            icon.className = 'bi bi-check-circle-fill text-success fs-4';
            title.textContent = 'TRẠNG THÁI DỰ KIẾN: ĐANG MỞ CỬA ĐÓN KHÁCH (200 OK)';
            sub.textContent = 'Bệnh nhân có thể đặt lịch khám trực tuyến bình thường 24/7.';
        }
    }
</script>
@endsection
