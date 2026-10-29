@extends('layouts.admin')

@section('title', 'Trung Tâm An Toàn Truyền Máu Lâm Sàng - BVĐK Tâm Anh')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-slate-800">
                <i class="bi bi-droplet-half text-danger me-2"></i>Kiểm Soát An Toàn Truyền Máu Lâm Sàng
            </h4>
            <p class="text-muted small mb-0">Quản lý kho máu, đối chiếu hòa hợp miễn dịch hệ ABO / Rh, xác thực kép 2 điều dưỡng và theo dõi sinh hiệu tại giường.</p>
        </div>
        <div>
            <a href="{{ route('reception.blood-transfusions.create') }}" class="btn btn-danger fw-semibold shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Chuẩn Bị Ca Truyền Máu Mới
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-x-octagon-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Blood Bank Stock Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm p-3 text-center border-top border-danger border-4">
                <span class="text-muted small d-block">Túi Nhóm O</span>
                <h3 class="fw-bold mb-0 text-danger">{{ $bloodStock['o_units'] }}</h3>
                <small class="text-muted" style="font-size: 0.75rem;">Cho toàn năng</small>
            </div>
        </div>
        <div class="col-sm-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm p-3 text-center border-top border-primary border-4">
                <span class="text-muted small d-block">Túi Nhóm A</span>
                <h3 class="fw-bold mb-0 text-primary">{{ $bloodStock['a_units'] }}</h3>
                <small class="text-muted" style="font-size: 0.75rem;">Sẵn sàng</small>
            </div>
        </div>
        <div class="col-sm-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm p-3 text-center border-top border-success border-4">
                <span class="text-muted small d-block">Túi Nhóm B</span>
                <h3 class="fw-bold mb-0 text-success">{{ $bloodStock['b_units'] }}</h3>
                <small class="text-muted" style="font-size: 0.75rem;">Sẵn sàng</small>
            </div>
        </div>
        <div class="col-sm-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm p-3 text-center border-top border-warning border-4">
                <span class="text-muted small d-block">Túi Nhóm AB</span>
                <h3 class="fw-bold mb-0 text-warning">{{ $bloodStock['ab_units'] }}</h3>
                <small class="text-muted" style="font-size: 0.75rem;">Nhận toàn năng</small>
            </div>
        </div>
        <div class="col-sm-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm p-3 text-center border-top border-info border-4">
                <span class="text-muted small d-block">Máu Hiếm Rh(-)</span>
                <h3 class="fw-bold mb-0 text-info">{{ $bloodStock['rh_neg'] }}</h3>
                <small class="text-muted" style="font-size: 0.75rem;">Dự trữ đặc biệt</small>
            </div>
        </div>
        <div class="col-sm-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm p-3 text-center border-top border-dark border-4">
                <span class="text-muted small d-block">Tổng Trong Kho</span>
                <h3 class="fw-bold mb-0 text-dark">{{ $bloodStock['total_units'] }}</h3>
                <small class="text-muted" style="font-size: 0.75rem;">Đạt chuẩn GSP</small>
            </div>
        </div>
    </div>

    <!-- Active Transfusions Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="fw-bold mb-0 text-slate-800">
                <i class="bi bi-clock-history me-1 text-danger"></i> Danh Sách Ca Truyền Máu Lâm Sàng
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mã Ca Truyền</th>
                        <th>Người Bệnh</th>
                        <th>Nhóm Máu BN</th>
                        <th>Túi Máu Chỉ Định</th>
                        <th>Hòa Hợp Chéo</th>
                        <th>Điều Dưỡng Đối Chiếu (Dual)</th>
                        <th>Trạng Thái</th>
                        <th class="text-end">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfusions as $trf)
                        <tr>
                            <td>
                                <strong class="text-danger font-monospace">{{ $trf->transfusion_code }}</strong>
                                <small class="text-muted d-block">{{ $trf->created_at->format('H:i d/m/Y') }}</small>
                            </td>
                            <td>
                                <strong class="text-dark">{{ $trf->patientProfile->full_name }}</strong>
                                <small class="text-muted d-block">{{ $trf->patientProfile->patient_code }} • {{ $trf->indication }}</small>
                            </td>
                            <td>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-6">
                                    {{ $trf->patient_abo }} Rh({{ $trf->patient_rh === 'positive' ? '+' : '-' }})
                                </span>
                            </td>
                            <td>
                                <strong class="font-monospace text-dark d-block">{{ $trf->bloodUnit->unit_code }}</strong>
                                <small class="text-muted">{{ $trf->bloodUnit->product_name }} ({{ $trf->bloodUnit->volume_ml }}ml)</small>
                            </td>
                            <td>
                                @if($trf->is_crossmatch_compatible)
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>HÒA HỢP</span>
                                @else
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>KHÔNG HÒA HỢP</span>
                                @endif
                            </td>
                            <td>
                                <div class="small">
                                    <span>1. {{ $trf->nurse1 ? $trf->nurse1->name : '--' }}</span><br>
                                    <span>2. {{ $trf->nurse2 ? $trf->nurse2->name : '--' }}</span>
                                </div>
                            </td>
                            <td>
                                @if($trf->status === 'completed')
                                    <span class="badge bg-success"><i class="bi bi-check2-all me-1"></i>Hoàn tất</span>
                                @elseif($trf->status === 'transfusing')
                                    <span class="badge bg-danger animate-pulse"><i class="bi bi-droplet-fill me-1"></i>Đang truyền máu</span>
                                @elseif($trf->status === 'aborted')
                                    <span class="badge bg-dark"><i class="bi bi-slash-circle me-1"></i>Đình chỉ (Tai biến)</span>
                                @else
                                    <span class="badge bg-warning text-dark"><i class="bi bi-shield-check me-1"></i>Đã chuẩn bị</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('reception.blood-transfusions.show', $trf) }}" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-eye"></i> Giám Sát
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-droplet text-secondary fs-2 d-block mb-2"></i>
                                Hiện tại không có ca truyền máu nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transfusions->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $transfusions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
