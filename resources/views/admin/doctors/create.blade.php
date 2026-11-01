@extends('layouts.admin')

@section('title', 'Thêm Bác sĩ Mới')

@section('content')
<x-page-header title="Thêm Bác sĩ Mới" subtitle="Đăng ký hồ sơ và tạo tài khoản bác sĩ trong hệ thống">
    <x-slot:actions>
        <a href="{{ route('admin.doctors.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </x-slot:actions>
</x-page-header>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.doctors.store') }}" enctype="multipart/form-data">
                    @csrf

                    <!-- Tài khoản bác sĩ -->
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-person-lock text-primary me-2"></i> 1. Thông tin Tài khoản Đăng nhập
                    </h5>

                    @if($availableUsers->isNotEmpty())
                        <div class="mb-3 p-3 bg-light rounded border">
                            <label class="form-label fw-semibold">Chọn tài khoản người dùng có sẵn (Vai trò Bác sĩ):</label>
                            <select name="user_id" id="user_id_select" class="form-select @error('user_id') is-invalid @enderror">
                                <option value="">-- Tạo tài khoản mới dưới đây (Khuyên dùng) --</option>
                                @foreach($availableUsers as $u)
                                    <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                                        {{ $u->name }} ({{ $u->email }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Nếu chọn tài khoản có sẵn, thông tin họ tên, email, mật khẩu bên dưới sẽ được bỏ qua.</small>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    <div id="new_user_fields">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-semibold">Họ và tên bác sĩ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Ví dụ: Nguyễn Văn An">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold">Email đăng nhập <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="doctor@tamanh.vn">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-semibold">Mật khẩu khởi tạo <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Tối thiểu 6 ký tự">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-semibold">Số điện thoại liên hệ</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="0901234567">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Hồ sơ chuyên môn -->
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3 mt-4">
                        <i class="bi bi-briefcase text-primary me-2"></i> 2. Hồ sơ Chuyên môn & Phòng khám
                    </h5>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="specialty_id" class="form-label fw-semibold">Chuyên khoa trực thuộc <span class="text-danger">*</span></label>
                            <select name="specialty_id" id="specialty_id" class="form-select @error('specialty_id') is-invalid @enderror" required>
                                <option value="">-- Chọn chuyên khoa --</option>
                                @foreach($specialties as $sp)
                                    <option value="{{ $sp->id }}" {{ (old('specialty_id', request('specialty_id')) == $sp->id) ? 'selected' : '' }}>
                                        {{ $sp->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('specialty_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="qualification" class="form-label fw-semibold">Học vị / Học hàm <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('qualification') is-invalid @enderror" id="qualification" name="qualification" value="{{ old('qualification', 'BS.CKI') }}" placeholder="Ví dụ: ThS.BS, TS.BS, PGS.TS.BS, BS.CKII..." required>
                            @error('qualification')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="room_number" class="form-label fw-semibold">Phòng khám được phân công <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('room_number') is-invalid @enderror" id="room_number" name="room_number" value="{{ old('room_number') }}" placeholder="Ví dụ: Phòng 201 - Tòa A" required>
                            @error('room_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="consultation_fee" class="form-label fw-semibold">Giá khám bệnh (VNĐ) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="10000" min="0" class="form-control @error('consultation_fee') is-invalid @enderror" id="consultation_fee" name="consultation_fee" value="{{ old('consultation_fee', 300000) }}" required>
                                <span class="input-group-text">đ</span>
                            </div>
                            @error('consultation_fee')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="avatar" class="form-label fw-semibold">Ảnh chân dung bác sĩ (Avatar)</label>
                            <input type="file" class="form-control @error('avatar') is-invalid @enderror" id="avatar" name="avatar" accept="image/jpeg,image/png,image/jpg,image/webp">
                            <small class="text-muted">Định dạng JPG, PNG, WEBP (Tối đa 2MB)</small>
                            @error('avatar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="bio" class="form-label fw-semibold">Tiểu sử & Quá trình công tác</label>
                            <textarea class="form-control @error('bio') is-invalid @enderror" id="bio" name="bio" rows="4" placeholder="Kinh nghiệm khám chữa bệnh, chứng chỉ chuyên sâu, công trình nghiên cứu...">{{ old('bio') }}</textarea>
                            @error('bio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_available" name="is_available" value="1" {{ old('is_available', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_available">Sẵn sàng nhận lịch khám bệnh ngay</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('admin.doctors.index') }}" class="btn btn-light">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                            <i class="bi bi-check-lg"></i> Thêm bác sĩ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
