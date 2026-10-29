@extends('layouts.admin')

@section('title', 'Trải Nghiệm Bệnh Nhân & Chăm Sóc Số')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="bi bi-person-heart text-info me-2"></i>Trải Nghiệm Bệnh Nhân & Chăm Sóc Số Thế Hệ Mới
            </h4>
            <p class="text-muted small mb-0">
                Hồ sơ số Bản sao số (Digital Twin 360°), Khám từ xa Telehealth Video, Xuất viện thông minh & Theo dõi sau xuất viện 30 ngày, Quản lý bệnh mãn tính theo nhóm
            </p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-info-subtle text-info border border-info px-3 py-2 rounded-pill">
                <i class="bi bi-camera-video me-1"></i> Patient Experience 360°
            </span>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small">Bản Sao Số (Digital Twin)</span>
                            <h4 class="fw-bold text-primary mb-0 mt-1">{{ $stats['digital_twins'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-primary-subtle p-3 rounded-circle text-primary">
                            <i class="bi bi-person-badge fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small">Khám Telehealth Hôm Nay</span>
                            <h4 class="fw-bold text-success mb-0 mt-1">{{ $stats['telehealth_today'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-success-subtle p-3 rounded-circle text-success">
                            <i class="bi bi-camera-video-fill fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small">Ca Xuất Viện Hôm Nay</span>
                            <h4 class="fw-bold text-warning mb-0 mt-1">{{ $stats['discharges_today'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-warning-subtle p-3 rounded-circle text-warning">
                            <i class="bi bi-clipboard-check fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small">Chiến Dịch Tầm Soát</span>
                            <h4 class="fw-bold text-info mb-0 mt-1">{{ $stats['campaigns_active'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-info-subtle p-3 rounded-circle text-info">
                            <i class="bi bi-megaphone fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small">Quản Lý Bệnh Mãn Tính</span>
                            <h4 class="fw-bold text-danger mb-0 mt-1">{{ $stats['chronic_enrolled'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-danger-subtle p-3 rounded-circle text-danger">
                            <i class="bi bi-heart-half fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
