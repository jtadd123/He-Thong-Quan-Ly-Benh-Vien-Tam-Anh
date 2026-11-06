@extends('layouts.master')

@section('title', 'Đổi Lịch Hẹn Khám - ' . $appointment->appointment_code)

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="mb-3">
                <a href="{{ route('patient.history') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Quay lại Lịch sử khám bệnh
                </a>
            </div>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-calendar2-range me-2"></i>Đổi Lịch Hẹn Khám Bệnh Trực Tuyến</h5>
                    <span class="badge bg-white text-primary font-monospace">{{ $appointment->appointment_code }}</span>
                </div>

                <div class="card-body p-4">
                    <!-- Current Appointment Info Box -->
                    <div class="p-3 bg-light rounded-3 border mb-4">
                        <span class="badge bg-secondary text-uppercase mb-2">Thông tin lịch hẹn hiện tại</span>
                        <div class="row g-2 small">
                            <div class="col-6"><strong>Mã lịch hẹn:</strong> <span class="font-monospace text-primary fw-bold">{{ $appointment->appointment_code }}</span></div>
                            <div class="col-6"><strong>Bệnh nhân:</strong> {{ $appointment->patientProfile->full_name }}</div>
                            <div class="col-6"><strong>Bác sĩ:</strong> {{ $appointment->doctor->full_name_with_title }}</div>
                            <div class="col-6"><strong>Chuyên khoa:</strong> {{ $appointment->specialty->name }}</div>
                            <div class="col-12 text-danger mt-1">
                                <strong>Lịch hiện tại:</strong> Ngày {{ $appointment->appointment_date->format('d/m/Y') }} (Ca: {{ $appointment->shift == 'morning' ? 'Sáng (07:30 - 11:30)' : 'Chiều (13:30 - 17:00)' }})
                            </div>
                        </div>
                    </div>

                    <!-- Reschedule Form -->
                    <form action="{{ route('patient.appointments.reschedule.process', $appointment) }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Chọn ngày khám mới: <span class="text-danger">*</span></label>
                            <input type="date" name="appointment_date" id="rescheduleDateInput" class="form-control form-control-lg" min="{{ date('Y-m-d') }}" max="{{ now()->addDays(max(1, (int) \App\Models\SystemSetting::get('max_booking_days_ahead', 30)))->format('Y-m-d') }}" value="{{ old('appointment_date', $appointment->appointment_date->format('Y-m-d')) }}" required>
                            <small class="text-muted">Chỉ có thể chọn ngày trong tương lai khi bác sĩ có lịch công tác.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Chọn ca khám mới: <span class="text-danger">*</span></label>
                            <select name="shift" id="rescheduleShiftSelect" class="form-select form-select-lg" required>
                                <option value="morning" {{ old('shift', $appointment->shift) == 'morning' ? 'selected' : '' }}>Ca Sáng (07:30 - 11:30)</option>
                                <option value="afternoon" {{ old('shift', $appointment->shift) == 'afternoon' ? 'selected' : '' }}>Ca Chiều (13:30 - 17:00)</option>
                            </select>
                            <div id="rescheduleSlotNotice" class="mt-2"></div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Lý do thay đổi lịch khám (tùy chọn):</label>
                            <textarea name="reschedule_reason" class="form-control" rows="3" placeholder="Ví dụ: Bận việc đột xuất, đổi kế hoạch công tác...">{{ old('reschedule_reason') }}</textarea>
                        </div>

                        <div class="p-3 bg-info-subtle border border-info-subtle rounded-3 small text-dark mb-4">
                            <i class="bi bi-info-circle-fill text-info me-1"></i>
                            <strong>Cơ chế Điều phối Thông minh:</strong> Ngay khi Quý khách đổi sang ngày mới, vị trí ca cũ sẽ được giải phóng và tự động đôn bệnh nhân đang ở <strong>Hàng đợi chờ (Smart Waitlist FIFO)</strong> lên thay thế.
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('patient.history') }}" class="btn btn-outline-secondary px-3">Hủy bỏ</a>
                            <button type="submit" class="btn btn-primary fw-bold px-4" onclick="return confirm('Bạn có chắc chắn muốn đổi sang ngày khám mới này không?');">
                                <i class="bi bi-check2-circle me-1"></i>Xác nhận đổi lịch
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dateInput = document.getElementById('rescheduleDateInput');
        const shiftSelect = document.getElementById('rescheduleShiftSelect');
        const noticeBox = document.getElementById('rescheduleSlotNotice');
        const doctorId = '{{ $appointment->doctor_id }}';

        function checkRescheduleSlots() {
            const dVal = dateInput?.value;
            if (!dVal || !noticeBox) return;

            fetch(`{{ url('/booking/doctor-slots') }}?doctor_id=${doctorId}&date=${dVal}`)
                .then(res => res.json())
                .then(data => {
                    if (data.slots && data.slots.length > 0) {
                        let html = '<div class="alert alert-info py-2 px-3 mb-0 small"><i class="bi bi-calendar-check me-1"></i><strong>Tình trạng lịch trực bác sĩ ngày ' + data.date + ':</strong><ul class="mb-0 ps-3 mt-1">';
                        data.slots.forEach(s => {
                            if (s.available) {
                                html += `<li class="text-success">${s.shift_name}: Còn <strong>${s.remaining}</strong> chỗ trống (Đã nhận ${s.booked_count}/${s.max_patients})</li>`;
                            } else {
                                html += `<li class="text-danger">${s.shift_name}: <strong>Đã đầy (${s.booked_count}/${s.max_patients})</strong></li>`;
                            }
                        });
                        html += '</ul></div>';
                        noticeBox.innerHTML = html;
                    } else {
                        noticeBox.innerHTML = '<div class="alert alert-warning py-2 px-3 mb-0 small"><i class="bi bi-exclamation-triangle me-1"></i>Bác sĩ chưa có lịch phân ca vào ngày này. Vui lòng chọn ngày khác.</div>';
                    }
                })
                .catch(() => {});
        }

        if (dateInput) dateInput.addEventListener('change', checkRescheduleSlots);
        if (shiftSelect) shiftSelect.addEventListener('change', checkRescheduleSlots);
        checkRescheduleSlots();
    });
</script>
@endpush
@endsection
