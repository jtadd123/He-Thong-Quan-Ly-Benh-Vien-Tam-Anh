@extends('layouts.admin')

@section('title', 'Chi Tiết Mũi Tiêm - ' . $record->record_code)

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('reception.vaccinations.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Trở lại danh sách
                </a>
                <span class="badge bg-secondary font-monospace">{{ $record->record_code }}</span>
                {!! $record->status_badge !!}
            </div>
            <h4 class="fw-bold mb-0 text-slate-800">
                Ca Tiêm: {{ $record->patientProfile->full_name }} - {{ $record->vaccine->name }}
            </h4>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reception.vaccinations.certificate', $record) }}" target="_blank" class="btn btn-outline-success d-inline-flex align-items-center gap-2">
                <i class="bi bi-printer"></i> In Giấy Chứng Nhận Tiêm Chủng
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

    <div class="row g-4">
        <!-- Patient & Vaccine Details -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-info-circle text-primary me-2"></i>Thông Tin Ca Tiêm Chủng</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Bệnh nhân</small>
                            <h5 class="fw-bold text-dark mb-0">{{ $record->patientProfile->full_name }}</h5>
                            <span class="text-muted small">Mã: {{ $record->patientProfile->patient_code }} • {{ $record->patientProfile->age_text }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Vắc-xin tiêm chủng</small>
                            <h5 class="fw-bold text-primary mb-0">{{ $record->vaccine->name }}</h5>
                            <span class="badge bg-light text-dark border">Mũi thứ {{ $record->dose_number }}/{{ $record->vaccine->total_doses }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Bệnh phòng ngừa</small>
                            <strong class="text-dark small">{{ $record->vaccine->disease_prevented }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Nhà sản xuất / Xuất xứ</small>
                            <strong class="text-dark small">{{ $record->vaccine->manufacturer }} ({{ $record->vaccine->origin_country }})</strong>
                        </div>
                        <div class="col-sm-4">
                            <small class="text-muted d-block">Số lô (Lot/Batch)</small>
                            <span class="badge bg-light text-dark font-monospace border fs-6">{{ $record->batch_number }}</span>
                        </div>
                        <div class="col-sm-4">
                            <small class="text-muted d-block">Vị trí tiêm</small>
                            <strong class="text-dark">{{ $record->injection_site }}</strong>
                        </div>
                        <div class="col-sm-4">
                            <small class="text-muted d-block">Thân nhiệt lúc tiêm</small>
                            <strong class="text-success fs-5">{{ $record->body_temperature }}°C</strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Thời điểm tiêm</small>
                            <strong class="text-dark">{{ $record->administered_at->format('H:i - d/m/Y') }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Hẹn mũi tiếp theo</small>
                            <strong class="text-primary">{{ $record->next_dose_due_date ? $record->next_dose_due_date->format('d/m/Y') : 'Đã hoàn thành phác đồ' }}</strong>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-light rounded-3">
                        <small class="text-muted d-block fw-semibold">Kết quả khám sàng lọc:</small>
                        <p class="mb-0 text-dark small mt-1">{{ $record->screening_notes ?: 'Đủ điều kiện tiêm chủng.' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 30-Minute Timer & Observation Status -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-4 border-top border-4 {{ $record->observation_status === 'cleared' ? 'border-success' : 'border-warning' }}">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-slate-800">
                        <i class="bi bi-stopwatch text-warning me-2"></i>Quy Trình Theo Dõi 30 Phút Sau Tiêm
                    </h6>
                </div>
                <div class="card-body p-4 text-center">
                    @if($record->observation_status === 'observing')
                        <div class="mb-3">
                            <span class="display-4 fw-bold {{ $record->is_observation_complete ? 'text-success' : 'text-warning' }}">
                                {{ $record->observation_elapsed_minutes }} <span class="fs-5 text-muted">/ 30 phút</span>
                            </span>
                        </div>

                        <div class="progress mb-3" style="height: 10px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated {{ $record->is_observation_complete ? 'bg-success' : 'bg-warning' }}" style="width: {{ min(100, round(($record->observation_elapsed_minutes / 30) * 100)) }}%"></div>
                        </div>

                        @if($record->is_observation_complete)
                            <div class="alert alert-success border-0 small mb-3">
                                <i class="bi bi-check-circle-fill me-1"></i>Bệnh nhân đã hoàn thành đủ thời gian 30 phút theo dõi an toàn.
                            </div>
                        @else
                            <p class="text-muted small mb-3">
                                Còn <strong>{{ $record->observation_remaining_minutes }} phút</strong> nữa mới hoàn tất thời gian theo dõi bắt buộc.
                            </p>
                        @endif

                        <form action="{{ route('reception.vaccinations.clear', $record) }}" method="POST">
                            @csrf
                            <div class="mb-3 text-start">
                                <label class="form-label small fw-semibold">Ghi nhận tình trạng trước khi cho ra về:</label>
                                <textarea name="notes" class="form-control form-control-sm" rows="2">Vết tiêm khô ráo, không sốt, không nổi ban, huyết áp và nhịp thở hoàn toàn bình thường.</textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100 fw-bold">
                                <i class="bi bi-shield-check me-1"></i> Xác Nhận An Toàn & Xuất Viện
                            </button>
                        </form>

                    @elseif($record->observation_status === 'cleared')
                        <div class="py-3">
                            <i class="bi bi-shield-fill-check display-3 text-success d-block mb-2"></i>
                            <h5 class="fw-bold text-success mb-1">ĐÃ XÁC NHẬN AN TOÀN</h5>
                            <p class="text-muted small mb-2">Thời gian xuất viện: {{ $record->observation_cleared_at?->format('H:i - d/m/Y') }}</p>
                            <div class="p-3 bg-light rounded text-start small">
                                <strong>Ghi chú:</strong> {{ $record->adverse_reaction_notes ?: 'Sinh hiệu ổn định, không có phản ứng phản vệ.' }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
