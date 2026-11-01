@extends('layouts.master')

@section('title', $doctor->full_name_with_title . ' - Bệnh Viện Đa Khoa Tâm Anh')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-decoration-none">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('public.doctors.index') }}" class="text-decoration-none">Danh bạ Bác sĩ</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $doctor->full_name_with_title }}</li>
        </ol>
    </nav>
</div>

<div class="row g-4">
    <!-- Doctor Card -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
            <div class="card-body p-4 text-center">
                <div class="rounded-circle bg-light text-primary d-inline-flex align-items-center justify-content-center mb-3 fs-1 overflow-hidden" style="width: 100px; height: 100px; border: 3px solid #e2e8f0;">
                    @if($doctor->avatar_url)
                        <img src="{{ $doctor->avatar_url }}" alt="{{ $doctor->full_name_with_title }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <i class="bi bi-person-badge"></i>
                    @endif
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ $doctor->full_name_with_title }}</h3>
                <div class="mb-3">
                    @if($doctor->specialty)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
                            <i class="{{ $doctor->specialty->icon ?: 'bi bi-tag' }} me-1"></i> {{ $doctor->specialty->name }}
                        </span>
                    @endif
                </div>

                <div class="p-3 bg-light rounded-3 mb-3 text-start">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Phòng khám:</span>
                        <span class="fw-semibold text-dark">{{ $doctor->room_number }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Giá khám:</span>
                        <span class="fw-bold text-primary">{{ number_format($doctor->consultation_fee, 0, ',', '.') }} VNĐ</span>
                    </div>
                </div>

                <div class="d-grid">
                    <a href="{{ route('booking.create') }}?doctor_id={{ $doctor->id }}&specialty_id={{ $doctor->specialty_id }}" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm">
                        <i class="bi bi-calendar-check me-1"></i> Đặt lịch khám với Bác sĩ
                    </a>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white py-3 fw-bold border-bottom">
                <i class="bi bi-shield-check text-success me-2"></i> Cam kết dịch vụ y tế
            </div>
            <div class="card-body p-4">
                <ul class="list-unstyled mb-0 d-flex flex-column gap-2 small text-secondary">
                    <li><i class="bi bi-check2-circle text-primary me-2"></i> Bác sĩ trực tiếp thăm khám và tư vấn phác đồ chuẩn y khoa.</li>
                    <li><i class="bi bi-check2-circle text-primary me-2"></i> Trang thiết bị chẩn đoán hình ảnh và xét nghiệm tân tiến.</li>
                    <li><i class="bi bi-check2-circle text-primary me-2"></i> Tiếp nhận đúng giờ hẹn, không phải chờ đợi lâu.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Doctor Details & Schedules -->
    <div class="col-lg-8">
        <!-- Bio -->
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-journal-text text-primary me-2"></i> Lý lịch chuyên môn & Tiểu sử
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="lh-lg text-secondary">
                    {!! nl2br(e($doctor->bio ?: 'Bác sĩ chuyên khoa tại Bệnh viện Đa khoa Tâm Anh với nhiều năm kinh nghiệm khám và điều trị chuyên sâu, luôn đặt sự an tâm và sức khỏe của bệnh nhân lên hàng đầu.')) !!}
                </div>
            </div>
        </div>

        <!-- Weekly Schedule -->
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-calendar-week text-primary me-2"></i> Lịch trực khám bệnh trong tuần
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Thứ trong tuần</th>
                                <th>Ca trực</th>
                                <th>Khung giờ làm việc</th>
                                <th class="text-end pe-4">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($doctor->schedules->sortBy('id') as $sched)
                                @php
                                    $isToday = (strtolower(now()->format('l')) === $sched->day_of_week);
                                @endphp
                                <tr class="{{ $isToday ? 'table-warning bg-opacity-25' : '' }}">
                                    <td class="ps-4 fw-bold text-dark">
                                        {{ $sched->day_name }}
                                        @if($isToday)
                                            <span class="badge bg-warning text-dark border border-warning ms-1" style="font-size: 0.7rem;">
                                                <i class="bi bi-star-fill me-1"></i> Hôm nay
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $sched->shift === 'morning' ? 'bg-warning-subtle text-warning border border-warning-subtle' : 'bg-info-subtle text-info border border-info-subtle' }} rounded-pill px-3 py-1">
                                            {{ $sched->shift === 'morning' ? 'Ca Sáng' : 'Ca Chiều' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-secondary fw-medium">{{ substr($sched->start_time, 0, 5) }} - {{ substr($sched->end_time, 0, 5) }}</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('booking.create') }}?doctor_id={{ $doctor->id }}&specialty_id={{ $doctor->specialty_id }}&shift={{ $sched->shift }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                            Chọn ca này
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        Hiện chưa có lịch trực nào được công bố cho bác sĩ này.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
