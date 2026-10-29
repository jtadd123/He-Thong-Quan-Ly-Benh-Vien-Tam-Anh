@extends('layouts.admin')

@section('title', 'Chi Tiết Hồ Sơ Tư Vấn Dinh Dưỡng - ' . $assessment->assessment_code)

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0 text-slate-800">Hồ Sơ Tư Vấn Dinh Dưỡng Cá Thể Hóa</h4>
                <span class="badge bg-success fs-6 font-monospace">{{ $assessment->assessment_code }}</span>
            </div>
            <p class="text-muted small mb-0 mt-1">
                Thời gian đánh giá: {{ $assessment->assessed_at->format('H:i d/m/Y') }} • 
                Bệnh nhân: <strong>{{ $assessment->patientProfile->full_name }}</strong> ({{ $assessment->patientProfile->patient_code }})
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reception.nutrition.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Danh sách
            </a>
            <a href="{{ route('reception.nutrition.print-plan', $assessment) }}" target="_blank" class="btn btn-success fw-semibold shadow-sm">
                <i class="bi bi-printer me-1"></i> In Phiếu Thực Đơn Trị Liệu (A4)
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

    <div class="row g-4 mb-4">
        <!-- Card Nhân trắc học -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-bold text-slate-800 border-bottom pb-2 mb-3">
                    <i class="bi bi-person-badge text-success me-1"></i> Chỉ Số Nhân Trắc & Thể Trạng
                </h6>

                <div class="row g-3 mb-3">
                    <div class="col-6 p-3 bg-light rounded text-center">
                        <small class="text-muted d-block">Chiều cao</small>
                        <h4 class="fw-bold text-dark mb-0">{{ $assessment->height_cm }} cm</h4>
                    </div>
                    <div class="col-6 p-3 bg-light rounded text-center">
                        <small class="text-muted d-block">Cân nặng</small>
                        <h4 class="fw-bold text-dark mb-0">{{ $assessment->weight_kg }} kg</h4>
                    </div>
                </div>

                <div class="p-3 bg-light rounded text-center mb-3">
                    <small class="text-muted d-block">Chỉ số khối cơ thể (BMI)</small>
                    <h2 class="fw-bold text-success mb-1">{{ $assessment->bmi }}</h2>
                    <span class="badge {{ $assessment->bmi >= 25 ? 'bg-danger' : ($assessment->bmi >= 23 ? 'bg-warning text-dark' : 'bg-success') }} fs-6">
                        {{ $assessment->bmi_category }}
                    </span>
                </div>

                <div class="p-3 bg-white border rounded small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Tổng năng lượng tiêu thụ (TDEE):</span>
                        <strong class="text-primary">{{ number_format($assessment->tdee_kcal) }} kcal/ngày</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Nhu cầu nước khuyến nghị:</span>
                        <strong class="text-info">{{ number_format($assessment->water_intake_ml) }} ml/ngày (~{{ round($assessment->water_intake_ml / 250, 1) }} ly)</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Phác đồ thực đơn điều trị -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-bold text-slate-800 border-bottom pb-2 mb-3">
                    <i class="bi bi-journal-check text-success me-1"></i> Phác Đồ Dinh Dưỡng Điều Trị Chỉ Định
                </h6>

                @if($assessment->dietRegimen)
                    <div class="mb-3">
                        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace mb-1">{{ $assessment->dietRegimen->code }}</span>
                        <h5 class="fw-bold text-dark mb-1">{{ $assessment->dietRegimen->title }}</h5>
                        <small class="text-muted d-block">Mục tiêu: <strong>{{ $assessment->dietRegimen->target_disease }}</strong></small>
                    </div>

                    <div class="list-group list-group-flush small mb-3">
                        <div class="list-group-item px-0">
                            <span class="badge bg-success me-2">NÊN ĂN</span>
                            <span class="text-dark">{{ $assessment->dietRegimen->foods_recommended }}</span>
                        </div>
                        <div class="list-group-item px-0">
                            <span class="badge bg-warning text-dark me-2">HẠN CHẾ</span>
                            <span class="text-dark">{{ $assessment->dietRegimen->foods_restricted }}</span>
                        </div>
                        <div class="list-group-item px-0">
                            <span class="badge bg-danger me-2">KIÊNG TUYỆT ĐỐI</span>
                            <span class="text-dark">{{ $assessment->dietRegimen->foods_avoided }}</span>
                        </div>
                    </div>
                @else
                    <p class="text-muted">Áp dụng chế độ ăn uống khoa học, duy trì năng lượng {{ number_format($assessment->tdee_kcal) }} kcal/ngày.</p>
                @endif

                @if($assessment->custom_instructions)
                    <div class="p-3 bg-light rounded-3 small">
                        <strong>Dặn dò của Bác sĩ:</strong>
                        <p class="mb-0 mt-1 text-secondary">{{ $assessment->custom_instructions }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
