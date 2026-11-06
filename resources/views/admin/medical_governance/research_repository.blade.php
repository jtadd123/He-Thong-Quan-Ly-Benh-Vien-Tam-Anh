@extends('layouts.admin')

@section('title', 'Kho Nghiên Cứu Y Học & Thử Nghiệm Lâm Sàng Bác Sĩ')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 border-bottom pb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.medical-governance.index') }}">Quản Trị Y Khoa</a></li>
                    <li class="breadcrumb-item active">Nghiên Cứu & Thử Nghiệm Lâm Sàng</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark mb-0">Kho Công Trình Nghiên Cứu Y Học & Thử Nghiệm Lâm Sàng Bác Sĩ</h3>
            <p class="text-muted small mb-0">{{ number_format($totalCount) }} công trình nghiên cứu được xuất bản trên các tạp chí y khoa quốc tế uy tín (NEJM, The Lancet, JACC, JCO)</p>
        </div>
        <div>
            <a href="{{ route('admin.medical-governance.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Quay Lại
            </a>
        </div>
    </div>

    <!-- Publications List -->
    <div class="card border-0 rounded-4 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0">
                <i class="bi bi-journal-richtext text-primary me-2"></i> Danh Sách Công Trình Điểm Trích Dẫn Cao (Top Impact Factor)
            </h6>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">GCP Certified</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted">
                    <tr>
                        <th style="width: 140px;">Mã Đề Tài</th>
                        <th>Tên Công Trình / Bài Báo</th>
                        <th style="width: 220px;">Tạp Chí Quốc Tế</th>
                        <th style="width: 110px;" class="text-center">Impact Factor</th>
                        <th style="width: 110px;" class="text-center">Cỡ Mẫu</th>
                        <th style="width: 160px;">Chủ Nhiệm Đề Tài</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($studies as $s)
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace">{{ $s['study_id'] }}</span>
                                <div class="small text-muted mt-1">PMID: {{ $s['pmid'] }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $s['title'] }}</div>
                                <div class="small text-muted mt-1">
                                    <span>DOI: <code>{{ $s['doi'] }}</code></span> &bull; 
                                    <span class="text-primary">{{ $s['study_design'] }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark small">{{ $s['journal'] }}</div>
                                <small class="text-muted">ISSN: {{ $s['issn'] }} ({{ $s['publication_year'] }})</small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold fs-6">
                                    {{ $s['impact_factor'] }}
                                </span>
                            </td>
                            <td class="text-center fw-bold text-dark">
                                {{ number_format($s['sample_size']) }} BN
                            </td>
                            <td>
                                <small class="text-dark fw-semibold">{{ $s['principal_investigator'] }}</small>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
