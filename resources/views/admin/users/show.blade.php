@extends('layouts.admin')

@section('title', 'Chi tiết Tài khoản: ' . $user->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <x-page-header title="Chi tiết Tài khoản" subtitle="Xem thông tin chi tiết và hồ sơ liên kết của người dùng">
            <x-slot:actions>
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-1">
                    <i class="bi bi-pencil-square"></i> Sửa tài khoản
                </a>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i> Quay lại
                </a>
            </x-slot:actions>
        </x-page-header>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fs-2 fw-bold" style="width: 64px; height: 64px;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1">{{ $user->name }}</h4>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge {{ $user->role_badge_class }} rounded-pill">{{ $user->role_name }}</span>
                            <span class="text-muted small">&bull; ID: #{{ $user->id }}</span>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Địa chỉ Email</label>
                        <span class="fw-medium">{{ $user->email }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Số điện thoại</label>
                        <span class="fw-medium">{{ $user->phone ?? 'Chưa cập nhật' }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Ngày tạo tài khoản</label>
                        <span>{{ $user->created_at ? $user->created_at->format('d/m/Y H:i:s') : '—' }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Cập nhật lần cuối</label>
                        <span>{{ $user->updated_at ? $user->updated_at->format('d/m/Y H:i:s') : '—' }}</span>
                    </div>
                </div>

                @if($user->doctor)
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-heart-pulse me-1"></i> Hồ sơ Bác sĩ liên kết</h6>
                        <div class="row g-2 small">
                            <div class="col-sm-6"><strong>Học vị:</strong> {{ $user->doctor->qualification }}</div>
                            <div class="col-sm-6"><strong>Chuyên khoa:</strong> {{ $user->doctor->specialty->name ?? '—' }}</div>
                            <div class="col-sm-6"><strong>Phòng khám:</strong> {{ $user->doctor->room_number }}</div>
                            <div class="col-sm-6"><strong>Giá khám:</strong> {{ number_format($user->doctor->consultation_fee, 0, ',', '.') }} VNĐ</div>
                        </div>
                    </div>
                @endif

                @if($user->patientProfile)
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="fw-bold text-success mb-3"><i class="bi bi-person-vcard me-1"></i> Hồ sơ Bệnh nhân liên kết</h6>
                        <div class="row g-2 small">
                            <div class="col-sm-6"><strong>Mã bệnh nhân:</strong> <code>{{ $user->patientProfile->patient_code }}</code></div>
                            <div class="col-sm-6"><strong>Ngày sinh:</strong> {{ $user->patientProfile->dob?->format('d/m/Y') ?? '—' }}</div>
                            <div class="col-sm-6"><strong>Giới tính:</strong> {{ $user->patientProfile->gender_name }}</div>
                            <div class="col-sm-6"><strong>CCCD:</strong> {{ $user->patientProfile->identity_card ?? '—' }}</div>
                            <div class="col-sm-12"><strong>Địa chỉ:</strong> {{ $user->patientProfile->address ?? '—' }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
