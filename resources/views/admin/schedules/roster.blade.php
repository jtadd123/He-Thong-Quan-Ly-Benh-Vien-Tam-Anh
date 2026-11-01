@extends('layouts.admin')

@section('title', 'Ma Trận Ca Trực Bệnh Viện & Phát Hiện Xung Đột')

@section('content')
<x-page-header title="Ma Trận Phân Ca Trực Tuần Bệnh Viện" subtitle="Giám sát tổng thể lịch trực tuần 7 ngày, phát hiện xung đột trùng phòng và thiếu hụt bác sĩ">
    <x-slot:actions>
        <a href="{{ route('admin.doctors.index') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-1">
            <i class="bi bi-people"></i> Quản lý bác sĩ
        </a>
        <a href="{{ route('admin.specialties.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-diagram-3"></i> Quản lý chuyên khoa
        </a>
    </x-slot:actions>
</x-page-header>

<!-- Quick Stats -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center fs-4" style="width: 50px; height: 50px;">
                    <i class="bi bi-calendar-week-fill"></i>
                </div>
                <div>
                    <span class="text-muted small">Tổng ca trực tuần</span>
                    <h4 class="fw-bold text-dark mb-0">{{ $stats['total_shifts'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 text-success d-inline-flex align-items-center justify-content-center fs-4" style="width: 50px; height: 50px;">
                    <i class="bi bi-person-check-fill"></i>
                </div>
                <div>
                    <span class="text-muted small">Bác sĩ tham gia trực</span>
                    <h4 class="fw-bold text-success mb-0">{{ $stats['active_doctors_rostered'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle {{ $stats['conflicts_count'] > 0 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-light text-muted' }} d-inline-flex align-items-center justify-content-center fs-4" style="width: 50px; height: 50px;">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div>
                    <span class="text-muted small">Xung đột lịch / trùng phòng</span>
                    <h4 class="fw-bold {{ $stats['conflicts_count'] > 0 ? 'text-danger' : 'text-muted' }} mb-0">{{ $stats['conflicts_count'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle {{ $stats['coverage_warnings_count'] > 0 ? 'bg-warning bg-opacity-10 text-warning' : 'bg-light text-muted' }} d-inline-flex align-items-center justify-content-center fs-4" style="width: 50px; height: 50px;">
                    <i class="bi bi-shield-exclamation"></i>
                </div>
                <div>
                    <span class="text-muted small">Cảnh báo thiếu ca</span>
                    <h4 class="fw-bold {{ $stats['coverage_warnings_count'] > 0 ? 'text-warning' : 'text-muted' }} mb-0">{{ $stats['coverage_warnings_count'] }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Conflict Alert Banner (If Any) -->
@if(!empty($conflicts))
    <div class="alert alert-danger border-0 shadow-sm rounded-4 p-4 mb-4">
        <div class="d-flex align-items-start gap-3">
            <div class="fs-2 text-danger">
                <i class="bi bi-exclamation-octagon-fill"></i>
            </div>
            <div class="w-100">
                <h6 class="alert-heading fw-bold mb-2">PHÁT HIỆN {{ count($conflicts) }} XUNG ĐỘT TRONG MA TRẬN CA TRỰC!</h6>
                <ul class="mb-0 ps-3 small">
                    @foreach($conflicts as $conf)
                        <li class="mb-1">
                            <strong>[{{ $conf['day'] }} - {{ $conf['shift'] }}]</strong>: {{ $conf['message'] }}
                            @if(isset($conf['doctor_id']))
                                <a href="{{ route('admin.doctors.schedule', $conf['doctor_id']) }}" class="btn btn-sm btn-link p-0 text-danger fw-bold ms-2">
                                    [Điều chỉnh lịch bác sĩ &rarr;]
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<!-- Filter Bar -->
<div class="card shadow-sm border-0 rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.roster') }}" class="row g-2 align-items-center">
            <div class="col-md-5 col-lg-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-filter"></i></span>
                    <select name="specialty_id" class="form-select border-0 bg-light" onchange="this.form.submit()">
                        <option value="">-- Tất cả Chuyên khoa --</option>
                        @foreach($specialties as $spec)
                            <option value="{{ $spec->id }}" {{ $selectedSpecialtyId == $spec->id ? 'selected' : '' }}>
                                {{ $spec->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-auto">
                @if($selectedSpecialtyId)
                    <a href="{{ route('admin.roster') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                        <i class="bi bi-x-circle me-1"></i> Xóa lọc
                    </a>
                @endif
            </div>
            <div class="col text-end text-muted small">
                <span class="badge bg-primary me-1">&bull;</span> Ca Sáng: 07:30 - 11:30 &nbsp;|&nbsp;
                <span class="badge bg-warning text-dark me-1">&bull;</span> Ca Chiều: 13:30 - 17:00
            </div>
        </form>
    </div>
</div>

<!-- Weekly Roster Matrix Table -->
<div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="bi bi-grid-3x3-gap-fill text-primary me-2"></i> Bảng Lưới Phân Bổ 7 Ngày Trong Tuần
        </h6>
        <span class="badge bg-light text-muted border">Tuần làm việc hiện tại</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered align-top mb-0" style="min-width: 1000px;">
                <thead class="table-light text-center">
                    <tr>
                        <th style="width: 130px;" class="py-3 bg-light text-secondary">Ca làm việc</th>
                        @foreach($days as $dayKey => $dayLabel)
                            <th class="py-3 text-dark fw-bold {{ in_array($dayKey, ['saturday', 'sunday']) ? 'bg-light text-primary' : '' }}">
                                {{ $dayLabel }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($shifts as $shiftKey => $shiftMeta)
                        <tr>
                            <!-- Shift Label Header Column -->
                            <td class="text-center align-middle bg-light p-3">
                                <span class="badge {{ $shiftMeta['badge'] }} px-2 py-1 rounded-pill mb-1">
                                    {{ $shiftMeta['label'] }}
                                </span>
                                <div class="text-muted small fw-semibold">{{ $shiftMeta['time'] }}</div>
                            </td>

                            <!-- 7 Days Grid Cells -->
                            @foreach($days as $dayKey => $dayLabel)
                                @php
                                    $slotDocs = $matrix[$dayKey][$shiftKey];
                                @endphp
                                <td class="p-2 {{ in_array($dayKey, ['saturday', 'sunday']) ? 'bg-light bg-opacity-25' : '' }}" style="min-height: 120px; width: 12.5%;">
                                    @if($slotDocs->isNotEmpty())
                                        <div class="d-flex flex-column gap-2">
                                            @foreach($slotDocs as $item)
                                                <div class="p-2 rounded-3 border bg-white shadow-xs">
                                                    <div class="d-flex align-items-center gap-2 mb-1">
                                                        <div class="rounded-circle overflow-hidden bg-light flex-shrink-0" style="width: 28px; height: 28px;">
                                                            @if($item->doctor->avatar_url)
                                                                <img src="{{ $item->doctor->avatar_url }}" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                                            @else
                                                                <i class="bi bi-person text-secondary small d-flex align-items-center justify-content-center h-100"></i>
                                                            @endif
                                                        </div>
                                                        <div class="overflow-hidden">
                                                            <a href="{{ route('admin.doctors.show', $item->doctor) }}" class="text-decoration-none fw-bold text-dark small d-block text-truncate" title="{{ $item->doctor->full_name_with_title }}">
                                                                {{ $item->doctor->full_name_with_title }}
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center small" style="font-size: 0.72rem;">
                                                        <span class="text-muted text-truncate" style="max-width: 75px;" title="{{ $item->doctor->room_number }}">
                                                            <i class="bi bi-door-open text-primary"></i> {{ $item->doctor->room_number ?: 'P.Khám' }}
                                                        </span>
                                                        <span class="badge bg-light text-muted border" title="Giới hạn bệnh nhân tối đa">
                                                            Max {{ $item->max_patients }}
                                                        </span>
                                                    </div>
                                                    @if($item->doctor->specialty)
                                                        <div class="mt-1">
                                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-1" style="font-size: 0.65rem;">
                                                                {{ $item->doctor->specialty->name }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-4 text-muted small">
                                            <span class="text-black-50">&mdash; Trống &mdash;</span>
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
