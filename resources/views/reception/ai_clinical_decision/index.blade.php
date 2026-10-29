@extends('layouts.admin')

@section('title', 'AI & Hỗ Trợ Quyết Định Lâm Sàng Thông Minh')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="bi bi-cpu text-primary me-2"></i>AI & Hỗ Trợ Quyết Định Lâm Sàng Thông Minh (CDSS)
            </h4>
            <p class="text-muted small mb-0">
                Dự báo Sepsis sớm theo thời gian thực, Clinical Decision Rules (Wells, HEART), Mã hóa ICD-10 bằng AI & Đối chiếu thuốc y lệnh
            </p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-primary-subtle text-primary border border-primary px-3 py-2 rounded-pill">
                <i class="bi bi-robot me-1"></i> AI Model v5.2 Active
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
                            <span class="text-muted small">Cảnh Báo Sepsis Hôm Nay</span>
                            <h4 class="fw-bold text-danger mb-0 mt-1">{{ $stats['sepsis_alerts_today'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-danger-subtle p-3 rounded-circle text-danger">
                            <i class="bi bi-radioactive fs-5"></i>
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
                            <span class="text-muted small">ICD-10 AI Chờ Duyệt</span>
                            <h4 class="fw-bold text-warning mb-0 mt-1">{{ $stats['icd10_pending'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-warning-subtle p-3 rounded-circle text-warning">
                            <i class="bi bi-file-earmark-medical fs-5"></i>
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
                            <span class="text-muted small">Đối Chiếu Thuốc Hôm Nay</span>
                            <h4 class="fw-bold text-success mb-0 mt-1">{{ $stats['med_reconciliations_today'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-success-subtle p-3 rounded-circle text-success">
                            <i class="bi bi-capsule fs-5"></i>
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
                            <span class="text-muted small">Mất Cân Bằng Dịch Nặng</span>
                            <h4 class="fw-bold text-info mb-0 mt-1">{{ $stats['fluid_imbalance_patients'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-info-subtle p-3 rounded-circle text-info">
                            <i class="bi bi-droplet-half fs-5"></i>
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
                            <span class="text-muted small">Đang Quản Lý Chống Đông</span>
                            <h4 class="fw-bold text-primary mb-0 mt-1">{{ $stats['anticoagulation_active'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-primary-subtle p-3 rounded-circle text-primary">
                            <i class="bi bi-heart-pulse fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Sepsis Alerts Table -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0">
                <i class="bi bi-shield-shaded text-danger me-2"></i>Bệnh Nhân Có Cảnh Báo Sepsis Cao / Nguy Kịch (AI Real-Time Alert)
            </h6>
            <span class="badge bg-danger rounded-pill px-3">{{ count($recentSepsis) }} Ca cần xử trí ngay</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase text-secondary">
                    <tr>
                        <th>Mã BN / Bệnh Nhân</th>
                        <th>Bác Sĩ</th>
                        <th>Điểm qSOFA</th>
                        <th>Điểm SOFA</th>
                        <th>Lactate</th>
                        <th>Dự Báo AI</th>
                        <th>Mức Độ Nguy Cơ</th>
                        <th>Thời Gian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentSepsis as $log)
                    <tr>
                        <td>
                            <div class="fw-semibold text-dark">{{ $log->patient->full_name ?? 'N/A' }}</div>
                            <span class="text-muted small">{{ $log->patient->patient_code ?? 'N/A' }}</span>
                        </td>
                        <td>{{ $log->doctor->full_name ?? 'N/A' }}</td>
                        <td><span class="badge {{ $log->qsofa_score >= 2 ? 'bg-danger' : 'bg-secondary' }}">{{ $log->qsofa_score }}</span></td>
                        <td><span class="badge {{ $log->sofa_score >= 6 ? 'bg-danger' : 'bg-warning text-dark' }}">{{ $log->sofa_score }}</span></td>
                        <td>{{ $log->lactate ? $log->lactate . ' mmol/L' : '---' }}</td>
                        <td>
                            <span class="badge bg-danger-subtle text-danger border border-danger">
                                {{ strtoupper(str_replace('_', ' ', $log->ai_prediction)) }} ({{ $log->ai_confidence_score }}%)
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $log->sepsis_risk === 'critical' ? 'bg-danger' : 'bg-warning text-dark' }} rounded-pill">
                                {{ strtoupper($log->sepsis_risk) }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $log->created_at->format('H:i d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="bi bi-check-circle-fill text-success fs-3 d-block mb-1"></i>
                            Hiện không có cảnh báo sepsis nguy cơ cao nào trong hệ thống
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
