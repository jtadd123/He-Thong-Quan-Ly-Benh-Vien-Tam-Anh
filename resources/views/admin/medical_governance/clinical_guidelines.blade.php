@extends('layouts.admin')

@section('title', 'Kho Phác Đồ Điều Trị Y Khoa Lâm Sàng - CPG')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 border-bottom pb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.medical-governance.index') }}">Quản Trị Y Khoa</a></li>
                    <li class="breadcrumb-item active">Phác Đồ Điều Trị CPG</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark mb-0">Kho Phác Đồ Chẩn Đoán & Điều Trị Lâm Sàng Chuẩn Vàng (CPG)</h3>
            <p class="text-muted small mb-0">730 phác đồ điều trị được chuẩn hóa chứng cứ loại A bởi Hội đồng Chuyên môn Bệnh viện Đa khoa Tâm Anh</p>
        </div>
        <div>
            <a href="{{ route('admin.medical-governance.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Quay Lại
            </a>
        </div>
    </div>

    <!-- Specialty Tabs -->
    <div class="row g-4">
        @foreach($allGuidelines as $specSlug => $guidelines)
            <div class="col-12">
                <div class="card border-0 rounded-4 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-dark mb-0 text-uppercase">
                            <i class="bi bi-heart-pulse-fill text-danger me-2"></i> Chuyên Khoa: {{ $specSlug }}
                        </h6>
                        <span class="badge bg-primary rounded-pill">{{ count($guidelines) }} Phác đồ chuẩn</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            @foreach(array_slice($guidelines, 0, 4) as $gid => $g)
                                <div class="col-lg-6">
                                    <div class="card border rounded-3 h-100 shadow-sm">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <span class="badge bg-secondary font-monospace">{{ $gid }}</span>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">GRADE A</span>
                                            </div>
                                            <h6 class="fw-bold text-dark mb-2">{{ $g['title'] }}</h6>
                                            <div class="small text-muted mb-2">
                                                <strong>Đối tượng:</strong> {{ $g['target_patient'] }}
                                            </div>
                                            <div class="p-2 bg-light rounded small mb-2">
                                                <strong>Phác đồ bước 1:</strong>
                                                <ul class="mb-0 ps-3 mt-1">
                                                    <li>{{ $g['first_line_protocol']['step_1'] }}</li>
                                                    <li>{{ $g['first_line_protocol']['step_2'] }}</li>
                                                </ul>
                                            </div>
                                            <div class="small text-danger">
                                                <strong><i class="bi bi-flag-fill me-1"></i> Dấu hiệu cờ đỏ:</strong>
                                                {{ $g['red_flags'][0] }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
