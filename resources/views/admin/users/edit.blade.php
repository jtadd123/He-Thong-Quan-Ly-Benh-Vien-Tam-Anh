@extends('layouts.admin')

@section('title', 'Chỉnh sửa Tài khoản: ' . $user->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <x-page-header title="Chỉnh sửa Tài khoản" subtitle="Cập nhật thông tin định danh và phân quyền cho người dùng" />

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.users.update', $user) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold">Địa chỉ Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-semibold">Số điện thoại</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="role" class="form-label fw-semibold">Vai trò hệ thống <span class="text-danger">*</span></label>
                            <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                <option value="patient" {{ old('role', $user->role) == 'patient' ? 'selected' : '' }}>Bệnh nhân (Patient)</option>
                                <option value="doctor" {{ old('role', $user->role) == 'doctor' ? 'selected' : '' }}>Bác sĩ (Doctor)</option>
                                <option value="receptionist" {{ old('role', $user->role) == 'receptionist' ? 'selected' : '' }}>Lễ tân tiếp nhận (Receptionist)</option>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Quản trị viên (Admin)</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="password" class="form-label fw-semibold">Mật khẩu mới (Bỏ trống nếu không đổi)</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Chỉ nhập khi muốn đặt lại mật khẩu mới">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <div class="form-check form-switch mt-2">
                                @if($user->id === Auth::id())
                                    <input type="hidden" name="is_active" value="1">
                                @else
                                    <input type="hidden" name="is_active" value="0">
                                @endif
                                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} {{ $user->id === Auth::id() ? 'disabled' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">
                                    Kích hoạt tài khoản (Cho phép đăng nhập và sử dụng hệ thống)
                                </label>
                                @if($user->id === Auth::id())
                                    <div class="form-text text-muted">Bạn không thể tự khóa tài khoản của chính mình.</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
