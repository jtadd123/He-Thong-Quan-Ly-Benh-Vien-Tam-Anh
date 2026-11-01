@extends('layouts.admin')

@section('title', 'Hồ sơ Bác sĩ: ' . $doctor->full_name_with_title)

@section('content')
<x-page-header title="{{ $doctor->full_name_with_title }}" subtitle="Hồ sơ chi tiết, lịch công tác và phòng khám chuyên khoa">
    <x-slot:actions>
        <a href="{{ route('admin.doctors.schedule', $doctor) }}" class="btn btn-outline-info d-inline-flex align-items-center gap-1">
            <i class="bi bi-calendar3"></i> Cấu hình lịch trực
        </a>
        <a href="{{ route('admin.doctors.leaves', $doctor) }}" class="btn btn-outline-warning d-inline-flex align-items-center gap-1">
            <i class="bi bi-calendar2-x"></i> Lịch nghỉ & Trực thay
        </a>
        <a href="{{ route('admin.doctors.edit', $doctor) }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-1">
            <i class="bi bi-pencil-square"></i> Chỉnh sửa
        </a>
        <a href="{{ route('admin.doctors.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </x-slot:actions>
</x-page-header>

<div class="row g-4">
    <!-- Doctor Profile Card -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4 text-center">
                <div class="rounded-circle bg-light text-primary d-inline-flex align-items-center justify-content-center mb-3 fs-1 overflow-hidden" style="width: 85px; height: 85px; border: 2px solid #e2e8f0;">
                    @if($doctor->avatar_url)
                        <img src="{{ $doctor->avatar_url }}" alt="{{ $doctor->full_name_with_title }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <i class="bi bi-person-badge"></i>
                    @endif
                </div>
                <h4 class="fw-bold text-dark mb-1">{{ $doctor->full_name_with_title }}</h4>
                <div class="mb-2">
                    @if($doctor->specialty)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill">
                            <i class="{{ $doctor->specialty->icon ?: 'bi bi-tag' }} me-1"></i> {{ $doctor->specialty->name }}
                        </span>
                    @endif
                </div>
                <div class="mt-2">
                    @if($doctor->is_available)
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill">
                            <i class="bi bi-check-circle me-1"></i> Sẵn sàng nhận khám
                        </span>
                    @else
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1 rounded-pill">
                            <i class="bi bi-pause-circle me-1"></i> Tạm dừng nhận khám
                        </span>
                    @endif
                </div>
            </div>
            <ul class="list-group list-group-flush border-top">
                <li class="list-group-item px-4 py-3 d-flex justify-content-between">
                    <span class="text-muted">Phòng khám:</span>
                    <span class="fw-semibold text-dark">{{ $doctor->room_number }}</span>
                </li>
                <li class="list-group-item px-4 py-3 d-flex justify-content-between">
                    <span class="text-muted">Giá khám:</span>
                    <span class="fw-bold text-primary">{{ number_format($doctor->consultation_fee, 0, ',', '.') }} VNĐ</span>
                </li>
                <li class="list-group-item px-4 py-3 d-flex justify-content-between">
                    <span class="text-muted">Email:</span>
                    <span class="text-dark">{{ $doctor->user ? $doctor->user->email : '—' }}</span>
                </li>
                <li class="list-group-item px-4 py-3 d-flex justify-content-between">
                    <span class="text-muted">Điện thoại:</span>
                    <span class="text-dark">{{ $doctor->user && $doctor->user->phone ? $doctor->user->phone : '—' }}</span>
                </li>
            </ul>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-transparent fw-bold py-3">Tiểu sử & Chuyên môn</div>
            <div class="card-body p-4 text-secondary">
                {{ $doctor->bio ?: 'Chưa cập nhật thông tin tiểu sử của bác sĩ.' }}
            </div>
        </div>
    </div>

    <!-- Doctor Schedules & Appointments -->
    <div class="col-lg-8">
        <!-- Schedule Overview -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-calendar-week text-primary me-2"></i> Lịch trực tuần hiện tại
                </h5>
                <a href="{{ route('admin.doctors.schedule', $doctor) }}" class="btn btn-sm btn-outline-info">
                    <i class="bi bi-gear"></i> Tùy chỉnh lịch trực
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Thứ trong tuần</th>
                                <th>Ca trực</th>
                                <th>Khung giờ</th>
                                <th class="text-center">Số bệnh nhân tối đa</th>
                                <th class="text-center pe-3">Trạng thái ca</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($doctor->schedules->sortBy('id') as $sched)
                                <tr>
                                    <td class="ps-3 fw-semibold text-dark">{{ $sched->day_name }}</td>
                                    <td>
                                        <span class="badge {{ $sched->shift === 'morning' ? 'bg-warning-subtle text-warning border border-warning-subtle' : 'bg-info-subtle text-info border border-info-subtle' }} rounded-pill px-2 py-1">
                                            {{ $sched->shift === 'morning' ? 'Sáng' : 'Chiều' }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ substr($sched->start_time, 0, 5) }} - {{ substr($sched->end_time, 0, 5) }}</small>
                                    </td>
                                    <td class="text-center fw-medium">
                                        {{ $sched->max_patients }} lượt
                                    </td>
                                    <td class="text-center pe-3">
                                        @if($sched->is_active)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Hoạt động</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">Nghỉ trực</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-3 text-muted">Chưa cấu hình lịch trực.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Appointments -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-transparent py-3">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-clock-history text-primary me-2"></i> Lịch hẹn khám gần nhất
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Mã hẹn</th>
                                <th>Bệnh nhân</th>
                                <th>Ngày khám</th>
                                <th>Ca</th>
                                <th class="text-center pe-3">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($doctor->appointments as $app)
                                <tr>
                                    <td class="ps-3 fw-bold text-primary">{{ $app->appointment_code }}</td>
                                    <td>{{ $app->patient_name ?? ($app->patient && $app->patient->user ? $app->patient->user->name : '—') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($app->appointment_date)->format('d/m/Y') }}</td>
                                    <td>{{ $app->shift === 'morning' ? 'Sáng' : 'Chiều' }}</td>
                                    <td class="text-center pe-3">
                                        <span class="badge {{ $app->status_badge_class ?? 'bg-secondary' }} rounded-pill px-2 py-1">
                                            {{ $app->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Chưa có lịch hẹn khám nào.</td>
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
