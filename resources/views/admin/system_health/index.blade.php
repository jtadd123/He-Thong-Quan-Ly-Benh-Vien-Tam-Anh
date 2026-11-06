@extends('layouts.admin')

@section('title', 'Giám Sát Sức Khỏe Toàn Diện Hệ Thống - Bệnh Viện Tâm Anh')

@section('content')
<x-page-header title="Giám Sát Sức Khỏe Hạ Tầng Hệ Thống" subtitle="Kiểm tra thời gian thực trạng thái Cơ sở dữ liệu, Bộ nhớ, Ổ cứng, Bộ đệm Cache và Máy chủ Email" />

<!-- Overall System Health Status Banner -->
<div class="card shadow-sm border-0 mb-4 {{ $report['status'] === 'healthy' ? 'bg-success text-white' : 'bg-warning text-dark' }}">
    <div class="card-body p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-white {{ $report['status'] === 'healthy' ? 'text-success' : 'text-warning' }} d-inline-flex align-items-center justify-content-center p-3">
                <i class="bi {{ $report['status'] === 'healthy' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }} fs-2"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-1">
                    {{ $report['status'] === 'healthy' ? 'HẠ TẦNG HỆ THỐNG HOẠT ĐỘNG HOÀN TOÀN KHỎE MẠNH (HEALTHY)' : 'HỆ THỐNG ĐANG SUY GIẢM HIỆU NĂNG (DEGRADED)' }}
                </h4>
                <div class="small opacity-75">
                    Thời điểm kiểm tra: {{ \Carbon\Carbon::parse($report['checked_at'])->format('d/m/Y H:i:s') }}
                </div>
            </div>
        </div>
        <div>
            <a href="{{ route('admin.system-health.index') }}" class="btn {{ $report['status'] === 'healthy' ? 'btn-light text-success' : 'btn-dark' }} fw-bold">
                <i class="bi bi-arrow-clockwise me-1"></i>Kiểm tra lại ngay
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Database Health Card -->
    <div class="col-md-6">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-database me-2 text-primary"></i>Cơ Sở Dữ Liệu (MySQL/MariaDB)</h5>
                <span class="badge {{ $report['database']['status'] === 'healthy' ? 'bg-success' : 'bg-danger' }}">
                    {{ strtoupper($report['database']['status']) }}
                </span>
            </div>
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-2 p-3 bg-light rounded">
                    <span class="text-muted fw-semibold">Độ trễ truy vấn (Query Latency):</span>
                    <span class="fs-4 fw-bold {{ $report['database']['latency_ms'] < 100 ? 'text-success' : 'text-warning' }}">
                        {{ $report['database']['latency_ms'] }} ms
                    </span>
                </div>
                @php
                    $latencyPct = min(100, max(5, round(($report['database']['latency_ms'] / 100) * 100)));
                @endphp
                <div class="progress mb-3" style="height: 6px;">
                    <div class="progress-bar {{ $report['database']['latency_ms'] < 50 ? 'bg-success' : 'bg-warning' }}" style="width: {{ $latencyPct }}%"></div>
                </div>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2"><strong>Driver:</strong> <span class="font-monospace">{{ $report['database']['driver'] }}</span></li>
                    <li class="mb-2"><strong>Tên Database:</strong> <span class="font-monospace">{{ $report['database']['database'] }}</span></li>
                    <li><strong>Thông điệp:</strong> {{ $report['database']['message'] }}</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Storage & Disk Health Card -->
    <div class="col-md-6">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-hdd-network me-2 text-info"></i>Lưu Trữ & Ổ Cứng (Disk Storage)</h5>
                <span class="badge {{ $report['storage']['status'] === 'healthy' ? 'bg-success' : 'bg-danger' }}">
                    {{ strtoupper($report['storage']['status']) }}
                </span>
            </div>
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-2 p-3 bg-light rounded">
                    <span class="text-muted fw-semibold">Dung lượng ổ cứng trống:</span>
                    <span class="fs-4 fw-bold text-info">
                        {{ $report['storage']['free_disk_gb'] ? $report['storage']['free_disk_gb'] . ' GB' : 'N/A' }}
                    </span>
                </div>
                @if(!empty($report['storage']['disk_usage_percent']))
                    <div class="progress mb-3" style="height: 6px;">
                        <div class="progress-bar {{ $report['storage']['disk_usage_percent'] < 85 ? 'bg-info' : 'bg-danger' }}" style="width: {{ min(100, $report['storage']['disk_usage_percent']) }}%"></div>
                    </div>
                @endif
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2"><strong>Tổng dung lượng ổ:</strong> {{ $report['storage']['total_disk_gb'] ? $report['storage']['total_disk_gb'] . ' GB' : 'N/A' }}</li>
                    <li class="mb-2"><strong>Quyền ghi thư mục Storage:</strong> 
                        @if($report['storage']['storage_path_writable'])
                            <span class="text-success fw-bold"><i class="bi bi-check-circle me-1"></i>Có quyền ghi (Writable)</span>
                        @else
                            <span class="text-danger fw-bold"><i class="bi bi-x-circle me-1"></i>Không có quyền ghi (Read-only)</span>
                        @endif
                    </li>
                    <li><strong>Tỷ lệ sử dụng:</strong> {{ $report['storage']['disk_usage_percent'] ? $report['storage']['disk_usage_percent'] . '%' : 'N/A' }}</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Cache Store Card -->
    <div class="col-md-6">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-lightning-charge me-2 text-warning"></i>Bộ Đệm Hệ Thống (Cache Store)</h5>
                <span class="badge {{ $report['cache']['status'] === 'healthy' ? 'bg-success' : 'bg-danger' }}">
                    {{ strtoupper($report['cache']['status']) }}
                </span>
            </div>
            <div class="card-body p-4">
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2"><strong>Driver đệm:</strong> <span class="badge bg-secondary font-monospace">{{ $report['cache']['driver'] }}</span></li>
                    <li class="mb-2"><strong>Kiểm tra Đọc / Ghi / Xóa:</strong> 
                        @if($report['cache']['read_write_ok'])
                            <span class="text-success fw-bold"><i class="bi bi-check-circle me-1"></i>Hoạt động bình thường (Passed)</span>
                        @else
                            <span class="text-danger fw-bold"><i class="bi bi-x-circle me-1"></i>Thất bại</span>
                        @endif
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Mail Transport Card -->
    <div class="col-md-6">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-envelope me-2 text-danger"></i>Máy Chủ Email (Mail Transport)</h5>
                <span class="badge bg-success">CONFIGURED</span>
            </div>
            <div class="card-body p-4">
                <div class="small mb-3">
                    <div><strong>Mailer:</strong> <span class="font-monospace">{{ $report['mail']['default_mailer'] }}</span></div>
                    <div><strong>From:</strong> {{ $report['mail']['from_address'] }}</div>
                </div>

                <form action="{{ route('admin.system-health.ping-mail') }}" method="POST" class="d-flex gap-2">
                    @csrf
                    <input type="email" name="email" class="form-control form-control-sm" placeholder="Email nhận thử nghiệm..." value="{{ auth()->user()->email }}" required>
                    <button type="submit" class="btn btn-sm btn-outline-danger text-nowrap fw-bold">
                        <i class="bi bi-send me-1"></i>Gửi mail test
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Runtime Environment & Memory Card -->
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-cpu me-2 text-secondary"></i>Môi Trường Thực Thi & Tài Nguyên Bộ Nhớ</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded text-center">
                            <span class="text-muted small">Phiên bản PHP</span>
                            <div class="fs-5 fw-bold text-dark mt-1">{{ $report['runtime']['php_version'] }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded text-center">
                            <span class="text-muted small">Phiên bản Laravel</span>
                            <div class="fs-5 fw-bold text-primary mt-1">{{ $report['runtime']['laravel_version'] }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded text-center">
                            <span class="text-muted small">Bộ nhớ đang dùng / Đỉnh</span>
                            <div class="fs-5 fw-bold text-success mt-1">{{ $report['runtime']['memory_usage_mb'] }} MB / {{ $report['runtime']['memory_peak_mb'] }} MB</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded text-center">
                            <span class="text-muted small">Hệ điều hành máy chủ</span>
                            <div class="fs-5 fw-bold text-secondary mt-1">{{ $report['runtime']['os'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
