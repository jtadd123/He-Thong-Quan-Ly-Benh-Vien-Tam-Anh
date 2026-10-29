@extends('layouts.admin')

@section('title', 'Chi Tiết & Phân Tích RCA Sự Cố Y Khoa: ' . $medicalIncident->incident_code)

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('reception.medical-incidents.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
            </a>
            <h4 class="fw-bold mb-1 text-danger">
                <i class="bi bi-shield-exclamation me-2"></i>Sự Cố Y Khoa: {{ $medicalIncident->incident_code }}
            </h4>
            <p class="text-muted small mb-0">Phân tích nguyên nhân gốc rễ (Root Cause Analysis - RCA 5 Whys) theo Thông tư 43/2018/TT-BYT</p>
        </div>
        <div>
            {!! $medicalIncident->severity_badge !!}
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-file-text text-primary me-2"></i>Nội Dung Sự Cố Đã Ghi Nhận
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Phân Nhóm Sự Cố:</span>
                            <span class="fw-bold text-dark">{{ $medicalIncident->incident_category }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Thời Gian & Địa Điểm:</span>
                            <span class="fw-semibold text-dark">{{ $medicalIncident->occurred_at->format('H:i d/m/Y') }}</span>
                            <div class="small text-danger"><i class="bi bi-geo-alt me-1"></i>{{ $medicalIncident->location }}</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <span class="text-muted small d-block mb-1">Mô Tả Diễn Biến Sự Cố:</span>
                        <div class="p-3 bg-light rounded text-secondary border">
                            {{ $medicalIncident->description }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <span class="text-muted small d-block mb-1">Hành Động Khắc Phục Tức Thì:</span>
                        <div class="p-3 bg-success bg-opacity-10 text-success rounded border border-success">
                            {{ $medicalIncident->immediate_action ?: 'Chưa ghi nhận hành động can thiệp tức thời.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-diagram-3-fill text-danger me-2"></i>Phân Tích Nguyên Nhân Gốc Rễ (RCA)
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('reception.medical-incidents.rca', $medicalIncident) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Trạng Thái Xử Lý <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="investigating" {{ $medicalIncident->status === 'investigating' ? 'selected' : '' }}>Đang điều tra nguyên nhân (Investigating)</option>
                                <option value="resolved" {{ $medicalIncident->status === 'resolved' ? 'selected' : '' }}>Đã ban hành quy trình khắc phục (Resolved)</option>
                                <option value="closed" {{ $medicalIncident->status === 'closed' ? 'selected' : '' }}>Đóng hồ sơ sự cố (Closed)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kết Luận Nguyên Nhân Gốc Rễ & Kế Hoạch Phòng Ngừa (RCA / 5 Whys) <span class="text-danger">*</span></label>
                            <textarea name="root_cause_analysis" class="form-control" rows="6" placeholder="Ghi nhận phân tích nguyên nhân cốt lõi (Con người, Thiết bị, Quy trình, Môi trường) và giải pháp khắc phục triệt để..." required>{{ old('root_cause_analysis', $medicalIncident->root_cause_analysis) }}</textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary fw-bold">
                                <i class="bi bi-check2-circle me-1"></i> Lưu Kết Quả Phân Tích & Kế Hoạch
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
