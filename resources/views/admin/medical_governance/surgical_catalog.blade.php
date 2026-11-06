@extends('layouts.admin')

@section('title', 'Danh Mục Phẫu Thuật & Đặc Quyền Lâm Sàng - Thông Tư 50/BYT')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 border-bottom pb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.medical-governance.index') }}">Quản Trị Y Khoa</a></li>
                    <li class="breadcrumb-item active">Danh Mục Phẫu Thuật Thông Tư 50/BYT</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark mb-0">Danh Mục Kỹ Thuật Phẫu Thuật & Thủ Thuật Chuyên Khoa</h3>
            <p class="text-muted small mb-0">Hệ thống phân cấp kỹ thuật, thời gian mổ chuẩn, số ca proctoring và phân quyền phẫu thuật viên</p>
        </div>
        <div>
            <a href="{{ route('admin.medical-governance.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Quay Lại Dashboard
            </a>
        </div>
    </div>

    <!-- Search Box -->
    <div class="card border-0 rounded-4 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.medical-governance.surgical-catalog') }}" class="row g-2 align-items-center">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" value="{{ $keyword }}" class="form-control border-start-0" placeholder="Nhập tên phẫu thuật, mã kỹ thuật (VD: PTTM, CTCH, thay khớp, bắc cầu vành, robot...)...">
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill w-100 fw-bold">
                        <i class="bi bi-funnel me-1"></i> Tra Cứu
                    </button>
                    @if(!empty($keyword))
                        <a href="{{ route('admin.medical-governance.surgical-catalog') }}" class="btn btn-outline-secondary rounded-pill px-3">Xóa</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 rounded-4 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0">
                <i class="bi bi-list-check text-primary me-2"></i> Danh Sách Quy Trình Kỹ Thuật (Hiển thị {{ count($procedures) }} kết quả)
            </h6>
            <span class="badge bg-light text-dark border">Tiêu chuẩn JCI International</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted">
                    <tr>
                        <th style="width: 140px;">Mã Quy Trình</th>
                        <th>Tên Phẫu Thuật / Kỹ Thuật</th>
                        <th style="width: 120px;" class="text-center">Phân Hạng</th>
                        <th style="width: 130px;" class="text-center">Học Vị Yêu Cầu</th>
                        <th style="width: 120px;" class="text-center">Số Ca Giám Sát</th>
                        <th style="width: 120px;" class="text-center">Thời Gian Mổ</th>
                        <th style="width: 140px;">Phương Pháp</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($procedures as $proc)
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace">{{ $proc['code'] }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $proc['name'] }}</div>
                                <div class="small text-muted d-flex gap-2 mt-1">
                                    <span>CPT: <code>{{ $proc['cpt_code'] }}</code></span>
                                    <span>ICD-9-CM: <code>{{ $proc['icd9cm_code'] }}</code></span>
                                    <span class="badge bg-{{ $proc['risk_level'] === 'High' ? 'danger' : ($proc['risk_level'] === 'Medium' ? 'warning text-dark' : 'success') }}-subtle text-{{ $proc['risk_level'] === 'High' ? 'danger' : ($proc['risk_level'] === 'Medium' ? 'warning' : 'success') }} border px-2">
                                        Rủi ro: {{ $proc['risk_level'] }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                @php
                                    $cBadge = match($proc['classification']) {
                                        'Đặc biệt' => 'danger',
                                        'Loại I' => 'warning text-dark',
                                        'Loại II' => 'info text-dark',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $cBadge }} rounded-pill px-3">{{ $proc['classification'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $proc['min_qualification'] }}</span>
                            </td>
                            <td class="text-center fw-bold text-dark">
                                {{ $proc['proctored_cases_required'] }} ca
                            </td>
                            <td class="text-center text-muted">
                                {{ $proc['estimated_duration_min'] }} phút
                            </td>
                            <td>
                                <small class="text-dark">{{ $proc['surgical_approach'] }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Không tìm thấy quy trình phẫu thuật nào phù hợp với từ khóa!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
