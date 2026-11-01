@extends('layouts.admin')

@section('title', 'Lịch Nghỉ Phép & Trực Thay - ' . $doctor->full_name_with_title)

@section('content')
<x-page-header title="Quản Lý Nghỉ Phép & Ủy Quyền Trực Thay" subtitle="Thiết lập vắng mặt, hội thảo y khoa và bác sĩ trực thay cho {{ $doctor->full_name_with_title }}">
    <x-slot:actions>
        <a href="{{ route('admin.doctors.show', $doctor) }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-1">
            <i class="bi bi-person"></i> Xem hồ sơ bác sĩ
        </a>
        <a href="{{ route('admin.doctors.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Danh sách bác sĩ
        </a>
    </x-slot:actions>
</x-page-header>

<div class="row g-4">
    <!-- Leave Registration Form -->
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-calendar-plus text-primary me-2"></i> Đăng Ký Lịch Vắng Mặt / Nghỉ Phép
                </h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.doctors.leaves.store', $doctor) }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="reason" class="form-label fw-semibold">Lý do nghỉ / Công tác <span class="text-danger">*</span></label>
                        <select name="reason" id="reason" class="form-select @error('reason') is-invalid @enderror" required>
                            <option value="">-- Chọn lý do --</option>
                            <option value="Tham dự Hội nghị Y khoa Quốc tế" {{ old('reason') == 'Tham dự Hội nghị Y khoa Quốc tế' ? 'selected' : '' }}>Tham dự Hội nghị Y khoa Quốc tế</option>
                            <option value="Nghỉ phép năm" {{ old('reason') == 'Nghỉ phép năm' ? 'selected' : '' }}>Nghỉ phép năm</option>
                            <option value="Công tác chuyển giao kỹ thuật" {{ old('reason') == 'Công tác chuyển giao kỹ thuật' ? 'selected' : '' }}>Công tác chuyển giao kỹ thuật</option>
                            <option value="Nghỉ ốm / Khám sức khỏe định kỳ" {{ old('reason') == 'Nghỉ ốm / Khám sức khỏe định kỳ' ? 'selected' : '' }}>Nghỉ ốm / Khám sức khỏe định kỳ</option>
                            <option value="Tham gia kíp phẫu thuật đặc biệt" {{ old('reason') == 'Tham gia kíp phẫu thuật đặc biệt' ? 'selected' : '' }}>Tham gia kíp phẫu thuật đặc biệt</option>
                            <option value="Lý do cá nhân" {{ old('reason') == 'Lý do cá nhân' ? 'selected' : '' }}>Lý do cá nhân</option>
                        </select>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label for="start_date" class="form-label fw-semibold">Từ ngày <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', now()->toDateString()) }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <label for="end_date" class="form-label fw-semibold">Đến ngày <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', now()->addDays(2)->toDateString()) }}" required>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="substitute_doctor_id" class="form-label fw-semibold">
                            Bác sĩ trực thay (Cùng chuyên khoa: {{ $doctor->specialty->name }})
                        </label>
                        <select name="substitute_doctor_id" id="substitute_doctor_id" class="form-select @error('substitute_doctor_id') is-invalid @enderror">
                            <option value="">-- Không chỉ định bác sĩ trực thay --</option>
                            @foreach($compatibleDoctors as $compatDoc)
                                <option value="{{ $compatDoc->id }}" {{ old('substitute_doctor_id') == $compatDoc->id ? 'selected' : '' }}>
                                    {{ $compatDoc->full_name_with_title }} ({{ $compatDoc->room_number }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small">Khi bệnh nhân đặt lịch hoặc xem hồ sơ, hệ thống sẽ gợi ý bác sĩ trực thay này.</div>
                        @error('substitute_doctor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label fw-semibold">Trạng thái phê duyệt <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="approved" {{ old('status', 'approved') == 'approved' ? 'selected' : '' }}>Đã phê duyệt (Hiệu lực ngay)</option>
                            <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt (Pending)</option>
                            <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy (Cancelled)</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="notes" class="form-label fw-semibold">Ghi chú bổ sung</label>
                        <textarea name="notes" id="notes" rows="2" class="form-control @error('notes') is-invalid @enderror" placeholder="Ghi chú nội bộ cho ban quản trị hoặc điều dưỡng...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">
                        <i class="bi bi-check2-circle me-1"></i> Lưu Lịch Vắng Mặt
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Leaves History & Status -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-clock-history text-primary me-2"></i> Lịch Sử Vắng Mặt & Phân Công Trực Thay
                </h6>
                <span class="badge bg-light text-dark border">{{ $leaves->count() }} bản ghi</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Thời gian</th>
                                <th>Lý do</th>
                                <th>Bác sĩ trực thay</th>
                                <th>Trạng thái</th>
                                <th class="text-end pe-3">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaves as $leave)
                                @php
                                    $isActiveNow = $leave->isActiveOn();
                                @endphp
                                <tr class="{{ $isActiveNow ? 'table-warning' : '' }}">
                                    <td class="ps-3">
                                        <div class="fw-semibold text-dark">
                                            {{ $leave->start_date->format('d/m/Y') }} &rarr; {{ $leave->end_date->format('d/m/Y') }}
                                        </div>
                                        @if($isActiveNow)
                                            <span class="badge bg-danger rounded-pill small"><i class="bi bi-broadcast me-1"></i> Đang diễn ra hôm nay</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-medium text-dark">{{ $leave->reason }}</div>
                                        @if($leave->notes)
                                            <small class="text-muted d-block">{{ Str::limit($leave->notes, 35) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($leave->substituteDoctor)
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2 py-1">
                                                <i class="bi bi-person-fill-check me-1"></i> {{ $leave->substituteDoctor->full_name_with_title }}
                                            </span>
                                        @else
                                            <span class="text-muted small">&mdash;</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($leave->status === 'approved')
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill">Đã duyệt</span>
                                        @elseif($leave->status === 'pending')
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill">Chờ duyệt</span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill">Đã hủy</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <form action="{{ route('admin.doctors.leaves.destroy', $leave) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn hủy bỏ lịch nghỉ phép này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill" title="Xóa lịch nghỉ">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-calendar-check fs-1 text-success d-block mb-2"></i>
                                        Bác sĩ hiện không có lịch nghỉ phép hoặc vắng mặt nào.
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
