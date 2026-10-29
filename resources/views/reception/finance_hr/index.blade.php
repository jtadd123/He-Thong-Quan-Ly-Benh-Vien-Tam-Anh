@extends('layouts.admin')

@section('title', 'Tài Chính & Quản Trị Y Tế Nâng Cao')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="bi bi-cash-coin text-success me-2"></i>Tài Chính & Quản Trị Y Tế Nâng Cao
            </h4>
            <p class="text-muted small mb-0">
                Thanh toán theo nhóm chẩn đoán DRG (Diagnosis Related Groups), Tính Case Mix Index (CMI) & Quản lý đề tài nghiên cứu thử nghiệm lâm sàng
            </p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill">
                <i class="bi bi-graph-up me-1"></i> RCM & DRG Module
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
                            <span class="text-muted small">Hồ Sơ DRG Chờ Duyệt</span>
                            <h4 class="fw-bold text-warning mb-0 mt-1">{{ $stats['drg_pending'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-warning-subtle p-3 rounded-circle text-warning">
                            <i class="bi bi-clock-history fs-5"></i>
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
                            <span class="text-muted small">DRG Đã Quyết Toán Hôm Nay</span>
                            <h4 class="fw-bold text-success mb-0 mt-1">{{ $stats['drg_approved_today'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-success-subtle p-3 rounded-circle text-success">
                            <i class="bi bi-check-all fs-5"></i>
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
                            <span class="text-muted small">Đề Tài Nghiên Cứu Đang Chạy</span>
                            <h4 class="fw-bold text-primary mb-0 mt-1">{{ $stats['research_active'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-primary-subtle p-3 rounded-circle text-primary">
                            <i class="bi bi-mortarboard fs-5"></i>
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
                            <span class="text-muted small">Tổng Số Đề Tài Khoa Học</span>
                            <h4 class="fw-bold text-secondary mb-0 mt-1">{{ $stats['research_total'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-secondary-subtle p-3 rounded-circle text-secondary">
                            <i class="bi bi-journal-bookmark fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
