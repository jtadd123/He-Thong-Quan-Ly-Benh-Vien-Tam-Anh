@extends('layouts.admin')

@section('title', 'Thư Bảo Lãnh Viện Phí: ' . $privateInsurance->gop_number)

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('reception.private-insurance.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
            </a>
            <h4 class="fw-bold mb-1 text-primary">
                <i class="bi bi-shield-check me-2"></i>Thư Bảo Lãnh Viện Phí GOP: {{ $privateInsurance->gop_number }}
            </h4>
            <p class="text-muted small mb-0">Hãng bảo hiểm: <strong>{{ $privateInsurance->tpa_company }}</strong> - Mã thẻ: <strong>{{ $privateInsurance->card_number }}</strong></p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reception.private-insurance.print', $privateInsurance) }}" target="_blank" class="btn btn-primary d-flex align-items-center gap-1">
                <i class="bi bi-printer"></i> In Thư Bảo Lãnh GOP
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-cash-stack text-primary me-2"></i>Chi Tiết Hạn Mức & Phân Tách Nguồn Chi Trả
                    </h6>
                    <div>{!! $privateInsurance->status_badge !!}</div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded text-center">
                                <span class="text-muted small d-block">Hạn Mức Thẻ Được Cấp</span>
                                <span class="fs-5 fw-bold text-dark">{{ number_format($privateInsurance->coverage_limit, 0, ',', '.') }} đ</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-success bg-opacity-10 rounded text-center border border-success">
                                <span class="text-success small d-block fw-semibold">Bảo Hiểm Chi Trả (GOP)</span>
                                <span class="fs-4 fw-bold text-success">{{ number_format($privateInsurance->approved_amount, 0, ',', '.') }} đ</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-danger bg-opacity-10 rounded text-center border border-danger">
                                <span class="text-danger small d-block fw-semibold">Bệnh Nhân Tự Trả (Copay)</span>
                                <span class="fs-5 fw-bold text-danger">{{ number_format($privateInsurance->patient_copay_amount, 0, ',', '.') }} đ</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Chuyên Viên Thẩm Định Bồi Thường:</span>
                            <span class="fw-semibold text-dark">{{ $privateInsurance->contact_officer ?: 'Hotline Thẩm Định' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Số Điện Thoại Xác Thực:</span>
                            <span class="fw-semibold text-dark">{{ $privateInsurance->contact_phone ?: '1900 xxxx' }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <span class="text-muted small d-block mb-1">Ghi Chú & Điều Khoản Loại Trừ:</span>
                        <div class="p-3 bg-light rounded text-secondary border">
                            {{ $privateInsurance->remarks ?: 'Không có điều khoản loại trừ đặc biệt. Áp dụng theo quy tắc bảo hiểm chuẩn.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-gem text-primary me-2"></i>Phân Hạng Tiếp Đón VIP
                    </h6>
                </div>
                <div class="card-body p-4 text-center">
                    <div class="mb-3">{!! $privateInsurance->vip_badge !!}</div>
                    <div class="fw-bold fs-5 text-dark mb-1">{{ $privateInsurance->patientProfile->full_name }}</div>
                    <div class="small text-muted mb-3">{{ $privateInsurance->patientProfile->patient_code }} | {{ $privateInsurance->patientProfile->phone }}</div>
                    
                    <div class="p-3 bg-light rounded text-start small border">
                        <div class="mb-1"><strong>Lễ tân phụ trách:</strong> {{ $privateInsurance->handledBy?->name ?? 'Lễ tân tiếp nhận' }}</div>
                        <div><strong>Ngày tạo GOP:</strong> {{ $privateInsurance->created_at->format('H:i d/m/Y') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
