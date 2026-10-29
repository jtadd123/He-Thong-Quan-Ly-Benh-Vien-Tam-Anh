@extends('layouts.admin')

@section('title', 'Chi Tiết Giấy Chuyển Tuyến BHYT: ' . $insuranceTransfer->transfer_code)

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('reception.insurance-transfers.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
            </a>
            <h4 class="fw-bold mb-1 text-primary">
                <i class="bi bi-file-earmark-check me-2"></i>Chi Tiết Giấy Chuyển Tuyến: {{ $insuranceTransfer->transfer_code }}
            </h4>
            <p class="text-muted small mb-0">Hồ sơ liên thông tuyến BHYT theo Quyết định của Bộ Y Tế</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reception.insurance-transfers.print', $insuranceTransfer) }}" target="_blank" class="btn btn-primary d-flex align-items-center gap-1">
                <i class="bi bi-printer"></i> In Phiếu Xác Nhận
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-info-circle text-primary me-2"></i>Nội Dung Giấy Chuyển Tuyến (Mẫu 40/BYT)
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Cơ Sở Khám Chữa Bệnh Nơi Đi:</span>
                            <span class="fw-bold text-dark fs-6">{{ $insuranceTransfer->issuing_hospital_name }}</span>
                            <div class="small text-muted">Mã ĐK KCB Ban Đầu: <strong class="text-primary">{{ $insuranceTransfer->issuing_hospital_code }}</strong></div>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Phân Loại Lý Do Chuyển Tuyến:</span>
                            <span class="badge bg-primary fs-6">
                                {{ match($insuranceTransfer->transfer_type) {
                                    'right_route' => 'Đúng Tuyến (Vượt chuyên môn kỹ thuật)',
                                    'emergency' => 'Cấp Cứu Chuyển Viện',
                                    default => 'Theo Yêu Cầu Của Người Bệnh'
                                } }}
                            </span>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Chẩn Đoán Nơi Chuyển Đi:</span>
                            <span class="fw-bold text-danger">{{ $insuranceTransfer->icd10_diagnosis }}</span>
                            @if($insuranceTransfer->icd10_code)
                                <div class="badge bg-secondary font-monospace">Mã ICD-10: {{ $insuranceTransfer->icd10_code }}</div>
                            @endif
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Thời Hạn Hiệu Lực:</span>
                            <div class="fw-bold text-dark">
                                Từ ngày {{ $insuranceTransfer->valid_from->format('d/m/Y') }} đến {{ $insuranceTransfer->valid_to->format('d/m/Y') }}
                            </div>
                            <div class="mt-1">{!! $insuranceTransfer->status_badge !!}</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <span class="text-muted small d-block mb-1">Tóm Tắt Bệnh Lý & Lý Do Chuyển Tuyến:</span>
                        <div class="p-3 bg-light rounded border text-secondary" style="min-height: 80px;">
                            {{ $insuranceTransfer->clinical_summary ?: 'Không có ghi chú thêm từ cơ sở chuyển đến.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-person-badge text-primary me-2"></i>Bệnh Nhân Hưởng BHYT
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3">
                            <i class="bi bi-person-fill fs-3"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-6">{{ $insuranceTransfer->patientProfile->full_name }}</div>
                            <small class="text-muted">{{ $insuranceTransfer->patientProfile->patient_code }} | {{ $insuranceTransfer->patientProfile->age_text }}</small>
                        </div>
                    </div>

                    <div class="mb-2">
                        <span class="text-muted small d-block">Mã Thẻ BHYT:</span>
                        <span class="badge bg-success font-monospace fs-6">{{ $insuranceTransfer->patientProfile->insurance_code }}</span>
                    </div>

                    <div class="mb-2">
                        <span class="text-muted small d-block">Số CCCD / CMND:</span>
                        <span class="fw-semibold text-dark">{{ $insuranceTransfer->patientProfile->identity_card ?: 'Chưa cập nhật' }}</span>
                    </div>

                    <div class="mb-3">
                        <span class="text-muted small d-block">Tỷ Lệ Hưởng BHYT Tại Tâm Anh:</span>
                        <span class="badge bg-info text-dark fs-5 fw-bold">{{ $insuranceTransfer->bhyt_reimbursement_rate }}%</span>
                    </div>

                    <div class="d-grid mt-4">
                        <a href="{{ route('reception.patients.show', $insuranceTransfer->patientProfile) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-person-vcard me-1"></i> Xem Hồ Sơ Bệnh Nhân Toàn Diện
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
