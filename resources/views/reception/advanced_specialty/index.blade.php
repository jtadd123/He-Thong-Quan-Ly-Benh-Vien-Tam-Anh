@extends('layouts.admin')

@section('title', 'Quản Lý Lâm Sàng Chuyên Khoa Sâu')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="bi bi-diagram-3-fill text-danger me-2"></i>Quản Lý Lâm Sàng Chuyên Khoa Sâu
            </h4>
            <p class="text-muted small mb-0">
                Stroke Pathway & tPA Thrombolysis, STEMI Cathlab Kích hoạt khẩn, Gây mê hồi sức AIMS, Máy thở & Cai máy ARDS, Loét tì đè PUSH, Đánh giá đau đa chiều & NICU
            </p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-danger-subtle text-danger border border-danger px-3 py-2 rounded-pill">
                <i class="bi bi-heart-pulse-fill me-1"></i> Critical Pathways Online
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
                            <span class="text-muted small">Ca Đột Quỵ Hôm Nay</span>
                            <h4 class="fw-bold text-danger mb-0 mt-1">{{ $stats['stroke_cases_today'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-danger-subtle p-3 rounded-circle text-danger">
                            <i class="bi bi-activity fs-5"></i>
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
                            <span class="text-muted small">STEMI Cathlab Hôm Nay</span>
                            <h4 class="fw-bold text-primary mb-0 mt-1">{{ $stats['stemi_activations_today'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-primary-subtle p-3 rounded-circle text-primary">
                            <i class="bi bi-heart-fill fs-5"></i>
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
                            <span class="text-muted small">Bệnh Nhân Thở Máy</span>
                            <h4 class="fw-bold text-info mb-0 mt-1">{{ $stats['ventilated_patients'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-info-subtle p-3 rounded-circle text-info">
                            <i class="bi bi-lungs fs-5"></i>
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
                            <span class="text-muted small">Đau Chưa Kiểm Soát</span>
                            <h4 class="fw-bold text-warning mb-0 mt-1">{{ $stats['pain_uncontrolled'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-warning-subtle p-3 rounded-circle text-warning">
                            <i class="bi bi-emoji-frown fs-5"></i>
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
                            <span class="text-muted small">Vết Thương Tiến Triển Xấu</span>
                            <h4 class="fw-bold text-secondary mb-0 mt-1">{{ $stats['wounds_deteriorating'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-secondary-subtle p-3 rounded-circle text-secondary">
                            <i class="bi bi-bandaid fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Specialty Hub Info -->
    <div class="card border-0 shadow-sm rounded-3 p-4 bg-white">
        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-check2-circle text-success me-2"></i>Quy Trình Chuyên Khoa Sâu Chuẩn JCI Quốc Tế</h6>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-light">
                    <h6 class="fw-bold text-danger mb-1"><i class="bi bi-lightning-charge me-1"></i> Stroke Giờ Vàng</h6>
                    <p class="text-muted small mb-0">Mục tiêu Door-to-Needle &le; 60 phút, Door-to-CT &le; 25 phút. Đánh giá NIHSS, ASPECTS score và liều Alteplase tự động theo cân nặng.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-light">
                    <h6 class="fw-bold text-primary mb-1"><i class="bi bi-heart-pulse me-1"></i> STEMI Code STEMI</h6>
                    <p class="text-muted small mb-0">Mục tiêu Door-to-Balloon &le; 90 phút. Phân loại Killip I-IV, tính điểm TIMI Risk Score và hỗ trợ lựa chọn can thiệp mạch vành khẩn cấp.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-light">
                    <h6 class="fw-bold text-info mb-1"><i class="bi bi-lungs me-1"></i> ARDS & Máy Thở</h6>
                    <p class="text-muted small mb-0">Phân loại Berlin ARDS theo tỷ lệ PaO2/FiO2, kiểm soát Driving Pressure &lt; 15 cmH2O và quy trình Spontaneous Breathing Trial (SBT).</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
