@extends('layouts.admin')

@section('title', 'Vận Hành Bệnh Viện & Hậu Cần Thông Minh')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="bi bi-buildings text-warning me-2"></i>Vận Hành Bệnh Viện & Hậu Cần Thông Minh
            </h4>
            <p class="text-muted small mb-0">
                Lịch phòng mổ Block Scheduling, Điều phối hộ lý vận chuyển bệnh nhân (Porter Tracking) & Suất ăn dinh dưỡng bệnh lý chuyên biệt
            </p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-warning-subtle text-warning border border-warning px-3 py-2 rounded-pill">
                <i class="bi bi-speedometer2 me-1"></i> Operations Command Active
            </span>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small">Ca Mổ Lên Lịch Hôm Nay</span>
                            <h4 class="fw-bold text-dark mb-0 mt-1">{{ $stats['surgeries_today'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-primary-subtle p-3 rounded-circle text-primary">
                            <i class="bi bi-calendar2-check fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small">Phòng Mổ Đã Hoàn Tất</span>
                            <h4 class="fw-bold text-success mb-0 mt-1">{{ $stats['or_utilization'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-success-subtle p-3 rounded-circle text-success">
                            <i class="bi bi-check-circle fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small">Vận Chuyển Đang Chờ</span>
                            <h4 class="fw-bold text-danger mb-0 mt-1">{{ $stats['transport_pending'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-danger-subtle p-3 rounded-circle text-danger">
                            <i class="bi bi-truck fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small">Suất Ăn Dinh Dưỡng Kích Hoạt</span>
                            <h4 class="fw-bold text-info mb-0 mt-1">{{ $stats['dietary_active'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-info-subtle p-3 rounded-circle text-info">
                            <i class="bi bi-egg-fried fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
