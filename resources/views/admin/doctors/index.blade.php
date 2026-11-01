@extends('layouts.admin')

@section('title', 'Quản lý Bác sĩ & Lịch trực')

@section('content')
<x-page-header title="Quản lý Bác sĩ" subtitle="Danh sách đội ngũ bác sĩ, chuyên khoa phân công và lịch khám bệnh">
    <x-slot:actions>
        <a href="{{ route('admin.roster') }}" class="btn btn-outline-info d-inline-flex align-items-center gap-1">
            <i class="bi bi-grid-3x3-gap-fill"></i> Ma trận ca trực tuần
        </a>
        <a href="{{ route('admin.doctors.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1">
            <i class="bi bi-person-plus-fill"></i> Thêm bác sĩ mới
        </a>
    </x-slot:actions>
</x-page-header>

<!-- Quick Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center fs-4" style="width: 50px; height: 50px;">
                    <i class="bi bi-person-badge-fill"></i>
                </div>
                <div>
                    <span class="text-muted small">Tổng số Bác sĩ</span>
                    <h4 class="fw-bold text-dark mb-0">{{ $stats['total'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 text-success d-inline-flex align-items-center justify-content-center fs-4" style="width: 50px; height: 50px;">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <span class="text-muted small">Sẵn sàng nhận khám</span>
                    <h4 class="fw-bold text-success mb-0">{{ $stats['available'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-inline-flex align-items-center justify-content-center fs-4" style="width: 50px; height: 50px;">
                    <i class="bi bi-slash-circle-fill"></i>
                </div>
                <div>
                    <span class="text-muted small">Tạm dừng nhận khám</span>
                    <h4 class="fw-bold text-warning mb-0">{{ $stats['unavailable'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 text-info d-inline-flex align-items-center justify-content-center fs-4" style="width: 50px; height: 50px;">
                    <i class="bi bi-door-open-fill"></i>
                </div>
                <div>
                    <span class="text-muted small">Phòng khám vận hành</span>
                    <h4 class="fw-bold text-info mb-0">{{ $stats['rooms_count'] }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.doctors.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5 col-lg-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0" placeholder="Tìm tên bác sĩ, phòng khám, email...">
                </div>
            </div>
            <div class="col-md-3 col-lg-3">
                <select name="specialty_id" class="form-select">
                    <option value="">-- Tất cả chuyên khoa --</option>
                    @foreach($specialties as $sp)
                        <option value="{{ $sp->id }}" {{ request('specialty_id') == $sp->id ? 'selected' : '' }}>
                            {{ $sp->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-lg-2">
                <select name="is_available" class="form-select">
                    <option value="">-- Trạng thái khám --</option>
                    <option value="1" {{ request('is_available') === '1' ? 'selected' : '' }}>Sẵn sàng nhận khám</option>
                    <option value="0" {{ request('is_available') === '0' ? 'selected' : '' }}>Tạm dừng nhận khám</option>
                </select>
            </div>
            <div class="col-md-2 col-lg-3 d-flex gap-2">
                <button type="submit" class="btn btn-secondary flex-grow-1">
                    <i class="bi bi-funnel"></i> Lọc
                </button>
                @if(request('search') || request('specialty_id') || request()->filled('is_available'))
                    <a href="{{ route('admin.doctors.index') }}" class="btn btn-outline-secondary" title="Đặt lại bộ lọc">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Doctors Table Card -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 60px;">ID</th>
                        <th>Bác sĩ</th>
                        <th>Chuyên khoa</th>
                        <th>Phòng khám</th>
                        <th>Giá khám</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-end pe-3" style="width: 170px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($doctors as $doctor)
                        <tr>
                            <td class="ps-3 text-muted fw-bold">#{{ $doctor->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-light text-primary d-flex align-items-center justify-content-center fw-bold overflow-hidden" style="width: 42px; height: 42px; border: 1px solid #e2e8f0;">
                                        @if($doctor->avatar_url)
                                            <img src="{{ $doctor->avatar_url }}" alt="{{ $doctor->full_name_with_title }}" style="width: 100%; height: 100%; object-fit: cover;">
                                        @else
                                            <i class="bi bi-person-fill fs-5"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $doctor->full_name_with_title }}</div>
                                        <small class="text-muted">{{ $doctor->user ? $doctor->user->email : '—' }}</small>
                                        @if($doctor->user && $doctor->user->phone)
                                            <span class="text-muted small ms-1">({{ $doctor->user->phone }})</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($doctor->specialty)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1">
                                        <i class="{{ $doctor->specialty->icon ?: 'bi bi-tag' }} me-1"></i> {{ $doctor->specialty->name }}
                                    </span>
                                @else
                                    <span class="text-muted">Chưa phân khoa</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-door-open me-1"></i> {{ $doctor->room_number }}
                                </span>
                            </td>
                            <td class="fw-semibold text-primary">
                                {{ number_format($doctor->consultation_fee, 0, ',', '.') }} đ
                            </td>
                            <td class="text-center">
                                <form method="POST" action="{{ route('admin.doctors.toggle-availability', $doctor) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent" title="Bấm để đổi trạng thái nhận khám">
                                        @if($doctor->is_available)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                                <i class="bi bi-check-circle me-1"></i> Sẵn sàng
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill">
                                                <i class="bi bi-slash-circle me-1"></i> Tạm dừng
                                            </span>
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.doctors.show', $doctor) }}" class="btn btn-outline-secondary" title="Xem hồ sơ">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.doctors.schedule', $doctor) }}" class="btn btn-outline-info" title="Lịch trực tuần">
                                        <i class="bi bi-calendar3"></i>
                                    </a>
                                    <a href="{{ route('admin.doctors.edit', $doctor) }}" class="btn btn-outline-primary" title="Chỉnh sửa">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.doctors.destroy', $doctor) }}" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa hồ sơ bác sĩ này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Xóa bác sĩ">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Không tìm thấy bác sĩ nào phù hợp.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($doctors->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $doctors->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
