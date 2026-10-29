@extends('layouts.admin')

@section('title', 'Trung Tâm Tư Vấn Dinh Dưỡng Lâm Sàng & Thực Đơn Bệnh Lý - BVĐK Tâm Anh')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-slate-800">
                <i class="bi bi-heart-pulse text-success me-2"></i>Khoa Dinh Dưỡng Lâm Sàng & Tiết Chế
            </h4>
            <p class="text-muted small mb-0">Tính toán chỉ số nhân trắc (BMI/BMR/TDEE), xây dựng thực đơn bệnh lý cá thể hóa chuẩn Viện Dinh Dưỡng Quốc Gia.</p>
        </div>
        <div>
            <a href="{{ route('reception.nutrition.create') }}" class="btn btn-success fw-semibold shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Đánh Giá Dinh Dưỡng Mới
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

    <!-- KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-success border-4">
                <span class="text-muted small d-block">Lượt Tư Vấn Đã Thực Hiện</span>
                <h3 class="fw-bold mb-0 text-slate-800">{{ number_format($stats['total']) }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-primary border-4">
                <span class="text-muted small d-block">Thực Đơn Đái Tháo Đường</span>
                <h3 class="fw-bold mb-0 text-primary">{{ number_format($stats['diabetes_diet']) }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-danger border-4">
                <span class="text-muted small d-block">Thực Đơn Tăng Huyết Áp (DASH)</span>
                <h3 class="fw-bold mb-0 text-danger">{{ number_format($stats['dash_diet']) }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 border-start border-warning border-4">
                <span class="text-muted small d-block">Thực Đơn Bệnh Gout / Axit Uric</span>
                <h3 class="fw-bold mb-0 text-warning">{{ number_format($stats['gout_diet']) }}</h3>
            </div>
        </div>
    </div>

    <!-- Diet Regimens Catalog Section -->
    <div class="card border-0 shadow-sm p-4 mb-4">
        <h6 class="fw-bold text-slate-800 border-bottom pb-2 mb-3">
            <i class="bi bi-journal-medical text-success me-1"></i> Thư Viện Phác Đồ Thực Đơn Mẫu Viện Dinh Dưỡng
        </h6>

        <div class="row g-3">
            @foreach($regimens as $reg)
                <div class="col-md-6 col-xl-3">
                    <div class="p-3 border rounded-3 bg-light h-100">
                        <span class="badge bg-success-subtle text-success border border-success-subtle mb-2 font-monospace">{{ $reg->code }}</span>
                        <h6 class="fw-bold text-dark mb-1">{{ $reg->title }}</h6>
                        <small class="text-muted d-block mb-2">{{ $reg->target_disease }}</small>
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top small">
                            <span class="text-muted">Năng lượng mục tiêu:</span>
                            <strong class="text-success">{{ $reg->daily_calorie_target }} kcal/ngày</strong>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Assessments Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-slate-800">
                <i class="bi bi-clock-history me-1 text-success"></i> Danh Sách Hồ Sơ Tư Vấn Dinh Dưỡng
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mã Hồ Sơ</th>
                        <th>Người Bệnh</th>
                        <th>Chỉ Số Nhân Trắc</th>
                        <th>Chỉ Số Khối (BMI)</th>
                        <th>Phác Đồ Áp Dụng</th>
                        <th>Năng Lượng Khuyến Nghị</th>
                        <th class="text-end">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assessments as $item)
                        <tr>
                            <td>
                                <strong class="text-success font-monospace">{{ $item->assessment_code }}</strong>
                                <small class="text-muted d-block">{{ $item->assessed_at->format('H:i d/m/Y') }}</small>
                            </td>
                            <td>
                                <strong class="text-dark">{{ $item->patientProfile->full_name }}</strong>
                                <small class="text-muted d-block">{{ $item->patientProfile->patient_code }} • {{ $item->patientProfile->phone }}</small>
                            </td>
                            <td>
                                <span>Cao: <strong>{{ $item->height_cm }} cm</strong></span> &bull; 
                                <span>Nặng: <strong>{{ $item->weight_kg }} kg</strong></span>
                            </td>
                            <td>
                                <span class="badge {{ $item->bmi >= 25 ? 'bg-danger' : ($item->bmi >= 23 ? 'bg-warning text-dark' : 'bg-success') }} fs-6">
                                    {{ $item->bmi }}
                                </span>
                                <small class="text-muted d-block mt-1">{{ $item->bmi_category }}</small>
                            </td>
                            <td>
                                @if($item->dietRegimen)
                                    <strong class="text-dark d-block">{{ $item->dietRegimen->title }}</strong>
                                    <small class="text-muted font-monospace">{{ $item->dietRegimen->code }}</small>
                                @else
                                    <span class="text-muted">Chế độ ăn chung</span>
                                @endif
                            </td>
                            <td>
                                <strong class="text-success">{{ number_format($item->tdee_kcal) }} kcal/ngày</strong>
                                <small class="text-muted d-block">Nước: {{ number_format($item->water_intake_ml) }} ml/ngày</small>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('reception.nutrition.show', $item) }}" class="btn btn-outline-success">
                                        <i class="bi bi-eye"></i> Chi tiết
                                    </a>
                                    <a href="{{ route('reception.nutrition.print-plan', $item) }}" target="_blank" class="btn btn-outline-secondary">
                                        <i class="bi bi-printer"></i> In Thực Đơn
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-cup-hot text-secondary fs-2 d-block mb-2"></i>
                                Chưa có hồ sơ đánh giá dinh dưỡng nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($assessments->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $assessments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
