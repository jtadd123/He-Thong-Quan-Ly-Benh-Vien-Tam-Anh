@extends('layouts.admin')

@section('title', 'Báo Cáo Sự Cố Y Khoa & Nguy Cơ An Toàn Người Bệnh')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-primary">
                <i class="bi bi-shield-exclamation me-2"></i>Báo Cáo Sự Cố Y Khoa Suýt Xảy Ra (Near-Miss)
            </h4>
            <p class="text-muted small mb-0">Hệ thống báo cáo tự nguyện không xử phạt theo Thông tư 43/2018/TT-BYT & Chuẩn an toàn JCI - Bệnh Viện Tâm Anh</p>
        </div>
        <div>
            <a href="{{ route('reception.medical-incidents.create') }}" class="btn btn-danger d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill"></i> Báo Cáo Nguy Cơ / Sự Cố Mới
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-primary border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Tổng Số Báo Cáo</div>
                            <div class="fs-4 fw-bold text-dark">{{ $stats['total'] }}</div>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                            <i class="bi bi-clipboard2-data fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-info border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Sự Cố Suýt Xảy Ra (NC0)</div>
                            <div class="fs-4 fw-bold text-info">{{ $stats['nc0_near_miss'] }}</div>
                        </div>
                        <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                            <i class="bi bi-shield-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-warning border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Đang Phân Tích RCA</div>
                            <div class="fs-4 fw-bold text-warning">{{ $stats['investigating'] }}</div>
                        </div>
                        <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                            <i class="bi bi-hourglass-split fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-success border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold">Đã Có Giải Pháp Khắc Phục</div>
                            <div class="fs-4 fw-bold text-success">{{ $stats['resolved'] }}</div>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                            <i class="bi bi-check2-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3">Mã Sự Cố</th>
                            <th>Mức Độ (BYT)</th>
                            <th>Nhóm Sự Cố</th>
                            <th>Địa Điểm Xảy Ra</th>
                            <th>Thời Gian</th>
                            <th>Người Báo Cáo</th>
                            <th>Trạng Thái Xử Lý</th>
                            <th class="text-end pe-3">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incidents as $inc)
                            <tr>
                                <td class="ps-3 fw-bold text-primary font-monospace">{{ $inc->incident_code }}</td>
                                <td>{!! $inc->severity_badge !!}</td>
                                <td class="fw-semibold text-dark">{{ $inc->incident_category }}</td>
                                <td><i class="bi bi-geo-alt text-danger me-1"></i>{{ $inc->location }}</td>
                                <td class="small">{{ $inc->occurred_at->format('H:i d/m/Y') }}</td>
                                <td>
                                    @if($inc->reporter_type === 'anonymous')
                                        <span class="badge bg-secondary"><i class="bi bi-incognito me-1"></i>Ẩn Danh</span>
                                    @else
                                        <span class="fw-semibold text-dark">{{ $inc->reporter?->name }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($inc->status === 'resolved' || $inc->status === 'closed')
                                        <span class="badge bg-success"><i class="bi bi-check2-circle me-1"></i>Đã Khắc Phục</span>
                                    @elseif($inc->status === 'investigating')
                                        <span class="badge bg-warning text-dark"><i class="bi bi-search me-1"></i>Đang Phân Tích</span>
                                    @else
                                        <span class="badge bg-danger animate-pulse">Mới Báo Cáo</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('reception.medical-incidents.show', $inc) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i> Xem & Phân Tích RCA
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-shield-check fs-1 text-success d-block mb-2"></i>
                                    Chưa có sự cố y khoa nào được ghi nhận. Toàn viện vận hành an toàn!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($incidents->hasPages())
                <div class="p-3 border-top">{{ $incidents->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
